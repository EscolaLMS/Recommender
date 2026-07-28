<?php

return [
    'api_url' => env('RECOMMENDER_API_URL', 'http://recommender:9000/recommender/'),
    'signature_secret' => env('RECOMMENDER_SIGNATURE_SECRET', ''),
    'frames_microservice_url' => env('RECOMMENDER_FRAMES_MICROSERVICE_URL', ''),
    'satisfaction_models' => [
        'prod_elasticnet' => true,
        'retrained_ridge3' => false,
        'retrained_rf13' => false,
    ],
];
