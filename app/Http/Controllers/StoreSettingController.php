<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StoreSetting;
use Illuminate\Support\Facades\Auth;

class StoreSettingController extends Controller
{
    public function index()
    {
        return view('shop.settings');
    }

    public function apiGet()
    {
        $settings = StoreSetting::where('user_id', Auth::id())->first();
        return response()->json($settings ?: ['discount' => 0, 'tax' => 0]);
    }

    public function apiUpdate(Request $request)
    {
        $validated = $request->validate([
            'discount' => 'numeric|min:0|max:100',
            'tax' => 'numeric|min:0|max:100',
        ]);

        $settings = StoreSetting::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'discount' => $validated['discount'],
                'tax' => $validated['tax']
            ]
        );

        return response()->json(['message' => 'Settings updated successfully!', 'settings' => $settings]);
    }
}
