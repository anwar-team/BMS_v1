<?php
// ✅ أفضل طريقة للبحث عن الصفحات المفقودة
try {
    $pdo = new PDO('mysql:host=145.223.98.97;port=3306;dbname=bms', 'bms', 'bms2025');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all page IDs from MySQL in chunks
    echo "Loading page IDs from MySQL...\n";
    $mysql_ids = [];
    $stmt = $pdo->query("SELECT id FROM pages ORDER BY id");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $mysql_ids[] = $row[0];
    }
    echo "Total pages in MySQL: " . count($mysql_ids) . "\n\n";
    
    // Get all IDs from Elasticsearch
    echo "Loading page IDs from Elasticsearch...\n";
    $es_ids = [];
    $scroll_id = null;
    $ch = curl_init();
    
    // Initial search with scroll
    curl_setopt($ch, CURLOPT_URL, "http://145.223.98.97:9201/pages_new_search/_search?scroll=5m");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'size' => 10000,
        '_source' => ['id'],
        'query' => ['match_all' => (object)[]]
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = json_decode(curl_exec($ch), true);
    
    $scroll_id = $response['_scroll_id'];
    foreach ($response['hits']['hits'] as $hit) {
        $es_ids[] = $hit['_source']['id'];
    }
    
    // Keep scrolling until no more results
    $iterations = 0;
    while (count($response['hits']['hits']) > 0 && $iterations < 1000) {
        curl_setopt($ch, CURLOPT_URL, "http://145.223.98.97:9201/_search/scroll");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'scroll' => '5m',
            'scroll_id' => $scroll_id
        ]));
        
        $response = json_decode(curl_exec($ch), true);
        if (!isset($response['hits']['hits'])) break;
        
        foreach ($response['hits']['hits'] as $hit) {
            $es_ids[] = $hit['_source']['id'];
        }
        
        $iterations++;
        if ($iterations % 10 == 0) {
            echo "Loaded " . count($es_ids) . " IDs so far...\r";
        }
    }
    curl_close($ch);
    
    echo "\nTotal pages in Elasticsearch: " . count($es_ids) . "\n\n";
    
    // Find missing IDs
    echo "Finding missing pages...\n";
    $es_ids_set = array_flip($es_ids);
    $missing = [];
    
    foreach ($mysql_ids as $id) {
        if (!isset($es_ids_set[$id])) {
            $missing[] = $id;
            if (count($missing) <= 100) {
                echo "  Missing ID: $id\n";
            }
        }
    }
    
    echo "\n";
    echo "═══════════════════════════════════════\n";
    echo "SUMMARY:\n";
    echo "═══════════════════════════════════════\n";
    echo "Total Missing: " . count($missing) . "\n";
    
    if (count($missing) > 0) {
        echo "\nFirst 20 missing IDs:\n";
        foreach (array_slice($missing, 0, 20) as $id) {
            echo "  - $id\n";
        }
        
        echo "\nLast 20 missing IDs:\n";
        foreach (array_slice($missing, -20) as $id) {
            echo "  - $id\n";
        }
        
        // Save to file for manual indexing
        file_put_contents('missing_page_ids.txt', implode("\n", $missing));
        echo "\n✅ Missing IDs saved to missing_page_ids.txt\n";
    }
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
