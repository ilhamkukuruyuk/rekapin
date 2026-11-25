<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ExpireInactiveSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $lastActivity = $request->session()->get('last_activity_at');
        $idleTimeout = (int) config('session.idle_timeout', 30) * 60;

        if (! is_numeric($lastActivity) || now()->timestamp - (int) $lastActivity >= $idleTimeout) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Sesi Anda berakhir karena tidak aktif. Silakan masuk kembali.');
        }

        $request->session()->put('last_activity_at', now()->timestamp);

        return $next($request);
    }
}
