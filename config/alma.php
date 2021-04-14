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

        // Entry point URL. This only needs to be specified if you use a proxy
        // or other non-standard entry point.
        'entrypoint' => null,

        // Optional list of extra headers to send with each request.
        'headers' => [
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

        // Entry point URL. This only needs to be specified if you use a proxy
        // or other non-standard entry point.
        'entrypoint' => null,

        // Optional list of extra headers to send with each request.
        'headers' => [
            // 'x-proxy-auth' => 'custom proxy auth'
        ],
    ],
];
