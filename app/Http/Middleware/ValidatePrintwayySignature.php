<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidatePrintwayySignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedToken = (string) config('services.printwayy.webhook_token', '');

        if ($expectedToken === '') {
            return $next($request);
        }

        $providedToken = (string) $request->header('X-Printwayy-Token', '');

        if (! hash_equals($expectedToken, $providedToken)) {
            abort(401, 'Invalid Printwayy token.');
        }

        return $next($request);
    }
}
