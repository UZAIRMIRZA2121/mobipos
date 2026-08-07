<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InvoiceSetting;
use Illuminate\Support\Facades\Auth;

class InvoiceSettingController extends Controller
{
    public function index()
    {
        return view('shop.print_settings');
    }

    public function apiGet()
    {
        $settings = InvoiceSetting::where('user_id', Auth::id())->first();
        return response()->json($settings ?: [
            'store_name' => '',
            'header_text' => '',
            'address' => '',
            'phone' => '',
            'footer_text' => '',
            'logo' => null,
            'logo_size' => 120
        ]);
    }

    public function apiUpdate(Request $request)
    {
        $validated = $request->validate([
            'store_name' => 'nullable|string|max:255',
            'header_text' => 'nullable|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:255',
            'footer_text' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'logo_size' => 'nullable|integer|min:40|max:250',
        ]);

        if ($request->has('remove_logo') && $request->remove_logo == '1') {
            $validated['logo'] = null;
        } elseif ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            $validated['logo'] = '/storage/' . $path;
        }

        $settings = InvoiceSetting::updateOrCreate(
            ['user_id' => Auth::id()],
            $validated
        );

        return response()->json(['message' => 'Print settings updated successfully!', 'settings' => $settings]);
    }
}
