<?php

return [
    'elasticsearch' => [
        'host' => env('ELASTICSEARCH_HOST', 'http://localhost:9200'),
        'index' => env('ELASTICSEARCH_INDEX', 'pages'),
    ],
];