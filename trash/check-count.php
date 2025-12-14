<?php
require 'vendor/autoload.php';

$client = Elasticsearch\ClientBuilder::create()
    ->setHosts(['http://145.223.98.97:9201'])
    ->build();

$result = $client->count(['index' => 'pages_new_search']);
echo "عدد المستندات في pages_new_search: " . number_format($result['count']) . "\n";

// اختبار بحث بسيط
$search = $client->search([
    'index' => 'pages_new_search',
    'body' => [
        'query' => [
            'match' => [
                'content.flexible' => 'الصلاة'
            ]
        ],
        'size' => 1
    ]
]);

$total = $search['hits']['total']['value'] ?? 0;
echo "نتائج البحث عن 'الصلاة': " . number_format($total) . "\n";
