<?php

namespace App\Http\Controllers;

use App\Models\Addon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddonController extends Controller
{
    public function index()
    {
        $addons = Addon::where('user_id', Auth::id())->get();
        return view('addons.index', compact('addons'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        Addon::create([
            'user_id' => Auth::id(),
            'name' => $request->name
        ]);
        return back()->with('success', 'Addon added successfully');
    }

    public function destroy(Addon $addon)
    {
        if ($addon->user_id == Auth::id()) {
            $addon->delete();
        }
        return back()->with('success', 'Addon deleted successfully');
    }
}
