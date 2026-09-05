<?php

namespace App\Http\Controllers;

use App\Models\Addon;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddonController extends Controller
{
    public function index()
    {
        $addons    = Addon::with('category')->where('user_id', Auth::id())->get();
        $categories = Category::where('user_id', Auth::id())->orderBy('name')->get();
        return view('addons.index', compact('addons', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'cat_id' => 'nullable|exists:categories,id',
        ]);
        Addon::create([
            'user_id' => Auth::id(),
            'cat_id'  => $request->cat_id ?: null,
            'name'    => $request->name,
        ]);
        return back()->with('success', 'Addon added successfully');
    }

    public function update(Request $request, Addon $addon)
    {
        if ($addon->user_id != Auth::id()) abort(403);
        $request->validate([
            'name'   => 'required|string|max:255',
            'cat_id' => 'nullable|exists:categories,id',
        ]);
        $addon->update([
            'cat_id' => $request->cat_id ?: null,
            'name'   => $request->name,
        ]);
        return back()->with('success', 'Addon updated successfully');
    }

    public function destroy(Addon $addon)
    {
        if ($addon->user_id == Auth::id()) {
            $addon->delete();
        }
        return back()->with('success', 'Addon deleted successfully');
    }
}
