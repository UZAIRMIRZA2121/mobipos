<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        return view('shop.purchase_orders');
    }

    public function apiIndex(Request $request)
    {
        $query = PurchaseOrder::where('user_id', Auth::id())->with(['items.product', 'user'])->orderBy('created_at', 'desc');
        
        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->has('payment_status') && $request->payment_status != '') {
            $query->where('payment_status', $request->payment_status);
        }
        
        $pos = $query->get()->toArray();
        $aggregates = (clone $query)->reorder()->select(
            \DB::raw('SUM(amount) as grand_total'),
            \DB::raw('SUM(LEAST(paid_amount, amount)) as total_paid'),
            \DB::raw('SUM(GREATEST(amount - paid_amount, 0)) as total_due')
        )->first();

        return response()->json([
            'data' => $pos,
            'totals' => [
                'grand_total' => $aggregates->grand_total ?? 0,
                'total_paid' => $aggregates->total_paid ?? 0,
                'total_due' => $aggregates->total_due ?? 0,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_name' => 'nullable|string|max:255',
            'paid_amount' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.custom_name' => 'nullable|string|max:255',
            'items.*.qty' => 'required|numeric|min:0.001',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $totalAmount += ($item['qty'] * $item['price']);
            }

            $paidAmount = $validated['paid_amount'];
            $remainingAmount = $totalAmount - $paidAmount;
            
            $paymentStatus = 'unpaid';
            if ($paidAmount > 0 && $paidAmount < $totalAmount) {
                $paymentStatus = 'partial';
            } elseif ($paidAmount >= $totalAmount && $totalAmount > 0) {
                $paymentStatus = 'paid';
            }

            $po = PurchaseOrder::create([
                'user_id' => Auth::id(),
                'supplier_name' => $validated['supplier_name'] ?? '',
                'amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'remaining_amount' => $remainingAmount,
                'payment_status' => $paymentStatus,
            ]);

            foreach ($request->items as $item) {
                $amount = $item['qty'] * $item['price'];
                
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $item['product_id'] ?? null,
                    'custom_name' => $item['custom_name'] ?? null,
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'amount' => $amount,
                    'user_id' => Auth::id(),
                ]);

                // Increment Stock, update prices, and save IMEIs
                if (!empty($item['product_id'])) {
                    $product = Product::find($item['product_id']);
                    if ($product) {
                        $product->stock += $item['qty'];
                        if (isset($item['sale_price']) && is_numeric($item['sale_price'])) {
                            $product->sale_price = $item['sale_price'];
                        }
                        // Optionally update purchase price to the latest PO price
                        if (isset($item['price']) && is_numeric($item['price'])) {
                            $product->purchase_price = $item['price'];
                        }
                        $product->save();

                        if (in_array($product->type, ['mobile', 'tablet', 'laptop'])) {
                            $imeisArray = $item['imeis'] ?? [];
                            for ($i = 0; $i < $item['qty']; $i++) {
                                \App\Models\ProductStockUnit::create([
                                    'product_id' => $product->id,
                                    'imeis' => $imeisArray[$i] ?? null,
                                    'status' => 'available',
                                ]);
                            }
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Purchase Order created successfully!',
                'po' => $po->load('items.product')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create Purchase Order: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            'supplier_name' => 'nullable|string|max:255',
            'paid_amount' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.custom_name' => 'nullable|string|max:255',
            'items.*.qty' => 'required|numeric|min:0.001',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Revert previous stock and delete old items
            foreach ($purchaseOrder->items as $item) {
                $product = $item->product;
                if ($product) {
                    $product->stock -= $item->qty;
                    if ($product->stock < 0) $product->stock = 0;
                    $product->save();
                }
            }
            $purchaseOrder->items()->delete();

            // Calculate new totals
            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $totalAmount += ($item['qty'] * $item['price']);
            }

            $paidAmount = $validated['paid_amount'];
            $remainingAmount = $totalAmount - $paidAmount;
            
            $paymentStatus = 'unpaid';
            if ($paidAmount > 0 && $paidAmount < $totalAmount) {
                $paymentStatus = 'partial';
            } elseif ($paidAmount >= $totalAmount && $totalAmount > 0) {
                $paymentStatus = 'paid';
            }

            $purchaseOrder->update([
                'supplier_name' => $validated['supplier_name'] ?? '',
                'amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'remaining_amount' => $remainingAmount,
                'payment_status' => $paymentStatus,
            ]);

            // Add new items and increment stock
            foreach ($request->items as $item) {
                $amount = $item['qty'] * $item['price'];
                
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $item['product_id'] ?? null,
                    'custom_name' => $item['custom_name'] ?? null,
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'amount' => $amount,
                    'user_id' => Auth::id(),
                ]);

                if (!empty($item['product_id'])) {
                    $product = Product::find($item['product_id']);
                    if ($product) {
                        $product->stock += $item['qty'];
                        if (isset($item['sale_price']) && is_numeric($item['sale_price'])) {
                            $product->sale_price = $item['sale_price'];
                        }
                        if (isset($item['price']) && is_numeric($item['price'])) {
                            $product->purchase_price = $item['price'];
                        }
                        $product->save();

                        if (in_array($product->type, ['mobile', 'tablet', 'laptop'])) {
                            $imeisArray = $item['imeis'] ?? [];
                            for ($i = 0; $i < $item['qty']; $i++) {
                                \App\Models\ProductStockUnit::create([
                                    'product_id' => $product->id,
                                    'imeis' => $imeisArray[$i] ?? null,
                                    'status' => 'available',
                                ]);
                            }
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Purchase Order updated successfully!',
                'po' => $purchaseOrder->load('items.product')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update Purchase Order: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        try {
            DB::beginTransaction();
            
            // Revert stock
            foreach ($purchaseOrder->items as $item) {
                $product = $item->product;
                if ($product) {
                    $product->stock -= $item->qty;
                    // Don't let it go below 0 just in case
                    if ($product->stock < 0) $product->stock = 0;
                    $product->save();
                }
            }

            $purchaseOrder->delete();
            
            DB::commit();
            return response()->json(['message' => 'Purchase Order deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to delete Purchase Order'], 500);
        }
    }
}
