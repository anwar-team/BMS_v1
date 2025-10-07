#!/usr/bin/env php
<?php

/**
 * ====================================================================
 * 🔍 فحص شامل لحالة Elasticsearch
 * ====================================================================
 * 
 * يتبع أفضل الممارسات من Context7:
 * - Cluster Health Monitoring
 * - Index Stats Analysis
 * - Error Detection
 * 
 * ====================================================================
 */

$ES_HOST = 'http://145.223.98.97:9201';
$ES_INDEX = 'pages_new_search';

echo "\n";
echo "════════════════════════════════════════════════════════════\n";
echo "         🔍 فحص شامل لـ Elasticsearch\n";
echo "════════════════════════════════════════════════════════════\n\n";

// ===================================================================
// 1. Cluster Health (حسب Context7 Best Practices)
// ===================================================================

echo "1️⃣ فحص صحة الـ Cluster:\n";
echo "─────────────────────────────────────\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$ES_HOST/_cluster/health");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($ch);
curl_close($ch);

$health = json_decode($response, true);

if ($health) {
    $status_emoji = [
        'green' => '🟢',
        'yellow' => '🟡',
        'red' => '🔴'
    ];
    
    echo "   Status: " . ($status_emoji[$health['status']] ?? '⚪') . " {$health['status']}\n";
    echo "   Cluster: {$health['cluster_name']}\n";
    echo "   Nodes: {$health['number_of_nodes']}\n";
    echo "   Active Shards: {$health['active_shards']}\n";
    echo "   Unassigned Shards: {$health['unassigned_shards']}\n";
    
    if ($health['status'] !== 'green') {
        echo "   ⚠️  WARNING: Cluster not in optimal state!\n";
    }
} else {
    echo "   ❌ خطأ في الاتصال بـ Elasticsearch\n";
    exit(1);
}

echo "\n";

// ===================================================================
// 2. Index Stats
// ===================================================================

echo "2️⃣ إحصائيات الفهرس ($ES_INDEX):\n";
echo "─────────────────────────────────────\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$ES_HOST/$ES_INDEX/_stats");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($ch);
curl_close($ch);

$stats = json_decode($response, true);

if ($stats && isset($stats['indices'][$ES_INDEX])) {
    $index_stats = $stats['indices'][$ES_INDEX]['total'];
    
    echo "   Documents: " . number_format($index_stats['docs']['count']) . "\n";
    echo "   Deleted Docs: " . number_format($index_stats['docs']['deleted']) . "\n";
    echo "   Store Size: " . formatBytes($index_stats['store']['size_in_bytes']) . "\n";
    echo "   Segments: " . number_format($index_stats['segments']['count']) . "\n";
    
    // Check for issues
    if ($index_stats['docs']['deleted'] > 0) {
        $deleted_percent = ($index_stats['docs']['deleted'] / $index_stats['docs']['count']) * 100;
        echo "   ⚠️  Deleted docs: " . number_format($deleted_percent, 2) . "%\n";
        
        if ($deleted_percent > 10) {
            echo "   💡 Consider running: POST /$ES_INDEX/_forcemerge?max_num_segments=1\n";
        }
    }
} else {
    echo "   ❌ خطأ في جلب إحصائيات الفهرس\n";
}

echo "\n";

// ===================================================================
// 3. Index Settings Check
// ===================================================================

echo "3️⃣ إعدادات الفهرس:\n";
echo "─────────────────────────────────────\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$ES_HOST/$ES_INDEX/_settings");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($ch);
curl_close($ch);

$settings = json_decode($response, true);

if ($settings && isset($settings[$ES_INDEX])) {
    $index_settings = $settings[$ES_INDEX]['settings']['index'];
    
    echo "   Number of Shards: " . ($index_settings['number_of_shards'] ?? 'N/A') . "\n";
    echo "   Number of Replicas: " . ($index_settings['number_of_replicas'] ?? 'N/A') . "\n";
    echo "   Refresh Interval: " . ($index_settings['refresh_interval'] ?? 'default (1s)') . "\n";
    
    // Check for common issues
    $shards = $index_settings['number_of_shards'] ?? 1;
    if ($shards > 5) {
        echo "   ⚠️  Too many shards ($shards) for a single index\n";
    }
}

echo "\n";

// ===================================================================
// 4. Mapping Check
// ===================================================================

echo "4️⃣ فحص Mapping:\n";
echo "─────────────────────────────────────\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$ES_HOST/$ES_INDEX/_mapping");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($ch);
curl_close($ch);

$mapping = json_decode($response, true);

if ($mapping && isset($mapping[$ES_INDEX]['mappings']['properties'])) {
    $properties = $mapping[$ES_INDEX]['mappings']['properties'];
    
    // Check for content field with multi-fields
    if (isset($properties['content'])) {
        $content_field = $properties['content'];
        
        if (isset($content_field['fields'])) {
            echo "   ✅ Content field has multi-fields:\n";
            foreach ($content_field['fields'] as $field_name => $field_config) {
                $analyzer = $field_config['analyzer'] ?? 'standard';
                echo "      - content.$field_name (analyzer: $analyzer)\n";
            }
        } else {
            echo "   ⚠️  Content field missing multi-fields (exact, flexible, stemmed)\n";
        }
    } else {
        echo "   ❌ Content field not found in mapping\n";
    }
    
    echo "   Total Fields: " . count($properties) . "\n";
}

echo "\n";

// ===================================================================
// 5. Sample Query Test
// ===================================================================

echo "5️⃣ اختبار استعلام تجريبي:\n";
echo "─────────────────────────────────────\n";

$test_query = [
    'query' => [
        'match' => [
            'content' => 'الله'
        ]
    ],
    'size' => 1
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$ES_HOST/$ES_INDEX/_search");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_query));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200) {
    $result = json_decode($response, true);
    $took = $result['took'] ?? 0;
    $total = $result['hits']['total']['value'] ?? 0;
    
    echo "   ✅ Query successful\n";
    echo "   Time: {$took}ms\n";
    echo "   Results: " . number_format($total) . "\n";
    
    if ($took > 1000) {
        echo "   ⚠️  Slow query (>1s), consider optimization\n";
    }
} else {
    echo "   ❌ Query failed (HTTP $http_code)\n";
}

echo "\n";

// ===================================================================
// 6. Compare with MySQL
// ===================================================================

echo "6️⃣ مقارنة مع MySQL:\n";
echo "─────────────────────────────────────\n";

try {
    $pdo = new PDO('mysql:host=145.223.98.97;port=3306;dbname=bms', 'bms', 'bms2025');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $result = $pdo->query('SELECT COUNT(*) as total FROM pages')->fetch();
    $mysql_count = $result['total'];
    
    echo "   MySQL Pages: " . number_format($mysql_count) . "\n";
    echo "   ES Pages: " . number_format($index_stats['docs']['count']) . "\n";
    
    $diff = $mysql_count - $index_stats['docs']['count'];
    echo "   Difference: " . number_format(abs($diff)) . "\n";
    
    if ($diff > 0) {
        echo "   ⚠️  Missing " . number_format($diff) . " pages in Elasticsearch\n";
        echo "   💡 Run: php fix_missing_pages.php\n";
    } elseif ($diff < 0) {
        echo "   ⚠️  Extra " . number_format(abs($diff)) . " pages in Elasticsearch\n";
    } else {
        echo "   ✅ Counts match perfectly!\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ خطأ في الاتصال بـ MySQL: {$e->getMessage()}\n";
}

echo "\n";
echo "════════════════════════════════════════════════════════════\n";
echo "                  انتهى الفحص\n";
echo "════════════════════════════════════════════════════════════\n\n";

// ===================================================================
// Helper Functions
// ===================================================================

function formatBytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
    return number_format($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
}
