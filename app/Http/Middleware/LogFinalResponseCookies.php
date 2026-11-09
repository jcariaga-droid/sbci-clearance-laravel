<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogFinalResponseCookies
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        Log::info('SBCI FINAL RESPONSE COOKIE CHECK', [
            'cookie_names' => array_map(
                fn ($cookie) => $cookie->getName(),
                $response->headers->getCookies()
            ),
        ]);

        return $response;
    }
}