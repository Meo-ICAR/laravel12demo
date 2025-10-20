<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Socialite Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default socialite provider that is used when an
    | input is given to the socialite authentication.
    |
    | Supported: "github", "gitlab", "bitbucket"
    |
    */

    'default' => env('SOCIALITE_DEFAULT_PROVIDER', 'github'),

    /*
    |--------------------------------------------------------------------------
    | Socialite Providers
    |--------------------------------------------------------------------------
    |
    | This option controls the socialite providers that are available for
    | authentication.
    |
    | Supported: "github", "gitlab", "bitbucket"
    |
    */
    'microsoft' => [
        'client_id' => env('MICROSOFT_CLIENT_ID'),
        'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
        'redirect' => env('MICROSOFT_REDIRECT_URI'),
        'tenant' => env('MICROSOFT_TENANT_ID', 'common'),
        'scopes' => [
            'openid',
            'email',
            'profile',
            'offline_access',
        ],
        'with' => [
            'response_type' => 'code',
            'prompt' => 'select_account',
            'response_mode' => 'query',
        ],
    ],

    'providers' => [
        'github' => [
            'client_id' => env('GITHUB_CLIENT_ID'),
            'client_secret' => env('GITHUB_CLIENT_SECRET'),
            'redirect' => env('GITHUB_REDIRECT_URI'),
        ],

        'gitlab' => [
            'client_id' => env('GITLAB_CLIENT_ID'),
            'client_secret' => env('GITLAB_CLIENT_SECRET'),
            'redirect' => env('GITLAB_REDIRECT_URI'),
        ],

        'bitbucket' => [
            'client_id' => env('BITBUCKET_CLIENT_ID'),
            'client_secret' => env('BITBUCKET_CLIENT_SECRET'),
            'redirect' => env('BITBUCKET_REDIRECT_URI'),
        ],

        'azure' => [
            'client_id' => env('AZURE_CLIENT_ID'),
            'client_secret' => env('AZURE_CLIENT_SECRET'),
            'redirect' => env('AZURE_REDIRECT_URI'),
            'tenant' => env('AZURE_TENANT_ID', 'common'),
            'scopes' => [
                'openid',
                'email',
                'profile',
                'offline_access',
            ],
            'with' => [
                'response_type' => 'code',
                'prompt' => 'select_account',
                'response_mode' => 'query',
            ],
        ],
    ],

];
