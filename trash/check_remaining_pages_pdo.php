<?php
require __DIR__.'/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_DATABASE']};charset=utf8mb4",
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "════════════════════════════════════════════════════════════\n";
    echo "         🔍 فحص الصفحات المتبقية\n";
    echo "════════════════════════════════════════════════════════════\n\n";

    // إجمالي الصفحات
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pages");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "📊 إجمالي الصفحات في MySQL: " . number_format($total) . "\n";

    // أعلى ID
    $stmt = $pdo->query("SELECT MAX(id) as max_id FROM pages");
    $max_id = $stmt->fetch(PDO::FETCH_ASSOC)['max_id'];
    echo "🔝 أعلى ID: " . number_format($max_id) . "\n";

    // الصفحات بعد 5855265
    $stmt = $pdo->query("SELECT COUNT(*) as remaining FROM pages WHERE id > 5855265");
    $remaining = $stmt->fetch(PDO::FETCH_ASSOC)['remaining'];
    echo "📄 الصفحات المتبقية (ID > 5855265): " . number_format($remaining) . "\n";

    if ($remaining > 0) {
        // أول صفحة بعد 5855265
        $stmt = $pdo->query("SELECT id, book_id, page_number FROM pages WHERE id > 5855265 ORDER BY id ASC LIMIT 1");
        $first = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "\n📌 أول صفحة متبقية:\n";
        echo "   ID: " . $first['id'] . "\n";
        echo "   Book ID: " . $first['book_id'] . "\n";
        echo "   Page Number: " . $first['page_number'] . "\n";
        
        // آخر صفحة
        $stmt = $pdo->query("SELECT id, book_id, page_number FROM pages WHERE id > 5855265 ORDER BY id DESC LIMIT 1");
        $last = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "\n📌 آخر صفحة متبقية:\n";
        echo "   ID: " . $last['id'] . "\n";
        echo "   Book ID: " . $last['book_id'] . "\n";
        echo "   Page Number: " . $last['page_number'] . "\n";
    } else {
        echo "\n✅ لا توجد صفحات متبقية! الفهرسة اكتملت!\n";
    }

    // فحص Elasticsearch
    echo "\n════════════════════════════════════════════════════════════\n";
    echo "         🔍 فحص Elasticsearch\n";
    echo "════════════════════════════════════════════════════════════\n\n";

    $ch = curl_init("http://145.223.98.97:9201/pages_new_search/_count");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $data = json_decode($response, true);
    $es_count = $data['count'] ?? 0;

    echo "📊 عدد الصفحات في Elasticsearch: " . number_format($es_count) . "\n";
    echo "📊 عدد الصفحات في MySQL: " . number_format($total) . "\n";
    echo "📊 الفرق: " . number_format($total - $es_count) . "\n";

    if ($es_count >= $total) {
        echo "\n✅✅✅ الفهرسة اكتملت بنجاح! ✅✅✅\n";
    } else {
        $percentage = ($es_count / $total) * 100;
        echo "\n⏸️ الفهرسة متوقفة عند: " . number_format($percentage, 2) . "%\n";
    }

} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
}
