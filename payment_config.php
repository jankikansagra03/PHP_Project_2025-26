<?php
/**
 * payment_config.php
 * ─ Central configuration for all payment gateways.
 * ─ All values are loaded from .env — edit .env to change them.
 */

if (!function_exists('env')) {
    require_once __DIR__ . '/env.php';
}

// ══════════════════════════════════════════════════════
//  CASHFREE
// ══════════════════════════════════════════════════════
define('CF_APP_ID',     env('CASHFREE_APP_ID',     'YOUR_CASHFREE_APP_ID'));
define('CF_SECRET_KEY', env('CASHFREE_SECRET_KEY', 'YOUR_CASHFREE_SECRET_KEY'));
define('CF_ENV',        env('CASHFREE_ENV',        'sandbox')); // sandbox | production
define('CF_API_BASE',   CF_ENV === 'production'
    ? 'https://api.cashfree.com/pg'
    : 'https://sandbox.cashfree.com/pg');
define('CF_API_VERSION','2023-08-01');
define('CF_JS_ENV',     CF_ENV === 'production' ? 'production' : 'sandbox');

// ══════════════════════════════════════════════════════
//  RAZORPAY
// ══════════════════════════════════════════════════════
define('RPY_KEY_ID',     env('RAZORPAY_KEY_ID',     'YOUR_RAZORPAY_KEY_ID'));
define('RPY_KEY_SECRET', env('RAZORPAY_KEY_SECRET', 'YOUR_RAZORPAY_KEY_SECRET'));

// ══════════════════════════════════════════════════════
//  PAYPAL
// ══════════════════════════════════════════════════════
define('PP_CLIENT_ID',     env('PAYPAL_CLIENT_ID',     'YOUR_PAYPAL_CLIENT_ID'));
define('PP_CLIENT_SECRET', env('PAYPAL_CLIENT_SECRET', 'YOUR_PAYPAL_CLIENT_SECRET'));
define('PP_ENV',           env('PAYPAL_ENV',           'sandbox')); // sandbox | live
define('PP_API_BASE',      PP_ENV === 'live'
    ? 'https://api-m.paypal.com'
    : 'https://api-m.sandbox.paypal.com');

// ══════════════════════════════════════════════════════
//  APP BASE URL  (used in payment return / callback URLs)
// ══════════════════════════════════════════════════════
define('APP_URL', rtrim(env('APP_URL', 'http://localhost/PHP/PHP_Project_2025-26'), '/'));
