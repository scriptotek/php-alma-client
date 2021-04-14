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
        'key' => env('ALMA_IZ_KEY'),
        'sru' => env('ALMA_IZ_SRU_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Network zone settings
    |--------------------------------------------------------------------------
    */
    'nz' => [
        'key' => env('ALMA_NZ_KEY'),
        'sru' => env('ALMA_NZ_SRU_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    | This is only necessary to set if you connect to a non-standard API URL,
    | for instance though a proxy.
    */
    'baseUrl' => null,

    /*
    |--------------------------------------------------------------------------
    | Extra request headers
    |--------------------------------------------------------------------------
    | An associated array of extra headers to be sent with each request.
    */
    'extraHeaders' => [
        // 'x-proxy-auth' => 'custom proxy auth'
    ],

];
