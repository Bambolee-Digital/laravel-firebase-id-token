<?php

return [
    'credentials_base64' => env('FIREBASE_CREDENTIALS_BASE64'),

    'auth_order' => env('AUTH_ORDER', 'firebase,sanctum'),

    'user_model' => env('FIREBASE_USER_MODEL', config('auth.providers.users.model')),

    'custom_claims' => [
        // 'role' => 'user_role',
    ],

    'auto_create_user' => true,

    'default_user_data' => [
        'name' => 'Dog Dot App User',
    ],

    'sanctum' => [
        'expiration' => null,
        'token_name' => 'firebase-auth-token',
    ],
];