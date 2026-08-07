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
            'footer_text' => ''
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
        ]);

        $settings = InvoiceSetting::updateOrCreate(
            ['user_id' => Auth::id()],
            $validated
        );

        return response()->json(['message' => 'Print settings updated successfully!', 'settings' => $settings]);
    }
}
