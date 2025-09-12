<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Checkout & Bank settings (utilisés par le CheckoutController)
    |--------------------------------------------------------------------------
    */
    'bank' => [
        'holder'       => env('BANK_HOLDER', 'César Loïc (Jarvistech)'),
        'iban'         => env('BANK_IBAN', 'BE00 0000 0000'),
        'bic'          => env('BANK_BIC', 'XXXXXX'),
        'payment_days' => env('BANK_PAYMENT_DAYS', 7),
    ],

    'checkout' => [
        'free_shipping_threshold' => env('CHECKOUT_FREE_SHIPPING_THRESHOLD', 1500.00),
        'flat_shipping'           => env('CHECKOUT_FLAT_SHIPPING', 14.90),
        'vat_be'                  => env('CHECKOUT_VAT_BE', 0.21),
    ],

];
