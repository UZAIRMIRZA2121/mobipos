<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Staff;
use Illuminate\Support\Facades\Auth;

class StaffAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.staff-login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|string',
        ]);

        $staff = Staff::where('email', $request->email)
                      ->where('otp', $request->otp)
                      ->first();

        if (!$staff) {
            return back()->withErrors(['email' => 'Invalid email or OTP'])->withInput();
        }

        if ($staff->status !== 'active') {
            return back()->withErrors(['email' => 'Your account is inactive. Please contact the administrator.'])->withInput();
        }

        // Nullify OTP and set online
        $staff->otp = null;
        $staff->is_online = true;
        $staff->save();

        // Log in as the Shop Owner
        Auth::loginUsingId($staff->user_id);
        
        // Store staff_id in session
        session()->put('staff_id', $staff->id);

        return redirect()->route('shop.dashboard')->with('success', 'Logged in successfully as ' . $staff->name);
    }

    public function logout(Request $request)
    {
        if (session()->has('staff_id')) {
            $staff = Staff::find(session('staff_id'));
            if ($staff) {
                $staff->is_online = false;
                $staff->save();
            }
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('staff.login');
    }
}
