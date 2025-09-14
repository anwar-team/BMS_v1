<?php

require_once __DIR__ . '/vendor/autoload.php';

use Elasticsearch\ClientBuilder;

try {
    $client = ClientBuilder::create()
        ->setHosts(['http://145.223.98.97:9201'])
        ->build();
    
    $info = $client->info();
    echo "Elasticsearch connection successful!\n";
    echo "Version: " . $info['version']['number'] . "\n";
    echo "Cluster: " . $info['cluster_name'] . "\n";
} catch (Exception $e) {
    echo "Elasticsearch connection failed: " . $e->getMessage() . "\n";
}