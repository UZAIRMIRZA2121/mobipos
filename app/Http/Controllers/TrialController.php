<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TrialController extends Controller
{
    public function show()
    {
        return view('auth.trial-ended');
    }

    public function verify(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'otp' => 'required|string',
        ]);

        $user = clone \Illuminate\Support\Facades\Auth::user();

        if ($user->otp === $request->otp) {
            // Using DB directly to bypass mass assignment if any issue, though we added it to fillable.
            $user = \App\Models\User::find($user->id);
            $user->status = 1;
            $user->otp = null;
            $user->save();

            return redirect()->route('dashboard')->with('success', 'Your account has been successfully approved!');
        }

        return back()->withErrors(['otp' => 'The provided OTP is incorrect.']);
    }

    public function resend(\Illuminate\Http\Request $request)
    {
        $user = clone \Illuminate\Support\Facades\Auth::user();
        
        $otp = rand(100000, 999999);
        
        $dbUser = \App\Models\User::find($user->id);
        $dbUser->otp = $otp;
        $dbUser->save();

        session(['last_otp_time' => now()]);

        try {
            $adminEmail = 'mirzauzair2121@gmail.com';
            \Illuminate\Support\Facades\Mail::to($adminEmail)->send(new \App\Mail\TrialOtpMail($dbUser, $otp));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send Trial OTP mail on resend: ' . $e->getMessage());
        }

        return back()->with('status', 'A new OTP has been generated and sent to the admin.');
    }
}
