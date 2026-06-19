<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    public function index()
    {
        return view('customers.index');
    }

    public function apiIndex()
    {
        $customers = Customer::where('user_id', Auth::id())->get();
        return response()->json($customers);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'cnic_number' => 'nullable|string|max:255',
            'cnic_front' => 'nullable|image|max:2048',
            'cnic_back' => 'nullable|image|max:2048',
        ]);

        $validated['user_id'] = Auth::id();

        if ($request->hasFile('cnic_front')) {
            $validated['cnic_front'] = $request->file('cnic_front')->store('cnic', 'public');
        }
        if ($request->hasFile('cnic_back')) {
            $validated['cnic_back'] = $request->file('cnic_back')->store('cnic', 'public');
        }

        $customer = Customer::create($validated);

        return response()->json($customer, 201);
    }

    public function update(Request $request, Customer $customer)
    {
        if ($customer->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'cnic_number' => 'nullable|string|max:255',
            'cnic_front' => 'nullable|image|max:2048',
            'cnic_back' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('cnic_front')) {
            if ($customer->cnic_front) {
                Storage::disk('public')->delete($customer->cnic_front);
            }
            $validated['cnic_front'] = $request->file('cnic_front')->store('cnic', 'public');
        }

        if ($request->hasFile('cnic_back')) {
            if ($customer->cnic_back) {
                Storage::disk('public')->delete($customer->cnic_back);
            }
            $validated['cnic_back'] = $request->file('cnic_back')->store('cnic', 'public');
        }

        $customer->update($validated);

        return response()->json($customer);
    }

    public function destroy(Customer $customer)
    {
        if ($customer->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($customer->cnic_front) {
            Storage::disk('public')->delete($customer->cnic_front);
        }
        if ($customer->cnic_back) {
            Storage::disk('public')->delete($customer->cnic_back);
        }

        $customer->delete();

        return response()->json(['message' => 'Customer deleted successfully']);
    }
}
