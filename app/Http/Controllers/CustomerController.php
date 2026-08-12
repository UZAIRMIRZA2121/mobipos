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
            'agreements_images.*' => 'nullable|image|max:2048',
        ]);

        $validated['user_id'] = Auth::id();

        if ($request->hasFile('cnic_front')) {
            $validated['cnic_front'] = $request->file('cnic_front')->store('cnic', 'public');
        }
        if ($request->hasFile('cnic_back')) {
            $validated['cnic_back'] = $request->file('cnic_back')->store('cnic', 'public');
        }

        $agreementsImages = [];
        if ($request->hasFile('agreements_images')) {
            foreach ($request->file('agreements_images') as $image) {
                $agreementsImages[] = $image->store('agreements', 'public');
            }
        }
        $validated['agreements_images'] = $agreementsImages;

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
            'agreements_images.*' => 'nullable|image|max:2048',
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

        $agreementsImages = $customer->agreements_images ?? [];
        if ($request->hasFile('agreements_images')) {
            foreach ($request->file('agreements_images') as $image) {
                $agreementsImages[] = $image->store('agreements', 'public');
            }
        }
        $validated['agreements_images'] = $agreementsImages;

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
        
        if (!empty($customer->agreements_images)) {
            foreach ($customer->agreements_images as $img) {
                Storage::disk('public')->delete($img);
            }
        }

        $customer->delete();

        return response()->json(['message' => 'Customer deleted successfully']);
    }

    public function deleteAgreementImage(Customer $customer, $index)
    {
        if ($customer->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $images = $customer->agreements_images ?? [];
        if (isset($images[$index])) {
            Storage::disk('public')->delete($images[$index]);
            unset($images[$index]);
            $customer->update(['agreements_images' => array_values($images)]);
        }

        return response()->json($customer);
    }

    public function deleteCnicImage(Customer $customer, $type)
    {
        if ($customer->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($type === 'front' && $customer->cnic_front) {
            Storage::disk('public')->delete($customer->cnic_front);
            $customer->update(['cnic_front' => null]);
        } elseif ($type === 'back' && $customer->cnic_back) {
            Storage::disk('public')->delete($customer->cnic_back);
            $customer->update(['cnic_back' => null]);
        }

        return response()->json($customer);
    }
}
