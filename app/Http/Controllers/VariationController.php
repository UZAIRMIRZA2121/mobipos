<?php

namespace App\Http\Controllers;

use App\Models\Variation;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VariationController extends Controller
{
    public function index()
    {
        $variations  = Variation::with('category')->where('user_id', Auth::id())->get();
        $categories  = Category::where('user_id', Auth::id())->orderBy('name')->get();
        return view('variations.index', compact('variations', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'cat_id' => 'nullable|exists:categories,id',
        ]);
        Variation::create([
            'user_id' => Auth::id(),
            'cat_id'  => $request->cat_id ?: null,
            'name'    => $request->name,
        ]);
        return back()->with('success', 'Variation added successfully');
    }

    public function update(Request $request, Variation $variation)
    {
        if ($variation->user_id != Auth::id()) abort(403);
        $request->validate([
            'name'   => 'required|string|max:255',
            'cat_id' => 'nullable|exists:categories,id',
        ]);
        $variation->update([
            'cat_id' => $request->cat_id ?: null,
            'name'   => $request->name,
        ]);
        return back()->with('success', 'Variation updated successfully');
    }

    public function destroy(Variation $variation)
    {
        if ($variation->user_id == Auth::id()) {
            $variation->delete();
        }
        return back()->with('success', 'Variation deleted successfully');
    }
}
