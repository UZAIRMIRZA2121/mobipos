<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StaffController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $staffs = \App\Models\Staff::where('user_id', auth()->id())->get();
        $privileges = \App\Models\Privilege::all();
        return view('shop.staff.index', compact('staffs', 'privileges'));
    }

    public function create()
    {
        $privileges = \App\Models\Privilege::all();
        return view('shop.staff.create', compact('privileges'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:staff,email',
            'status' => 'required|in:active,inactive',
            'staff_type' => 'required|string|max:255',
            'privileges' => 'nullable|array',
        ]);

        $data = $request->except('privileges');
        $data['user_id'] = auth()->id();
        $data['privileges'] = $request->privileges ? implode(',', $request->privileges) : null;
        $data['otp'] = rand(100000, 999999); // Generate a random 6-digit OTP

        \App\Models\Staff::create($data);

        return redirect()->route('shop.staff.index')->with('success', 'Staff member added successfully.');
    }

    public function edit(string $id)
    {
        $staff = \App\Models\Staff::where('user_id', auth()->id())->findOrFail($id);
        $privileges = \App\Models\Privilege::all();
        $staffPrivileges = $staff->privileges ? explode(',', $staff->privileges) : [];
        return view('shop.staff.edit', compact('staff', 'privileges', 'staffPrivileges'));
    }

    public function update(Request $request, string $id)
    {
        $staff = \App\Models\Staff::where('user_id', auth()->id())->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:staff,email,'.$id,
            'status' => 'required|in:active,inactive',
            'staff_type' => 'required|string|max:255',
            'privileges' => 'nullable|array',
        ]);

        $data = $request->except('privileges');
        $data['privileges'] = $request->privileges ? implode(',', $request->privileges) : null;

        $staff->update($data);

        return redirect()->route('shop.staff.index')->with('success', 'Staff member updated successfully.');
    }

    public function destroy(string $id)
    {
        $staff = \App\Models\Staff::where('user_id', auth()->id())->findOrFail($id);
        $staff->delete();

        return redirect()->route('shop.staff.index')->with('success', 'Staff member deleted successfully.');
    }

    public function generateOtp(string $id)
    {
        $staff = \App\Models\Staff::where('user_id', auth()->id())->findOrFail($id);
        $staff->otp = rand(100000, 999999);
        $staff->save();

        return redirect()->route('shop.staff.index')->with('success', 'New OTP generated for ' . $staff->name);
    }

    public function forceOffline(string $id)
    {
        $staff = \App\Models\Staff::where('user_id', auth()->id())->findOrFail($id);
        $staff->is_online = false;
        $staff->save();

        return redirect()->route('shop.staff.index')->with('success', $staff->name . ' has been forced offline.');
    }
}
