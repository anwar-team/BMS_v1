<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Search Engine
    |--------------------------------------------------------------------------
    |
    | This option controls the default search connection that gets used while
    | using Laravel Scout. This connection is used when syncing all models
    | to the search service.
    |
    */

    'driver' => env('SCOUT_DRIVER', 'elastic'),

    /*
    |--------------------------------------------------------------------------
    | Index Prefix
    |--------------------------------------------------------------------------
    |
    | Here you may specify a prefix that will be applied to all search index
    | names used by Scout. This prefix may be useful if you have multiple
    | "tenants" or applications sharing the same search infrastructure.
    |
    */

    'prefix' => env('SCOUT_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Queue Data Syncing
    |--------------------------------------------------------------------------
    |
    | This option allows you to control if the operations that sync your data
    | with your search engines are queued. When this is set to "false" then
    | data syncing will be immediate for better search responsiveness.
    |
    */

    'queue' => env('SCOUT_QUEUE', false),

    /*
    |--------------------------------------------------------------------------
    | Elasticsearch Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Elasticsearch settings.
    |
    */

    'elastic' => [
        'client' => [
            'hosts' => [
                env('ELASTICSEARCH_HOST', 'http://145.223.98.97:9201'),
            ],
            'retries' => 1,
            'timeout' => 30,
            'connection_params' => [
                'client' => [
                    'timeout' => 30,
                    'connect_timeout' => 10,
                ],
            ],
        ],
        'update_mapping' => env('SCOUT_ELASTIC_UPDATE_MAPPING', true),
        'indexer' => env('SCOUT_ELASTIC_INDEXER', 'single'),
        'document_refresh' => env('SCOUT_ELASTIC_DOCUMENT_REFRESH', 'wait_for'),
    ],

    'elasticsearch' => [
        'index' => env('ELASTICSEARCH_INDEX', 'pages'),
        'hosts' => [
            env('ELASTICSEARCH_HOST', 'http://145.223.98.97:9201'),
        ],
    ],
];