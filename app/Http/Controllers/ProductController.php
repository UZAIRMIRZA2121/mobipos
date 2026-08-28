<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductStockUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        return view('products.index');
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
            'type' => 'required|in:mobile,tablet,laptop,accessory',
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
            'purchase_price' => 'nullable|numeric|min:0|lt:sale_price',
            'sale_price' => 'required|numeric|min:0',
            'status' => 'required|in:in_stock,sold,in_repair',
            'stock' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'buyer_id' => 'nullable|exists:customers,id',
            'category_id' => 'nullable|exists:categories,id',
            'units_imeis' => 'nullable|array',
            'units_imeis.*' => 'nullable|string',
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
            
            for ($i = 0; $i < $stock; $i++) {
                ProductStockUnit::create([
                    'product_id' => $product->id,
                    'imeis' => $imeisArray[$i] ?? null,
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
            'type' => 'required|in:mobile,tablet,laptop,accessory',
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
            'purchase_price' => 'nullable|numeric|min:0|lt:sale_price',
            'sale_price' => 'required|numeric|min:0',
            'status' => 'required|in:in_stock,sold,in_repair',
            'stock' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'buyer_id' => 'nullable|exists:customers,id',
            'category_id' => 'nullable|exists:categories,id',
            'units_imeis' => 'nullable|array',
            'units_imeis.*' => 'nullable|string',
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
            
            // Remove currently available units
            $product->stockUnits()->where('status', 'available')->delete();
            
            // Re-create available units based on new stock
            for ($i = 0; $i < $stock; $i++) {
                ProductStockUnit::create([
                    'product_id' => $product->id,
                    'imeis' => $imeisArray[$i] ?? null,
                    'status' => 'available',
                ]);
            }
        }

        return response()->json($product->load(['stockUnits' => function($q) {
            $q->where('status', 'available');
        }]));
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
            ->with(['order', 'order.buyer'])
            ->get()
            ->map(function($item) {
                return [
                    'date' => $item->order->created_at->format('Y-m-d H:i:s'),
                    'order_id' => $item->order_id,
                    'customer' => $item->order->buyer ? $item->order->buyer->name : 'Walk-in',
                    'qty' => $item->qty,
                    'price' => $item->sell_price,
                    'total' => $item->qty * $item->sell_price
                ];
            });

        return response()->json($sales);
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
