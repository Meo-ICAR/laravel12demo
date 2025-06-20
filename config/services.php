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

        'microsoft' => [
            'client_id' => env('MICROSOFT_CLIENT_ID'),
            'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
            'redirect' => env('MICROSOFT_REDIRECT_URI'),
        ],
    ],

];
