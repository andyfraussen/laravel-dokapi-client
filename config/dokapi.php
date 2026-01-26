<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Dokapi API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure credentials and endpoints for Dokapi's Peppol API.
    |
    */

    'base_url' => env('DOKAPI_BASE_URL', 'https://peppol-api.dokapi-stg.io/v1'),
    'token_url' => env('DOKAPI_TOKEN_URL', 'https://dev-portal.dokapi.io/api/oauth2/token'),
    'client_id' => env('DOKAPI_CLIENT_ID'),
    'client_secret' => env('DOKAPI_CLIENT_SECRET'),
    'access_token' => env('DOKAPI_ACCESS_TOKEN'),
    'cache_token' => env('DOKAPI_CACHE_TOKEN', true),
    'timeout' => 30,
    'connect_timeout' => 10,
    'verify' => true,
];
