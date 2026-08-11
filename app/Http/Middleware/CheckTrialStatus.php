<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTrialStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (\Illuminate\Support\Facades\Auth::check()) {
            $user = clone \Illuminate\Support\Facades\Auth::user();
            if ($user->status == 0 && $user->created_at->diffInDays(\Carbon\Carbon::now()) >= 7) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['message' => 'Your trial account has ended.'], 403);
                }
                
                if (!$request->routeIs('trial.ended') && !$request->routeIs('trial.verify') && !$request->routeIs('trial.resend') && !$request->routeIs('logout')) {
                    return redirect()->route('trial.ended');
                }
            }
        }

        return $next($request);
    }
}
