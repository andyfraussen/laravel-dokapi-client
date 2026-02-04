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
    'timeout' => env('DOKAPI_TIMEOUT', 30),
    'connect_timeout' => env('DOKAPI_CONNECT_TIMEOUT', 10),
    'verify' => env('DOKAPI_VERIFY', true),
    'user_agent' => env('DOKAPI_USER_AGENT', 'andyfraussen/laravel-dokapi-client'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Options
    |--------------------------------------------------------------------------
    |
    | Extra Guzzle options or headers to merge into each request.
    |
    */
    'http' => [],
];
