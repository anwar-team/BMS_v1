<?php
try {
    $pdo = new PDO('mysql:host=145.223.98.97;port=3306;dbname=bms', 'bms', 'bms2025');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $result = $pdo->query('SELECT MAX(id) as max_id, COUNT(*) as total FROM pages')->fetch();
    echo "Max page ID in DB: " . $result['max_id'] . "\n";
    echo "Total pages in DB: " . $result['total'] . "\n";
    
    // Check if ID 5855265 exists
    $check = $pdo->query("SELECT id FROM pages WHERE id > 5855265 LIMIT 10")->fetchAll();
    echo "\nFirst 10 IDs after 5855265:\n";
    foreach($check as $row) {
        echo "  - " . $row['id'] . "\n";
    }
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
