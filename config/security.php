<?php
/**
 * Security configuration.
 */
return [
    // Session
    'session_name'      => 'KYROS_SESSID',
    'session_lifetime'  => 7200,          // seconds (2h)
    'cookie_httponly'   => true,
    'cookie_samesite'   => 'Lax',         // Lax | Strict
    // Set true only when serving over HTTPS, otherwise cookies break on http.
    'cookie_secure'     => false,

    // CSRF
    'csrf_token_name'   => '_csrf',
    'csrf_lifetime'     => 7200,

    // Brute force / login throttling
    'login_max_attempts'   => 5,          // failed attempts...
    'login_decay_minutes'  => 15,         // ...within this window triggers lockout
    'login_lockout_minutes'=> 15,

    // Password policy (min length enforced server-side)
    'password_min_length'  => 8,

    // File uploads
    'upload_max_bytes'   => 5 * 1024 * 1024,   // 5 MB
    'upload_allowed_mime'=> [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'application/pdf' => 'pdf',
    ],

    // Security headers applied globally
    'headers' => [
        'X-Frame-Options'        => 'SAMEORIGIN',
        'X-Content-Type-Options' => 'nosniff',
        'Referrer-Policy'        => 'strict-origin-when-cross-origin',
        'Permissions-Policy'     => 'geolocation=(self), camera=(), microphone=()',
        // CSP allows the CDNs we use (Tailwind, Chart.js, FullCalendar, AOS, Alpine, Lucide, fonts).
        'Content-Security-Policy'=> "default-src 'self'; "
            . "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://unpkg.com; "
            . "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://unpkg.com https://fonts.googleapis.com; "
            . "font-src 'self' https://fonts.gstatic.com data:; "
            . "img-src 'self' data: https:; "
            . "connect-src 'self'; "
            . "frame-ancestors 'self';",
    ],
];
