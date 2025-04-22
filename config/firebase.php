<?php

return [
    'credentials' => [
        "type" => env('FIREBASE_TYPE'),
        "project_id" => env('FIREBASE_PROJECT_ID'),
        "private_key_id" => env('FIREBASE_PRIVATE_KEY_ID'),
        "private_key" => str_replace("\\n", "\n", env('FIREBASE_PRIVATE_KEY')),
        "client_email" => env('FIREBASE_CLIENT_EMAIL'),
        "client_id" => env('FIREBASE_CLIENT_ID'),
        "auth_uri" => env('FIREBASE_AUTH_URI'),
        "token_uri" => env('FIREBASE_TOKEN_URI'),
        "auth_provider_x509_cert_url" => env('FIREBASE_AUTH_PROVIDER_CERT_URL'),
        "client_x509_cert_url" => env('FIREBASE_CLIENT_CERT_URL'),
        "universe_domain" => env('FIREBASE_UNIVERSE_DOMAIN'),
        
    ],
    'database_url' => env('FIREBASE_DATABASE_URL'),
];
