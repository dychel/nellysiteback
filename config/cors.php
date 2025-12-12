<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your settings for cross-origin resource sharing (CORS).
    | This determines what cross-origin operations may execute in web browsers.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000',           // Dev local
        'https://nos-provisions.netlify.app', // Prod
    ],

    // Permet les sous-domaines Netlify (preview, staging, etc.)
    'allowed_origins_patterns' => [
        '/https:\/\/.*\.netlify\.app/',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true, // Obligatoire pour Sanctum avec cookies

];
