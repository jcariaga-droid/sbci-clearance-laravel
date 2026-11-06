<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Admin and Staff do not require email verification
        if ($user->role !== 'student') {
            return $next($request);
        }

        // Students must verify their email
        if (empty($user->email_verified_at)) {
            return redirect()->route('verification.notice');
        }

        return $next($request);
    }
}
