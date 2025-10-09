<?php

/**
 * التحقق من Index Template
 */

require __DIR__ . '/vendor/autoload.php';

use Elasticsearch\ClientBuilder;

$client = ClientBuilder::create()
    ->setHosts(['http://145.223.98.97:9201'])
    ->build();

echo "════════════════════════════════════════════════════════\n";
echo "   التحقق من Index Template\n";
echo "════════════════════════════════════════════════════════\n\n";

try {
    $response = $client->indices()->getIndexTemplate(['name' => 'pages_new_search_template']);
    
    if (isset($response['index_templates'][0])) {
        $tmpl = $response['index_templates'][0]['index_template'];
        
        echo "✅ Template Name: pages_new_search_template\n";
        echo "✅ Index Patterns: " . json_encode($tmpl['index_patterns']) . "\n";
        echo "✅ Priority: " . ($tmpl['priority'] ?? 0) . "\n\n";
        
        // Settings
        if (isset($tmpl['template']['settings'])) {
            echo "📊 Settings:\n";
            $settings = $tmpl['template']['settings'];
            
            if (isset($settings['index'])) {
                echo "  • Shards: " . ($settings['index']['number_of_shards'] ?? 'N/A') . "\n";
                echo "  • Replicas: " . ($settings['index']['number_of_replicas'] ?? 'N/A') . "\n";
                echo "  • Refresh Interval: " . ($settings['index']['refresh_interval'] ?? 'N/A') . "\n";
                echo "  • Max Result Window: " . ($settings['index']['max_result_window'] ?? 'N/A') . "\n";
                
                if (isset($settings['index']['analysis']['analyzer'])) {
                    $analyzers = array_keys($settings['index']['analysis']['analyzer']);
                    echo "  • Analyzers: " . json_encode($analyzers) . "\n";
                }
            }
            echo "\n";
        }
        
        // Mappings
        if (isset($tmpl['template']['mappings']['properties'])) {
            echo "🗺️  Mappings:\n";
            $props = $tmpl['template']['mappings']['properties'];
            
            echo "  • Total Fields: " . count($props) . "\n";
            
            if (isset($props['content'])) {
                echo "  • Content Type: " . $props['content']['type'] . "\n";
                echo "  • Content Analyzer: " . ($props['content']['analyzer'] ?? 'N/A') . "\n";
                
                if (isset($props['content']['fields'])) {
                    $fields = array_keys($props['content']['fields']);
                    echo "  • Content Sub-fields: " . json_encode($fields) . "\n";
                }
            }
            echo "\n";
        }
        
        echo "════════════════════════════════════════════════════════\n";
        echo "   ✅ Template جاهز!\n";
        echo "════════════════════════════════════════════════════════\n";
        
    } else {
        echo "❌ Template غير موجود!\n";
    }
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
}
