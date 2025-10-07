<?php

/**
 * فحص mapping الفهرس
 */

require __DIR__.'/vendor/autoload.php';

use Elasticsearch\ClientBuilder;

$ES_HOST = 'http://145.223.98.97:9201';
$ES_INDEX = 'pages_new_search';

$es = ClientBuilder::create()->setHosts([$ES_HOST])->build();

echo "\n=== Checking Index Mapping ===\n\n";

try {
    $mapping = $es->indices()->getMapping(['index' => $ES_INDEX]);
    
    $contentMapping = $mapping[$ES_INDEX]['mappings']['properties']['content'] ?? null;
    
    if ($contentMapping) {
        echo "Content field mapping:\n";
        echo json_encode($contentMapping, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        echo "\n\n";
        
        // Check for multi-fields
        if (isset($contentMapping['fields'])) {
            echo "Multi-fields detected:\n";
            foreach ($contentMapping['fields'] as $fieldName => $fieldConfig) {
                echo "  - content.$fieldName:\n";
                echo "    Type: " . ($fieldConfig['type'] ?? 'N/A') . "\n";
                echo "    Analyzer: " . ($fieldConfig['analyzer'] ?? 'N/A') . "\n";
            }
        } else {
            echo "⚠️  No multi-fields found!\n";
            echo "   This is the problem - we need .exact, .flexible, .stemmed fields\n";
        }
        
    } else {
        echo "❌ Content field not found in mapping!\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n";
