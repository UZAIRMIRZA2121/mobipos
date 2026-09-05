<?php

namespace App\Http\Controllers;

use App\Models\Variation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VariationController extends Controller
{
    public function index()
    {
        $variations = Variation::where('user_id', Auth::id())->get();
        return view('variations.index', compact('variations'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        Variation::create([
            'user_id' => Auth::id(),
            'name' => $request->name
        ]);
        return back()->with('success', 'Variation added successfully');
    }

    public function destroy(Variation $variation)
    {
        if ($variation->user_id == Auth::id()) {
            $variation->delete();
        }
        return back()->with('success', 'Variation deleted successfully');
    }
}
