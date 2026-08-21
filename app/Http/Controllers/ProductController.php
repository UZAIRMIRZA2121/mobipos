<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductStockUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
            'code' => 'nullable|string|max:255|unique:products',
            'barcode' => 'nullable|string|max:255|unique:products',
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
        ]);

        $validated['user_id'] = Auth::id();

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($validated);

        if (in_array($product->type, ['mobile', 'tablet', 'laptop'])) {
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
            'code' => 'nullable|string|max:255|unique:products,code,' . $product->id,
            'barcode' => 'nullable|string|max:255|unique:products,barcode,' . $product->id,
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

        $product->update($validated);

        if (in_array($product->type, ['mobile', 'tablet', 'laptop'])) {
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
}
