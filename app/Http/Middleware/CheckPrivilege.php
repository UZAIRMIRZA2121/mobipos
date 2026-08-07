<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPrivilege
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Check if a staff is logged in
        if (session()->has('staff_id')) {
            $staff = \App\Models\Staff::find(session('staff_id'));
            
            if (!$staff) {
                auth()->logout();
                session()->invalidate();
                return redirect()->route('staff.login')->withErrors(['email' => 'Session expired.']);
            }

            // Kick if the store owner made them offline
            if (!$staff->is_online) {
                auth()->logout();
                session()->invalidate();
                return redirect()->route('staff.login')->withErrors(['email' => 'You have been disconnected by the store owner.']);
            }
        }

        $routeName = $request->route()->getName();

        if ($routeName && !$user->hasPrivilege($routeName)) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action. You do not have the required privileges to access this page.');
        }

        return $next($request);
    }
}
