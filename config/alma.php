<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Region code
    |--------------------------------------------------------------------------
    */
    'region' => env('ALMA_REGION', 'eu'),

    /*
    |--------------------------------------------------------------------------
    | Institution zone settings
    |--------------------------------------------------------------------------
    */
    'iz' => [
        // API key for institution zone
        'key' => env('ALMA_IZ_KEY'),

        // SRU URL for institution zone
        'sru' => env('ALMA_IZ_SRU_URL'),

        // Base URL for institution zone. This only needs to be specified if you
        // use a proxy or other non-standard URL.
        'baseUrl' => null,

        // Optional list of extra headers to send with each request.
        'extraHeaders' => [
            // 'x-proxy-auth' => 'custom proxy auth'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Network zone settings
    |--------------------------------------------------------------------------
    */
    'nz' => [
        // API key for institution zone
        'key' => env('ALMA_NZ_KEY'),

        // SRU URL for institution zone
        'sru' => env('ALMA_NZ_SRU_URL'),

        // Base URL for institution zone. This only needs to be specified if you
        // use a proxy or other non-standard URL.
        'baseUrl' => null,

        // Optional list of extra headers to send with each request.
        'extraHeaders' => [
            // 'x-proxy-auth' => 'custom proxy auth'
        ],
    ],
];
