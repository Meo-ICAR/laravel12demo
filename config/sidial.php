<?php

return [
    'base_url' => env('SIDIAL_BASE_URL', 'https://ancoraservizi.sidial.cloudx'),
    'api_token' => env('SIDIAL_API_TOKEN', ''),
    // Optional fallback when no rows exist yet; format DD/MM/YYYY per API requirement
    'last_activation' => env('SIDIAL_LAST_ACTIVATION', null),
];
