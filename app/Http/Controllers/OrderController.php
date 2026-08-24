<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
class OrderController extends Controller
{
    public function apiIndex(Request $request)
    {
        $query = Order::where('user_id', Auth::id())->where('is_installment', 0)->with(['items.product', 'buyer'])->orderBy('created_at', 'desc');
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('buyer', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  })
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhereHas('items.product', function($q3) use ($search) {
                      $q3->where('code', 'like', "%{$search}%")
                         ->orWhere('barcode', 'like', "%{$search}%");
                  })
                  ->orWhereHas('items', function($q4) use ($search) {
                      $q4->where('imeis', 'like', "%{$search}%");
                  });
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

    public function dashboardStats(\Illuminate\Http\Request $request)
    {
        $totalEarning = Order::where('user_id', Auth::id())->where('payment_status', '!=', 'refunded')->where('is_installment', 0)->sum('total');
        
        $totalCostOfGoodsSold = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.user_id', Auth::id())
            ->where('orders.payment_status', '!=', 'refunded')
            ->where('orders.is_installment', 0)
            ->sum(DB::raw('order_items.buy_price * order_items.qty'));

        $totalExpense = Expense::where('user_id', Auth::user()->id)->sum('amount');

        $actualEarning = $totalEarning - $totalCostOfGoodsSold - $totalExpense;
        
        $stockValue = DB::table('products')
            ->where('user_id', Auth::id())
            ->where('status', 'in_stock')
            ->sum(DB::raw('purchase_price * stock'));

        // Recent Sales
        $recentSales = Order::where('user_id', Auth::id())
            ->where('is_installment', 0)
            ->with(['items.product', 'buyer'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Top Selling Products (Performance)
        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->select('order_items.product_id', DB::raw('SUM(order_items.qty) as total_qty'), DB::raw('SUM(order_items.sell_price * order_items.qty) as total_revenue'))
            ->where('orders.user_id', Auth::id())
            ->where('orders.is_installment', 0)
            ->groupBy('order_items.product_id')
            ->orderBy('total_qty', 'desc')
            ->take(5)
            ->get();
        
        // Attach product names for top products
        foreach($topProducts as $tp) {
            $prod = DB::table('products')->where('id', $tp->product_id)->first();
            $tp->name = $prod ? $prod->name : 'Unknown';
            $tp->imei = $prod ? $prod->code : '';
        }

        // Determine period
        $period = $request->input('period', 'week'); // 'week', 'month', 'year'
        if ($period === 'year') {
            $startDate = \Carbon\Carbon::now()->subMonths(11)->startOfMonth();
        } else if ($period === 'month') {
            $startDate = \Carbon\Carbon::now()->subDays(29)->startOfDay();
        } else {
            $startDate = \Carbon\Carbon::now()->subDays(6)->startOfDay(); // Default 'week'
        }
        
        $dailySalesQuery = DB::table('orders')
            ->where('user_id', Auth::id())
            ->where('payment_status', '!=', 'refunded')
            ->where('is_installment', 0)
            ->where('created_at', '>=', $startDate);

        $dailyExpensesQuery = DB::table('expenses')
            ->where('user_id', Auth::id())
            ->where('expense_date', '>=', $startDate);

        $dailyCostsQuery = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.user_id', Auth::id())
            ->where('orders.payment_status', '!=', 'refunded')
            ->where('orders.is_installment', 0)
            ->where('orders.created_at', '>=', $startDate);

        if ($period === 'year') {
            // Group by month
            $dailySales = $dailySalesQuery
                ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m-01") as date'), DB::raw('SUM(total) as total_sales'))
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();

            $dailyExpenses = $dailyExpensesQuery
                ->select(DB::raw('DATE_FORMAT(expense_date, "%Y-%m-01") as date'), DB::raw('SUM(amount) as total_expense'))
                ->groupBy('date')
                ->get()
                ->keyBy('date');

            $dailyCosts = $dailyCostsQuery
                ->select(DB::raw('DATE_FORMAT(orders.created_at, "%Y-%m-01") as date'), DB::raw('SUM(order_items.buy_price * order_items.qty) as total_cost'))
                ->groupBy('date')
                ->get()
                ->keyBy('date');
        } else {
            // Group by day
            $dailySales = $dailySalesQuery
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as total_sales'))
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();

            $dailyExpenses = $dailyExpensesQuery
                ->select(DB::raw('DATE(expense_date) as date'), DB::raw('SUM(amount) as total_expense'))
                ->groupBy('date')
                ->get()
                ->keyBy('date');

            $dailyCosts = $dailyCostsQuery
                ->select(DB::raw('DATE(orders.created_at) as date'), DB::raw('SUM(order_items.buy_price * order_items.qty) as total_cost'))
                ->groupBy('date')
                ->get()
                ->keyBy('date');
        }

        foreach ($dailySales as $ds) {
            $expense = isset($dailyExpenses[$ds->date]) ? $dailyExpenses[$ds->date]->total_expense : 0;
            $cost = isset($dailyCosts[$ds->date]) ? $dailyCosts[$ds->date]->total_cost : 0;
            $ds->net_profit = $ds->total_sales - $cost - $expense;
        }

        return response()->json([
            'total_earning' => $totalEarning,
            'total_expense' => $totalExpense,
            'actual_earning' => $actualEarning,
            'stock_value' => $stockValue,
            'recent_sales' => $recentSales,
            'top_products' => $topProducts,
            'daily_sales' => $dailySales,
        ]);
    }

    public function apiShow($id)
    {
        $order = Order::where('user_id', Auth::id())->with(['items.product', 'buyer'])->findOrFail($id);
        return response()->json($order);
    }

    public function store(Request $request)
    {
        $request->validate([
            'buyer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:255',
            'subtotal' => 'required|numeric',
            'tax' => 'required|numeric',
            'discount' => 'required|numeric',
            'total' => 'required|numeric',
            'paid_amount' => 'required|numeric',
            'due_amount' => 'required|numeric',
            'payment_status' => 'required|string',
            'payment_method' => 'required|string',
            'items' => 'required|array',
            'items.*.product_id' => [
                'required',
                Rule::exists('products', 'id')->where(function ($query) {
                    $query->where('user_id', Auth::id());
                }),
            ],
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric',
            'items.*.stock_units' => 'nullable|array',
            'items.*.stock_units.*' => 'exists:product_stock_units,id',
        ]);

        try {
            DB::beginTransaction();

            $is_ledger = $request->has('save_to_ledger') && $request->save_to_ledger == 1 && $request->buyer_id;
            $order_paid = $is_ledger ? $request->total : $request->paid_amount;
            $order_due = $is_ledger ? 0 : $request->due_amount;

            // Create Order
            $order = Order::create([
                'buyer_id' => $request->buyer_id,
                'customer_name' => $request->customer_name,
                'subtotal' => $request->subtotal,
                'tax' => $request->tax,
                'discount' => $request->discount,
                'total' => $request->total,
                'paid_amount' => $order_paid,
                'due_amount' => $order_due,
                'payment_status' => $is_ledger ? 'paid' : $request->payment_status,
                'payment_method' => $request->payment_method,
                'is_installment' => ($request->payment_method === 'installment' || $request->is_installment == 1) ? 1 : 0,
                'user_id' => Auth::id(),
            ]);

            // Create Items & Deduct Stock
            foreach ($request->items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'seller_id' => $product->buyer_id ?: null, // Force null if empty to avoid FK constraint errors
                    'qty' => $itemData['qty'],
                    'buy_price' => $product->purchase_price ?? 0, 
                    'sell_price' => $itemData['price'],
                    'user_id' => Auth::id(),
                ]);

                // Handle Stock Units (IMEIs)
                $imeisString = null;
                if (!empty($itemData['stock_units'])) {
                    $selectedUnits = \App\Models\ProductStockUnit::whereIn('id', $itemData['stock_units'])
                        ->where('status', 'available')
                        ->get();
                        
                    if ($selectedUnits->count() < $itemData['qty']) {
                        throw new \Exception("One or more selected IMEIs for {$product->name} are no longer available.");
                    }
                    
                    $imeisList = [];
                    foreach ($selectedUnits as $unit) {
                        $unit->update([
                            'status' => 'sold',
                            'order_item_id' => $orderItem->id
                        ]);
                        if (!empty($unit->imeis)) {
                            $imeisList[] = $unit->imeis;
                        }
                    }
                    
                    if (!empty($imeisList)) {
                        $imeisString = implode(' | ', $imeisList);
                        $orderItem->update(['imeis' => $imeisString]);
                    }
                }

                // Update product stock and status
                if ($product->stock < $itemData['qty']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}");
                }
                
                $product->stock -= $itemData['qty'];
                
                if ($product->stock <= 0) {
                    $product->status = 'sold';
                }
            
                $product->save();
            }

            // Save to Ledger if checked
            if ($request->has('save_to_ledger') && $request->save_to_ledger == 1 && $request->buyer_id) {
                $diff = (float)$request->total - (float)$request->paid_amount;
                if ($diff > 0) {
                    $lastLedger = \App\Models\CustomerLedger::where('customer_id', $request->buyer_id)->orderBy('date', 'desc')->orderBy('id', 'desc')->first();
                    $previousBalance = $lastLedger ? (float)$lastLedger->balance : 0;
                    $debit = $diff;
                    $credit = 0;
                    $newBalance = $previousBalance + $debit - $credit;

                    \App\Models\CustomerLedger::create([
                        'customer_id' => $request->buyer_id,
                        'user_id' => \Illuminate\Support\Facades\Auth::id(),
                        'date' => now()->format('Y-m-d H:i:s'),
                        'type' => 'Sale (Order #' . $order->id . ')',
                        'debit' => $debit,
                        'credit' => $credit,
                        'balance' => $newBalance,
                        'note' => 'Auto entry from POS'
                    ]);
                }
            }

            // Create Installment Record if applicable
            if ($request->payment_method === 'installment' || ($request->has('is_installment') && $request->is_installment == 1)) {
                \App\Models\Installment::create([
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    'order_id' => $order->id,
                    'customer_id' => $request->buyer_id,
                    'total_amount' => $request->total,
                    'down_payment' => $request->installment_down_payment ?? 0,
                    'agreed_monthly_amount' => $request->installment_monthly_amount ?? 0,
                    'payment_day' => $request->installment_payment_day ?? 10,
                    'interest_percentage' => $request->installment_interest_percentage ?? 0,
                    'actual_price' => $request->installment_actual_price ?? 0,
                    'status' => 'Active'
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully!',
                'order_id' => $order->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Order Creation Failed: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
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
                    \App\Models\ProductStockUnit::where('order_item_id', $item->id)->update([
                        'status' => 'available',
                        'order_item_id' => null
                    ]);

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

            $is_ledger = $request->has('save_to_ledger') && $request->save_to_ledger == 1 && $request->buyer_id;
            $order_paid = $is_ledger ? $request->total : $request->paid_amount;
            $order_due = $is_ledger ? 0 : $request->due_amount;

            // Update Order Data
            $order->update([
                'buyer_id' => $request->buyer_id,
                'subtotal' => $request->subtotal,
                'tax' => $request->tax,
                'discount' => $request->discount,
                'total' => $request->total,
                'paid_amount' => $order_paid,
                'due_amount' => $order_due,
                'payment_status' => $is_ledger ? 'paid' : $request->payment_status,
                'payment_method' => $request->payment_method,
                'is_installment' => ($request->payment_method === 'installment' || $request->is_installment == 1) ? 1 : 0,
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
                
            if (!empty($product->code) && $product->stock <= 0) {
                $product->status = 'sold';
            }
            
            $product->save();
        }

        // Save to Ledger if checked
        if ($request->has('save_to_ledger') && $request->save_to_ledger == 1 && $request->buyer_id) {
            $diff = $request->total - $request->paid_amount;
            if ($diff != 0) {
                $lastLedger = \App\Models\CustomerLedger::where('customer_id', $request->buyer_id)->orderBy('date', 'desc')->orderBy('id', 'desc')->first();
                $previousBalance = $lastLedger ? $lastLedger->balance : 0;
                $debit = $diff > 0 ? $diff : 0;
                $credit = $diff < 0 ? abs($diff) : 0;
                $newBalance = $previousBalance + $debit - $credit;

                \App\Models\CustomerLedger::create([
                    'customer_id' => $request->buyer_id,
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    'date' => now()->format('Y-m-d'),
                    'type' => 'Sale Update (Order #' . $order->id . ')',
                    'debit' => $debit,
                    'credit' => $credit,
                    'balance' => $newBalance,
                    'note' => 'Auto entry from POS Update'
                ]);
            }
        }

        // Update Installment Record if applicable
        if ($request->payment_method === 'installment' || ($request->has('is_installment') && $request->is_installment == 1)) {
            \App\Models\Installment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'customer_id' => $request->buyer_id,
                    'total_amount' => $request->total,
                    'down_payment' => $request->installment_down_payment ?? 0,
                    'agreed_monthly_amount' => $request->installment_monthly_amount ?? 0,
                    'payment_day' => $request->installment_payment_day ?? 10,
                    'interest_percentage' => $request->installment_interest_percentage ?? 0,
                    'actual_price' => $request->installment_actual_price ?? 0,
                    'status' => 'Active'
                ]
            );
        } else {
            \App\Models\Installment::where('order_id', $order->id)->delete();
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
    private function removeOrderLedgers(Order $order)
    {
        $ledgers = \App\Models\CustomerLedger::where('type', 'like', '%Order #' . $order->id . ')%')->get();
        foreach ($ledgers as $ledger) {
            $deletedDate = $ledger->date;
            $deletedId = $ledger->id;
            $customerId = $ledger->customer_id;
            
            if ($ledger->payment_proof) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($ledger->payment_proof);
            }
            $ledger->delete();

            $subsequentLedgers = \App\Models\CustomerLedger::where('customer_id', $customerId)
                ->where(function($query) use ($deletedDate, $deletedId) {
                    $query->where('date', '>', $deletedDate)
                          ->orWhere(function($q) use ($deletedDate, $deletedId) {
                              $q->where('date', '=', $deletedDate)
                                ->where('id', '>', $deletedId);
                          });
                })
                ->orderBy('date', 'asc')->orderBy('id', 'asc')->get();

            $previousLedger = \App\Models\CustomerLedger::where('customer_id', $customerId)
                ->where(function($query) use ($deletedDate, $deletedId) {
                    $query->where('date', '<', $deletedDate)
                          ->orWhere(function($q) use ($deletedDate, $deletedId) {
                              $q->where('date', '=', $deletedDate)
                                ->where('id', '<', $deletedId);
                          });
                })
                ->orderBy('date', 'desc')->orderBy('id', 'desc')->first();

            $currentBalance = $previousLedger ? (float)$previousLedger->balance : 0;
            foreach ($subsequentLedgers as $subLedger) {
                $currentBalance = $currentBalance + $subLedger->debit - $subLedger->credit;
                $subLedger->update(['balance' => $currentBalance]);
            }
        }
    }

    public function destroy(Order $order)
    {
        DB::transaction(function () use ($order) {
            if ($order->payment_status !== 'refunded') {
                foreach ($order->items as $item) {
                    \App\Models\ProductStockUnit::where('order_item_id', $item->id)->update([
                        'status' => 'available',
                        'order_item_id' => null
                    ]);

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
            $this->removeOrderLedgers($order);
            $order->items()->delete();
            \App\Models\Installment::where('order_id', $order->id)->delete();
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
                \App\Models\ProductStockUnit::where('order_item_id', $item->id)->update([
                    'status' => 'available',
                    'order_item_id' => null
                ]);

                $product = $item->product;
                if ($product) {
                    $product->stock += $item->qty;
                    if ($product->status === 'sold') {
                        $product->status = 'in_stock';
                    }
                    $product->save();
                }
            }
            $this->removeOrderLedgers($order);
            $order->payment_status = 'refunded';
            $order->save();
        });

        return response()->json(['success' => true, 'message' => 'Order refunded successfully']);
    }
}
