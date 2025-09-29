<?php

return [
    // Base endpoint for SIDIAL API, e.g. https://ancoraservizi.sidial.cloud/api.php
    'base_url' => env('SIDIAL_BASE_URL'),

    // API token provided by SIDIAL
    'api_token' => env('SIDIAL_API_TOKEN'),

    // Optional: fallback start date (format dd/mm/YYYY) used by some imports when no previous data exists
    'last_activation' => env('SIDIAL_LAST_ACTIVATION', '01/01/2024'),
];
