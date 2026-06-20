<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
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
                ]);

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
        return view('pos.invoice', compact('order'));
    }
}
