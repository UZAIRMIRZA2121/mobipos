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
        return response()->json($settings ?: [
            'discount' => 0, 
            'tax' => 0, 
            'business_type' => null,
            'ultramsg_api_url' => null,
            'ultramsg_instance_id' => null,
            'ultramsg_token' => null,
            'whatsapp_config' => false
        ]);
    }

    public function apiUpdate(Request $request)
    {
        $validated = $request->validate([
            'discount' => 'nullable|numeric|min:0|max:100',
            'tax' => 'nullable|numeric|min:0|max:100',
            'business_type' => 'nullable|string|in:mobile,cosmetics,garments,shoes,retail_grocery,toys,cloth,fast_food',
            'ultramsg_api_url' => 'nullable|string|max:255',
            'ultramsg_instance_id' => 'nullable|string|max:255',
            'ultramsg_token' => 'nullable|string|max:255',
            'whatsapp_config' => 'nullable|boolean',
        ]);

        $settings = StoreSetting::where('user_id', Auth::id())->first();
        if (!$settings) {
            $settings = new StoreSetting(['user_id' => Auth::id()]);
        }

        if (array_key_exists('discount', $validated) && $validated['discount'] !== null) {
            $settings->discount = $validated['discount'];
        } else {
            $settings->discount = $settings->discount ?? 0;
        }

        if (array_key_exists('tax', $validated) && $validated['tax'] !== null) {
            $settings->tax = $validated['tax'];
        } else {
            $settings->tax = $settings->tax ?? 0;
        }

        if (array_key_exists('business_type', $validated) && $validated['business_type']) {
            $settings->business_type = $validated['business_type'];
        }

        if (array_key_exists('whatsapp_config', $validated)) {
            $settings->whatsapp_config = $validated['whatsapp_config'] ? 1 : 0;
        }

        if (array_key_exists('ultramsg_api_url', $validated)) {
            $settings->ultramsg_api_url = $validated['ultramsg_api_url'];
        }

        if (array_key_exists('ultramsg_instance_id', $validated)) {
            $settings->ultramsg_instance_id = $validated['ultramsg_instance_id'];
        }

        if (array_key_exists('ultramsg_token', $validated)) {
            $settings->ultramsg_token = $validated['ultramsg_token'];
        }

        $settings->save();

        return response()->json(['message' => 'Settings updated successfully!', 'settings' => $settings]);
    }
}
