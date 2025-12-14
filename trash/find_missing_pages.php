<?php
require __DIR__.'/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "════════════════════════════════════════════════════════════\n";
echo "         🔍 البحث عن الصفحات الناقصة\n";
echo "════════════════════════════════════════════════════════════\n\n";

// فحص Elasticsearch
$ch = curl_init("http://145.223.98.97:9201/pages_new_search/_search");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    "size" => 0,
    "aggs" => [
        "min_id" => ["min" => ["field" => "id"]],
        "max_id" => ["max" => ["field" => "id"]]
    ]
]));
$response = curl_exec($ch);
$data = json_decode($response, true);

$es_min_id = $data['aggregations']['min_id']['value'] ?? 0;
$es_max_id = $data['aggregations']['max_id']['value'] ?? 0;

echo "📊 Elasticsearch:\n";
echo "   أصغر ID: " . number_format($es_min_id) . "\n";
echo "   أكبر ID: " . number_format($es_max_id) . "\n";
echo "   عدد الصفحات: " . number_format($data['hits']['total']['value']) . "\n";

// فحص MySQL
try {
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_DATABASE']};charset=utf8mb4",
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "\n📊 MySQL:\n";
    
    $stmt = $pdo->query("SELECT MIN(id) as min_id, MAX(id) as max_id, COUNT(*) as total FROM pages");
    $mysql_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "   أصغر ID: " . number_format($mysql_data['min_id']) . "\n";
    echo "   أكبر ID: " . number_format($mysql_data['max_id']) . "\n";
    echo "   عدد الصفحات: " . number_format($mysql_data['total']) . "\n";
    
    // فحص عينة من IDs الناقصة
    echo "\n════════════════════════════════════════════════════════════\n";
    echo "         🔍 عينة من الصفحات الناقصة\n";
    echo "════════════════════════════════════════════════════════════\n\n";
    
    // نفحص 10 IDs عشوائية من MySQL
    $stmt = $pdo->query("
        SELECT id, book_id, page_number 
        FROM pages 
        ORDER BY RAND() 
        LIMIT 10
    ");
    
    $missing_count = 0;
    while ($page = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // نشيك إذا موجود في Elasticsearch
        $ch = curl_init("http://145.223.98.97:9201/pages_new_search/_search");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            "query" => [
                "term" => ["id" => $page['id']]
            ]
        ]));
        $response = curl_exec($ch);
        $es_data = json_decode($response, true);
        
        $found = $es_data['hits']['total']['value'] > 0;
        $status = $found ? "✅ موجود" : "❌ ناقص";
        
        if (!$found) {
            $missing_count++;
            echo "ID: {$page['id']} - $status\n";
        }
    }
    
    echo "\n📊 من 10 صفحات عشوائية: $missing_count ناقصة\n";
    
    if ($missing_count > 0) {
        echo "\n⚠️ المشكلة: Logstash يعتمد على ID بس، ومش بيشوف الفجوات!\n";
        echo "🔧 الحل: لازم نعيد الفهرسة من ID = 1 أو نستخدم طريقة pagination مختلفة\n";
    }

} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
}
