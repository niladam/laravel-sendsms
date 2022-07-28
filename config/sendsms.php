<?php

/**
 * This is the package main config file.
 */
return [
    /**
     * This is your main username.
     */
    'username' => env('LARAVEL_SENDSMS_USERNAME', null),

    /**
     * This is your main password.
     */
    'password' => env('LARAVEL_SENDSMS_PASSWORD', null),

    /**
     * This is the base URL that the package will use.
     *
     * It has already been filled with a default value.
     */
    'url' => env('LARAVEL_SENDSMS_URL', 'https://api.sendsms.ro/json'),

    /**
     * If this package should have debug turned on
     * please set this here.
     */
    'debug' => env('LARAVEL_SENDSMS_DEBUG', false),

    'messages' => [
        'from' => env('LARAVEL_SENDSMS_FROM', null),
        'callback_url' => env('LARAVEL_SENDSMS_CALLBACK', null),
        'charset' => env('LARAVEL_SENDSMS_CHARSET', null),
        'coding' => env('LARAVEL_SENDSMS_CODING', null),
        'class' => env('LARAVEL_SENDSMS_CLASS', -1),
        'auto_detect_encoding' => env(
            'LARAVEL_SENDSMS_AUTODETECT_ENCODING',
            null
        ),
        /**
         * Information on the report mask:
         *
         *  1   Delivered
         *  2   Undelivered
         *  4   Queued at network
         *  8   Sent to network
         *  16  Failed at network
         *
         * So, 19 means:
         *
         * (Delivered + Undelivered + Failed at network)
         * 1 + 2 + 16 = 19
         */
        'report_mask' => env('LARAVEL_SENDSMS_MASK', 19),
    ],

    /**
     * This is basically a mapping of the operations
     * that the API will use.
     */
    'operations' => [
        'balance' => 'user_get_balance',
        'ping' => 'ping',
        'price' => 'route_check_price',
        'info' => 'user_get_info',
        'number' => 'user_get_phone_number',
        'send' => 'message_send',
    ],
];
