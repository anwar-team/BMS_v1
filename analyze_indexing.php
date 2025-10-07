<?php
try {
    $pdo = new PDO('mysql:host=145.223.98.97;port=3306;dbname=bms', 'bms', 'bms2025');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check gap in IDs
    $result = $pdo->query("
        SELECT 
            MIN(id) as min_id,
            MAX(id) as max_id,
            COUNT(*) as total,
            MAX(id) - MIN(id) + 1 as id_range,
            (MAX(id) - MIN(id) + 1) - COUNT(*) as missing_ids
        FROM pages
    ")->fetch();
    
    echo "Page ID Analysis:\n";
    echo "==================\n";
    echo "Min ID: " . number_format($result['min_id']) . "\n";
    echo "Max ID: " . number_format($result['max_id']) . "\n";
    echo "Total Pages: " . number_format($result['total']) . "\n";
    echo "ID Range: " . number_format($result['id_range']) . "\n";
    echo "Missing IDs: " . number_format($result['missing_ids']) . "\n";
    
    // Check Elasticsearch count
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://145.223.98.97:9201/pages_new_search/_count");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $es_result = json_decode($response, true);
    $es_count = $es_result['count'] ?? 0;
    
    echo "\nElasticsearch Status:\n";
    echo "=====================\n";
    echo "Indexed Pages: " . number_format($es_count) . "\n";
    echo "Missing Pages: " . number_format($result['total'] - $es_count) . "\n";
    echo "Progress: " . round(($es_count / $result['total']) * 100, 2) . "%\n";
    
    if ($es_count >= $result['total']) {
        echo "\n✅ INDEXING COMPLETE! All pages are indexed.\n";
    } else {
        echo "\n⚠️  INDEXING INCOMPLETE. " . number_format($result['total'] - $es_count) . " pages remaining.\n";
    }
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
