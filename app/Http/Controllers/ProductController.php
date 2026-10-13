<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductStockUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        $variations = \App\Models\Variation::where('user_id', Auth::id())->get(['id','name','cat_id']);
        $addons     = \App\Models\Addon::where('user_id', Auth::id())->get(['id','name','cat_id']);
        return view('products.index', compact('variations', 'addons'));
    }

    public function apiIndex(Request $request)
    {
        $products = Product::where('user_id', Auth::id())->with('stockUnits')->get();
        return response()->json($products);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string',
            'condition' => 'required|in:new,used,refurbished',
            'code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products')->where(fn ($query) => $query->where('user_id', Auth::id()))
            ],
            'barcode' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products')->where(fn ($query) => $query->where('user_id', Auth::id()))
            ],
            'color' => 'nullable|string|max:255',
            'storage' => 'nullable|string|max:255',
            'purchase_price' => 'nullable|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'status' => 'required|in:in_stock,sold,in_repair',
            'stock' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'buyer_id' => 'nullable|exists:customers,id',
            'category_id' => 'nullable|exists:categories,id',
            'units_imeis' => 'nullable|array',
            'units_imeis.*' => 'nullable|string',
            'units_is_pta' => 'nullable|array',
            'units_is_pta.*' => 'nullable|boolean',
            'meta_data' => 'nullable|json',
        ]);

        $validated['user_id'] = Auth::id();

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        if ($request->has('meta_data')) {
            $validated['meta_data'] = json_decode($request->input('meta_data'), true);
        }

        $product = Product::create($validated);

        $businessType = Auth::user()->storeSetting->business_type ?? 'mobile';
        
        if ($businessType === 'mobile' && in_array($product->type, ['mobile', 'tablet', 'laptop'])) {
            $stock = (int) ($product->stock ?: 1);
            $imeisArray = $request->input('units_imeis', []);
            $isPtaArray = $request->input('units_is_pta', []);
            
            for ($i = 0; $i < $stock; $i++) {
                ProductStockUnit::create([
                    'product_id' => $product->id,
                    'imeis' => $imeisArray[$i] ?? null,
                    'is_pta' => !empty($isPtaArray[$i]) && ($isPtaArray[$i] == '1' || $isPtaArray[$i] == 'true'),
                    'status' => 'available',
                ]);
            }
        }

        return response()->json($product->load(['stockUnits' => function($q) {
            $q->where('status', 'available');
        }]), 201);
    }

    public function update(Request $request, Product $product)
    {
        if ($product->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string',
            'condition' => 'required|in:new,used,refurbished',
            'code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products')->where(fn ($query) => $query->where('user_id', Auth::id()))->ignore($product->id)
            ],
            'barcode' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products')->where(fn ($query) => $query->where('user_id', Auth::id()))->ignore($product->id)
            ],
            'color' => 'nullable|string|max:255',
            'storage' => 'nullable|string|max:255',
            'purchase_price' => 'nullable|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'status' => 'required|in:in_stock,sold,in_repair',
            'stock' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'buyer_id' => 'nullable|exists:customers,id',
            'category_id' => 'nullable|exists:categories,id',
            'units_imeis' => 'nullable|array',
            'units_imeis.*' => 'nullable|string',
            'units_is_pta' => 'nullable|array',
            'units_is_pta.*' => 'nullable|boolean',
            'meta_data' => 'nullable|json',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        } elseif ($request->input('delete_image') == '1') {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = null;
        }

        if ($request->has('meta_data')) {
            $validated['meta_data'] = json_decode($request->input('meta_data'), true);
        }

        $product->update($validated);

        $businessType = Auth::user()->storeSetting->business_type ?? 'mobile';

        if ($businessType === 'mobile' && in_array($product->type, ['mobile', 'tablet', 'laptop'])) {
            $stock = (int) ($product->stock ?: 1);
            $imeisArray = $request->input('units_imeis', []);
            $isPtaArray = $request->input('units_is_pta', []);
            
            // Remove currently available units
            $product->stockUnits()->where('status', 'available')->delete();
            
            // Re-create available units based on new stock
            for ($i = 0; $i < $stock; $i++) {
                ProductStockUnit::create([
                    'product_id' => $product->id,
                    'imeis' => $imeisArray[$i] ?? null,
                    'is_pta' => !empty($isPtaArray[$i]) && ($isPtaArray[$i] == '1' || $isPtaArray[$i] == 'true'),
                    'status' => 'available',
                ]);
            }
        }

        return response()->json($product->load('stockUnits'));
    }

    public function destroy(Product $product)
    {
        if ($product->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }

    public function salesHistory(Product $product)
    {
        if ($product->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $sales = \App\Models\OrderItem::where('product_id', $product->id)
            ->whereHas('order', function($q) {
                $q->where('user_id', Auth::id());
            })
            ->with(['order', 'order.buyer', 'stockUnits'])
            ->latest('id')
            ->get()
            ->map(function($item) {
                $isRefunded = (bool)($item->is_refunded || ($item->order && $item->order->payment_status === 'refunded'));
                
                $imeiUnits = [];
                if ($item->stockUnits && $item->stockUnits->count() > 0) {
                    foreach ($item->stockUnits as $unit) {
                        $imeiUnits[] = [
                            'id' => $unit->id,
                            'imeis' => $unit->imeis,
                            'is_pta' => (bool)$unit->is_pta,
                            'status' => $unit->status,
                        ];
                    }
                } elseif (!empty($item->imeis)) {
                    $rawImeis = preg_split('/[\s\|\,]+/', $item->imeis);
                    $rawImeis = array_values(array_filter(array_map('trim', $rawImeis)));
                    foreach ($rawImeis as $imeiStr) {
                        $existingUnit = \App\Models\ProductStockUnit::where('product_id', $item->product_id)
                            ->where('imeis', $imeiStr)
                            ->first();
                        $imeiUnits[] = [
                            'id' => $existingUnit ? $existingUnit->id : null,
                            'imeis' => $imeiStr,
                            'is_pta' => $existingUnit ? (bool)$existingUnit->is_pta : false,
                            'status' => $existingUnit ? $existingUnit->status : 'sold',
                        ];
                    }
                }

                return [
                    'id' => $item->id,
                    'date' => $item->order->created_at->format('Y-m-d H:i:s'),
                    'order_id' => $item->order_id,
                    'is_installment' => (bool)$item->order->is_installment,
                    'order_type' => $item->order->is_installment ? 'Installment' : 'Billing',
                    'customer' => $item->order->buyer ? $item->order->buyer->name : ($item->order->customer_name ?: 'Walk-in'),
                    'qty' => (float)$item->qty,
                    'price' => (float)$item->sell_price,
                    'total' => (float)($item->qty * $item->sell_price),
                    'imeis' => $item->imeis,
                    'imei_units' => $imeiUnits,
                    'is_refunded' => $isRefunded,
                    'order_payment_status' => $item->order->payment_status
                ];
            });

        return response()->json($sales);
    }

    private function removeOrderLedgers(\App\Models\Order $order)
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

    public function refundStockUnit(ProductStockUnit $stockUnit)
    {
        $product = $stockUnit->product;
        if (!$product || $product->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($stockUnit->status === 'available') {
            return response()->json(['success' => false, 'message' => 'This unit is already available in stock.'], 400);
        }

        try {
            DB::beginTransaction();

            $orderItem = $stockUnit->order_item_id ? \App\Models\OrderItem::with('order')->find($stockUnit->order_item_id) : null;

            // Make stock unit available
            $stockUnit->status = 'available';
            $stockUnit->order_item_id = null;
            $stockUnit->save();

            // Increase product stock
            $product->stock += 1;
            if ($product->status === 'sold') {
                $product->status = 'in_stock';
            }
            $product->save();

            // If attached to an order item, refund / update order item & order
            if ($orderItem) {
                $order = $orderItem->order;
                
                if ($orderItem->qty > 1) {
                    // Split into active and refunded item
                    $remainingQty = $orderItem->qty - 1;

                    \App\Models\OrderItem::create([
                        'order_id' => $orderItem->order_id,
                        'product_id' => $orderItem->product_id,
                        'seller_id' => $orderItem->seller_id,
                        'qty' => 1,
                        'buy_price' => $orderItem->buy_price,
                        'sell_price' => $orderItem->sell_price,
                        'user_id' => $orderItem->user_id,
                        'imeis' => $stockUnit->imeis,
                        'meta_data' => $orderItem->meta_data,
                        'is_refunded' => true,
                        'refunded_at' => now(),
                    ]);

                    $orderItem->qty = $remainingQty;
                    $remainingUnits = \App\Models\ProductStockUnit::where('order_item_id', $orderItem->id)->get();
                    $orderItem->imeis = $remainingUnits->pluck('imeis')->filter()->implode(' | ') ?: null;
                    $orderItem->save();
                } else {
                    $orderItem->is_refunded = true;
                    $orderItem->refunded_at = now();
                    $orderItem->save();
                }

                if ($order) {
                    $activeItems = $order->items()->where('is_refunded', false)->get();
                    if ($activeItems->count() === 0) {
                        $order->payment_status = 'refunded';
                        $order->subtotal = 0;
                        $order->total = 0;
                        $order->paid_amount = 0;
                        $order->due_amount = 0;

                        // Remove ledgers
                        $this->removeOrderLedgers($order);

                        $installment = \App\Models\Installment::where('order_id', $order->id)->first();
                        if ($installment) {
                            $installment->status = 'Refunded';
                            $installment->save();
                        }
                    } else {
                        $newSubtotal = $activeItems->sum(fn($it) => $it->qty * $it->sell_price);
                        $order->subtotal = $newSubtotal;
                        $order->total = max(0, $newSubtotal - $order->discount + $order->tax);
                        $order->paid_amount = min($order->paid_amount, $order->total);
                        $order->due_amount = max(0, $order->total - $order->paid_amount);

                        $installment = \App\Models\Installment::where('order_id', $order->id)->first();
                        if ($installment) {
                            $installment->actual_price = max(0, $installment->actual_price - $orderItem->sell_price);
                            $interest = $installment->interest_percentage ?? 0;
                            $installment->total_amount = $installment->actual_price + ($installment->actual_price * ($interest / 100));
                            $months = $order->installment_months ?: 1;
                            $remainingAfterDown = max(0, $installment->total_amount - $installment->down_payment);
                            $installment->agreed_monthly_amount = $months > 0 ? ($remainingAfterDown / $months) : 0;
                            $installment->save();
                        }
                    }
                    $order->save();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'IMEI refunded and returned to stock successfully.',
                'product' => $product->fresh('stockUnits')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function barcodeLookup($barcode)
    {
        if (Auth::user()->type !== 'shop' && !Auth::user()->hasPrivilege('shop.products.index')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Try OpenFoodFacts/OpenBeautyFacts first
        $response = \Illuminate\Support\Facades\Http::timeout(5)->get("https://world.openfoodfacts.org/api/v2/product/{$barcode}.json");
        
        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['product'])) {
                $product = $data['product'];
                return response()->json([
                    'found' => true,
                    'name' => $product['product_name'] ?? null,
                    'brand' => $product['brands'] ?? null,
                    'weight' => $product['quantity'] ?? null,
                ]);
            }
        }

        // Fallback to UPCitemdb
        $response = \Illuminate\Support\Facades\Http::timeout(5)->get("https://api.upcitemdb.com/prod/trial/lookup", [
            'upc' => $barcode
        ]);

        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['items']) && count($data['items']) > 0) {
                $item = $data['items'][0];
                return response()->json([
                    'found' => true,
                    'name' => $item['title'] ?? null,
                    'brand' => $item['brand'] ?? null,
                    'weight' => $item['weight'] ?? null, // UPCitemdb may have weight, model, etc.
                ]);
            }
        }

        return response()->json(['found' => false, 'message' => 'Product not found in global databases.']);
    }

    public function reportLoss(Request $request, Product $product)
    {
        if ($product->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'qty' => 'required|numeric|min:1|max:' . ($product->stock ?: 0),
        ]);

        $qty = $validated['qty'];
        $lossAmount = $qty * ($product->purchase_price ?: 0);

        // Deduct stock
        $product->stock -= $qty;
        $product->save();
        
        // Mark available units as lost (if applicable)
        $businessType = Auth::user()->storeSetting->business_type ?? 'mobile';
        if ($businessType === 'mobile' && in_array($product->type, ['mobile', 'tablet', 'laptop'])) {
             $unitsToMark = $product->stockUnits()->where('status', 'available')->limit($qty)->get();
             foreach($unitsToMark as $unit) {
                 $unit->status = 'lost';
                 $unit->save();
             }
        }

        // Create Expense
        \App\Models\Expense::create([
            'user_id' => Auth::id(),
            'title' => 'Product Loss',
            'amount' => $lossAmount,
            'expense_date' => now()->toDateString(),
            'description' => "Product: {$product->name} (Code: {$product->code}) - Qty Lost: {$qty}, Purchase Price: " . ($product->purchase_price ?: 0),
        ]);

        return response()->json(['message' => 'Loss reported successfully']);
    }
}
