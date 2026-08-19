<?php

return [
    'base_url' => env('GENIUSPAY_BASE_URL', 'https://geniuspay.ci/api/v1/merchant'),
    'api_key' => trim((string) env('GENIUSPAY_API_KEY', '')),
    'api_secret' => trim((string) env('GENIUSPAY_API_SECRET', '')),
    'webhook_secret' => trim((string) env('GENIUSPAY_WEBHOOK_SECRET', '')),
];
