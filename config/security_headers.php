<?php

return [
    'enabled' => (bool) env('SECURITY_HEADERS_ENABLED', true),

    'x_content_type_options' => env('SECURITY_HEADERS_X_CONTENT_TYPE_OPTIONS', 'nosniff'),
    'x_frame_options' => env('SECURITY_HEADERS_X_FRAME_OPTIONS', 'DENY'),
    'referrer_policy' => env('SECURITY_HEADERS_REFERRER_POLICY', 'strict-origin-when-cross-origin'),
    'permissions_policy' => env(
        'SECURITY_HEADERS_PERMISSIONS_POLICY',
        "accelerometer=(), autoplay=(), camera=(), display-capture=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=()"
    ),
    'cross_origin_opener_policy' => env('SECURITY_HEADERS_COOP', 'same-origin'),
    'cross_origin_resource_policy' => env('SECURITY_HEADERS_CORP', 'same-origin'),
    'x_permitted_cross_domain_policies' => env('SECURITY_HEADERS_X_PERMITTED_CROSS_DOMAIN_POLICIES', 'none'),
    'x_xss_protection' => env('SECURITY_HEADERS_X_XSS_PROTECTION', '0'),

    'csp' => env(
        'SECURITY_HEADERS_CSP',
        "default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'; object-src 'none'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net; font-src 'self' https://fonts.gstatic.com https://fonts.bunny.net data:; img-src 'self' data: https:; connect-src 'self'; frame-src 'none'"
    ),

    'hsts' => [
        'enabled' => (bool) env('SECURITY_HEADERS_HSTS_ENABLED', true),
        'max_age' => (int) env('SECURITY_HEADERS_HSTS_MAX_AGE', 31536000),
        'include_subdomains' => (bool) env('SECURITY_HEADERS_HSTS_INCLUDE_SUBDOMAINS', true),
        'preload' => (bool) env('SECURITY_HEADERS_HSTS_PRELOAD', false),
    ],
];
