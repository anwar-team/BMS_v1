<?php
require __DIR__.'/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "════════════════════════════════════════════════════════════\n";
echo "         🔍 تحليل سرعة الفهرسة بالتفصيل\n";
echo "════════════════════════════════════════════════════════════\n\n";

// 1. فحص MySQL - أصغر ID
try {
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_DATABASE']};charset=utf8mb4",
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $stmt = $pdo->query("SELECT MIN(id) as min_id FROM pages");
    $min_mysql_id = $stmt->fetch(PDO::FETCH_ASSOC)['min_id'];
    
    echo "📊 MySQL:\n";
    echo "   أصغر ID في MySQL: " . number_format($min_mysql_id) . "\n\n";

    // 2. فحص Elasticsearch - أصغر ID
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
    $es_count = $data['hits']['total']['value'] ?? 0;
    
    echo "📊 Elasticsearch:\n";
    echo "   أصغر ID مفهرس: " . number_format($es_min_id) . "\n";
    echo "   أكبر ID مفهرس: " . number_format($es_max_id) . "\n";
    echo "   عدد الصفحات: " . number_format($es_count) . "\n\n";
    
    // 3. هل بدأ من أصغر ID؟
    if ($es_min_id == $min_mysql_id) {
        echo "✅ بدأ من أول ID في MySQL!\n\n";
    } else {
        echo "⚠️ لم يبدأ من أول ID!\n";
        echo "   المفروض: " . number_format($min_mysql_id) . "\n";
        echo "   الفعلي: " . number_format($es_min_id) . "\n";
        echo "   الفرق: " . number_format($es_min_id - $min_mysql_id) . " صفحة ناقصة من البداية!\n\n";
    }
    
    // 4. تحليل السرعة
    echo "════════════════════════════════════════════════════════════\n";
    echo "         ⚡ تحليل السرعة\n";
    echo "════════════════════════════════════════════════════════════\n\n";
    
    // قياس سرعة MySQL
    $start = microtime(true);
    $stmt = $pdo->query("
        SELECT 
            p.id,
            p.page_number,
            p.content,
            p.book_id,
            b.title as book_title,
            b.book_section_id,
            a.full_name as book_author,
            bs.name as book_section,
            CHAR_LENGTH(p.content) as content_length
        FROM pages p 
        LEFT JOIN books b ON p.book_id = b.id 
        LEFT JOIN author_book ab ON b.id = ab.book_id AND ab.is_main = 1
        LEFT JOIN authors a ON ab.author_id = a.id
        LEFT JOIN book_sections bs ON b.book_section_id = bs.id
        WHERE p.id > 500000
        ORDER BY p.id ASC 
        LIMIT 10000
    ");
    $mysql_time = microtime(true) - $start;
    $rows = $stmt->fetchAll();
    $count = count($rows);
    
    echo "📊 اختبار سرعة MySQL (10,000 صفحة):\n";
    echo "   الوقت: " . number_format($mysql_time, 3) . " ثانية\n";
    echo "   السرعة: " . number_format($count / $mysql_time, 0) . " صفحة/ثانية\n";
    echo "   حجم البيانات: " . number_format(strlen(json_encode($rows)) / 1024 / 1024, 2) . " MB\n\n";
    
    // 5. مشاكل محتملة
    echo "════════════════════════════════════════════════════════════\n";
    echo "         🔍 تشخيص المشاكل المحتملة\n";
    echo "════════════════════════════════════════════════════════════\n\n";
    
    $expected_speed = 10000; // صفحة/ثانية
    $actual_speed = 743; // من آخر قياس
    
    echo "📊 السرعة المتوقعة: " . number_format($expected_speed) . " صفحة/ثانية\n";
    echo "📊 السرعة الفعلية: " . number_format($actual_speed) . " صفحة/ثانية\n";
    echo "📊 الفرق: " . number_format(($expected_speed - $actual_speed) / $expected_speed * 100, 1) . "% أبطأ\n\n";
    
    echo "🔍 الأسباب المحتملة:\n\n";
    
    // السبب 1: Logstash Schedule
    echo "1️⃣ Logstash Schedule:\n";
    echo "   - الحالي: كل 10 ثوان (*/10 * * * * *)\n";
    echo "   - يعني: 6 مرات في الدقيقة\n";
    echo "   - كل مرة: 10,000 صفحة\n";
    echo "   - النتيجة: 60,000 صفحة/دقيقة = 1,000 صفحة/ثانية ✅\n\n";
    
    // السبب 2: Elasticsearch Indexing
    echo "2️⃣ Elasticsearch Bulk Indexing:\n";
    echo "   - كل 10,000 صفحة تاخد وقت للمعالجة\n";
    echo "   - التحليل العربي يأخذ وقت إضافي\n";
    echo "   - 3 analyzers لكل صفحة (normal, exact, stemmed)\n\n";
    
    // السبب 3: Network
    echo "3️⃣ Network:\n";
    echo "   - نقل البيانات من MySQL إلى Logstash\n";
    echo "   - نقل البيانات من Logstash إلى Elasticsearch\n";
    echo "   - حجم البيانات كبير (محتوى عربي)\n\n";
    
    // الحل
    echo "════════════════════════════════════════════════════════════\n";
    echo "         💡 الحلول المقترحة\n";
    echo "════════════════════════════════════════════════════════════\n\n";
    
    echo "✅ لزيادة السرعة إلى 10,000 صفحة/ثانية:\n\n";
    echo "1. تقليل Schedule من 10 ثوان إلى 5 ثوان (*/5)\n";
    echo "2. زيادة batch_size من 10,000 إلى 25,000\n";
    echo "3. زيادة pipeline.workers في Logstash\n";
    echo "4. زيادة pipeline.batch.size\n\n";
    
    echo "⚠️ لكن:\n";
    echo "   - السرعة الحالية (743 ص/ث) جيدة ومستقرة\n";
    echo "   - ستكتمل في ~2 ساعة\n";
    echo "   - أفضل من التعديل والمخاطرة بأخطاء جديدة\n\n";

} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
}
