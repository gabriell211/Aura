<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (! (bool) config('security_headers.enabled', true)) {
            return $response;
        }

        $response->headers->set('X-Content-Type-Options', (string) config('security_headers.x_content_type_options', 'nosniff'));
        $response->headers->set('X-Frame-Options', (string) config('security_headers.x_frame_options', 'DENY'));
        $response->headers->set('Referrer-Policy', (string) config('security_headers.referrer_policy', 'strict-origin-when-cross-origin'));
        $response->headers->set('Permissions-Policy', (string) config('security_headers.permissions_policy', 'accelerometer=(), camera=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=()'));
        $response->headers->set('Cross-Origin-Opener-Policy', (string) config('security_headers.cross_origin_opener_policy', 'same-origin'));
        $response->headers->set('Cross-Origin-Resource-Policy', (string) config('security_headers.cross_origin_resource_policy', 'same-origin'));
        $response->headers->set('X-Permitted-Cross-Domain-Policies', (string) config('security_headers.x_permitted_cross_domain_policies', 'none'));
        $response->headers->set('X-XSS-Protection', (string) config('security_headers.x_xss_protection', '0'));

        $csp = (string) config('security_headers.csp', '');

        if ($csp !== '') {
            $response->headers->set('Content-Security-Policy', $csp);
        }

        if ($request->isSecure() && (bool) config('security_headers.hsts.enabled', true)) {
            $maxAge = (int) config('security_headers.hsts.max_age', 31536000);
            $hsts = 'max-age='.$maxAge;

            if ((bool) config('security_headers.hsts.include_subdomains', true)) {
                $hsts .= '; includeSubDomains';
            }

            if ((bool) config('security_headers.hsts.preload', false)) {
                $hsts .= '; preload';
            }

            $response->headers->set('Strict-Transport-Security', $hsts);
        }

        return $response;
    }
}

