<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
class OrderController extends Controller
{
    public function apiIndex(Request $request)
    {
        $query = Order::with(['items.product', 'buyer'])->orderBy('created_at', 'desc');
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('buyer', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        if ($request->has('payment_status') && $request->payment_status != '') {
            $query->where('payment_status', $request->payment_status);
        }

        $orders = $query->paginate(10)->toArray();
        $aggregates = (clone $query)->reorder()->select(
            \DB::raw('SUM(total) as grand_total'),
            \DB::raw('SUM(LEAST(paid_amount, total)) as total_paid'),
            \DB::raw('SUM(GREATEST(total - paid_amount, 0)) as total_due')
        )->first();

        $orders['totals'] = [
            'grand_total' => $aggregates->grand_total ?? 0,
            'total_paid' => $aggregates->total_paid ?? 0,
            'total_due' => $aggregates->total_due ?? 0,
        ];
        
        return response()->json($orders);
    }

    public function dashboardStats()
    {
        $totalEarning = Order::where('payment_status', '!=', 'refunded')->sum('total');
        
        // Sum of buy_price * qty for all non-refunded orders
        // Note: order_items buy_price might not exist if they don't store it, 
        // wait, let's verify if 'buy_price' is actually stored in order_items. 
        // We can just join products to get purchase_price if it's not.
        // Actually, order_items table might not have buy_price column in DB despite it being fillable. Let's join products just in case.
        $totalExpense = Expense::where('user_id',Auth::user()->id)->sum('amount');
      
            
        $actualEarning = $totalEarning - $totalExpense;
        
        $stockValue = DB::table('products')
            ->where('status', 'in_stock')
            ->sum(DB::raw('purchase_price * stock'));

        // Recent Sales
        $recentSales = Order::with(['items.product', 'buyer'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Top Selling Products (Performance)
        $topProducts = DB::table('order_items')
            ->select('product_id', DB::raw('SUM(qty) as total_qty'), DB::raw('SUM(sell_price * qty) as total_revenue'))
            ->groupBy('product_id')
            ->orderBy('total_qty', 'desc')
            ->take(5)
            ->get();
        
        // Attach product names for top products
        foreach($topProducts as $tp) {
            $prod = DB::table('products')->where('id', $tp->product_id)->first();
            $tp->name = $prod ? $prod->name : 'Unknown';
            $tp->imei = $prod ? $prod->imei_serial : '';
        }

        return response()->json([
            'total_earning' => $totalEarning,
            'total_expense' => $totalExpense,
            'actual_earning' => $actualEarning,
            'stock_value' => $stockValue,
            'recent_sales' => $recentSales,
            'top_products' => $topProducts,
        ]);
    }

    public function apiShow($id)
    {
        $order = Order::with(['items.product', 'buyer'])->findOrFail($id);
        return response()->json($order);
    }

    public function store(Request $request)
    {
        $request->validate([
            'buyer_id' => 'nullable|exists:customers,id',
            'subtotal' => 'required|numeric',
            'tax' => 'required|numeric',
            'discount' => 'required|numeric',
            'total' => 'required|numeric',
            'paid_amount' => 'required|numeric',
            'due_amount' => 'required|numeric',
            'payment_status' => 'required|string',
            'payment_method' => 'required|string',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();

            // Create Order
            $order = Order::create([
                'buyer_id' => $request->buyer_id,
                'subtotal' => $request->subtotal,
                'tax' => $request->tax,
                'discount' => $request->discount,
                'total' => $request->total,
                'paid_amount' => $request->paid_amount,
                'due_amount' => $request->due_amount,
                'payment_status' => $request->payment_status,
                'payment_method' => $request->payment_method,
                'user_id' => Auth::id(),
            ]);

            // Create Items & Deduct Stock
            foreach ($request->items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'seller_id' => $product->buyer_id, // buyer_id from product table
                    'qty' => $itemData['qty'],
                    'buy_price' => $product->purchase_price ?? 0, 
                    'sell_price' => $itemData['price'],
                    'user_id' => Auth::id(),
                ]);

                // Update product stock and status
                if ($product->stock < $itemData['qty']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}");
                }
                
                $product->stock -= $itemData['qty'];
                
                // Only mark as sold if it's a unique item with an IMEI
                if (!empty($product->imei_serial) && $product->stock <= 0) {
                    $product->status = 'sold';
                }
                
                $product->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully!',
                'order_id' => $order->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function invoice($id)
    {
        $order = Order::with(['items.product', 'buyer', 'items.seller'])->findOrFail($id);
        $invoiceSettings = \App\Models\InvoiceSetting::where('user_id', auth()->id())->first();
        return view('pos.invoice', compact('order', 'invoiceSettings'));
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'buyer_id' => 'nullable|exists:customers,id',
            'subtotal' => 'required|numeric',
            'tax' => 'required|numeric',
            'discount' => 'required|numeric',
            'total' => 'required|numeric',
            'paid_amount' => 'required|numeric',
            'due_amount' => 'required|numeric',
            'payment_status' => 'required|string',
            'payment_method' => 'required|string',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();

            // Revert old items and stock if it wasn't already refunded
            if ($order->payment_status !== 'refunded') {
                foreach ($order->items as $item) {
                    $product = $item->product;
                    if ($product) {
                        $product->stock += $item->qty;
                        if ($product->status === 'sold') {
                            $product->status = 'in_stock';
                        }
                        $product->save();
                    }
                }
            }
            $order->items()->delete();

            // Update Order Data
            $order->update([
                'buyer_id' => $request->buyer_id,
                'subtotal' => $request->subtotal,
                'tax' => $request->tax,
                'discount' => $request->discount,
                'total' => $request->total,
                'paid_amount' => $request->paid_amount,
                'due_amount' => $request->due_amount,
                'payment_status' => $request->payment_status,
                'payment_method' => $request->payment_method,
            ]);

            // Create New Items & Deduct Stock
            foreach ($request->items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'seller_id' => $product->buyer_id,
                    'qty' => $itemData['qty'],
                    'buy_price' => $product->purchase_price ?? 0, 
                    'sell_price' => $itemData['price'],
                ]);

                // Update product stock and status
                if ($product->stock < $itemData['qty']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}");
                }
                
                $product->stock -= $itemData['qty'];
                
                if (!empty($product->imei_serial) && $product->stock <= 0) {
                    $product->status = 'sold';
                }
                
                $product->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully!',
                'order_id' => $order->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function destroy(Order $order)
    {
        DB::transaction(function () use ($order) {
            if ($order->payment_status !== 'refunded') {
                foreach ($order->items as $item) {
                    $product = $item->product;
                    if ($product) {
                        $product->stock += $item->qty;
                        if ($product->status === 'sold') {
                            $product->status = 'in_stock';
                        }
                        $product->save();
                    }
                }
            }
            $order->items()->delete();
            $order->delete();
        });

        return response()->json(['success' => true, 'message' => 'Order deleted successfully']);
    }

    public function refund(Order $order)
    {
        if ($order->payment_status === 'refunded') {
            return response()->json(['success' => false, 'message' => 'Order is already refunded.'], 400);
        }

        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $product = $item->product;
                if ($product) {
                    $product->stock += $item->qty;
                    if ($product->status === 'sold') {
                        $product->status = 'in_stock';
                    }
                    $product->save();
                }
            }
            $order->payment_status = 'refunded';
            $order->save();
        });

        return response()->json(['success' => true, 'message' => 'Order refunded successfully']);
    }
}
