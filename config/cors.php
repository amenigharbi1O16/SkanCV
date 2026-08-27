<?php

return [
    // Toutes les routes API sont concernées
    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    // En dev : autoriser localhost React (Vite = 5173, CRA = 3000)
    // En prod : remplacer par le vrai domaine du frontend, JAMAIS '*' avec credentials
    'allowed_origins' => [
        env('FRONTEND_URL', 'http://localhost:5173'),
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // true car le frontend enverra le JWT via header Authorization
    'supports_credentials' => true,
];