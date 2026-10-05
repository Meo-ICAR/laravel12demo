<?php

return [
    'api' => [
        'url' => env('PRATICHE_API_URL', 'https://api.your-pratiche-service.com/v1'),
        'key' => env('PRATICHE_API_KEY'),
        'timeout' => 60, // seconds
    ],
    'import' => [
        'default_days' => 7, // Default days to look back when no date range is provided
    ],
];
