<?php
/**
 * اختبار سرعة MySQL Query بعد التحسين
 */

$host = '145.223.98.97';
$user = 'bms';
$pass = 'bms2025';
$db = 'bms';

echo "════════════════════════════════════════════════════════════\n";
echo "        اختبار سرعة Logstash Query بعد التحسين            \n";
echo "════════════════════════════════════════════════════════════\n\n";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    
    // اختبار 3 مرات للحصول على متوسط
    $times = [];
    
    for ($i = 1; $i <= 3; $i++) {
        echo "🧪 اختبار #$i...\n";
        
        $testSql = "
            SELECT 
              p.id,
              p.page_number,
              CHAR_LENGTH(p.content) as content_length,
              p.book_id,
              b.title as book_title,
              b.book_section_id,
              a.full_name as book_author,
              bs.name as book_section
            FROM pages p 
            LEFT JOIN books b ON p.book_id = b.id 
            LEFT JOIN author_book ab ON b.id = ab.book_id AND ab.is_main = 1
            LEFT JOIN authors a ON ab.author_id = a.id
            LEFT JOIN book_sections bs ON b.book_section_id = bs.id
            WHERE p.id > 1000000 
            ORDER BY p.id ASC 
            LIMIT 10000
        ";
        
        $startTime = microtime(true);
        $stmt = $pdo->query($testSql);
        $count = 0;
        while ($row = $stmt->fetch()) {
            $count++;
        }
        $endTime = microtime(true);
        
        $duration = $endTime - $startTime;
        $times[] = $duration;
        
        echo "   ⏱️ الوقت: " . number_format($duration, 3) . " ثانية\n";
        echo "   📄 عدد النتائج: $count\n\n";
        
        sleep(1); // انتظار ثانية بين الاختبارات
    }
    
    $avgTime = array_sum($times) / count($times);
    $minTime = min($times);
    $maxTime = max($times);
    
    echo "════════════════════════════════════════════════════════════\n";
    echo "                      النتائج النهائية                    \n";
    echo "════════════════════════════════════════════════════════════\n\n";
    
    echo "⏱️ متوسط الوقت: " . number_format($avgTime, 3) . " ثانية\n";
    echo "⬇️ أسرع وقت: " . number_format($minTime, 3) . " ثانية\n";
    echo "⬆️ أبطأ وقت: " . number_format($maxTime, 3) . " ثانية\n\n";
    
    // حساب السرعة المتوقعة
    $batchSize = 10000;
    $pagesPerSecond = $avgTime > 0 ? $batchSize / $avgTime : 0;
    
    echo "🚀 السرعة المتوقعة: " . number_format($pagesPerSecond, 0) . " صفحة/ثانية\n\n";
    
    if ($avgTime < 1) {
        echo "✅ ممتاز! Query سريع جداً!\n";
        echo "🎯 Logstash سيحقق الهدف: 10,000 صفحة/ثانية!\n";
    } elseif ($avgTime < 3) {
        echo "⚡ جيد جداً! Query محسّن بشكل كبير\n";
        echo "   السرعة المتوقعة: ~" . number_format($pagesPerSecond, 0) . " صفحة/ثانية\n";
    } else {
        echo "🐌 لا زال بطيء - يحتاج المزيد من التحسين\n";
        echo "   السرعة الحالية: ~" . number_format($pagesPerSecond, 0) . " صفحة/ثانية\n";
    }
    
    echo "\n════════════════════════════════════════════════════════════\n";
    echo "                  الوقت المتوقع للإكمال                   \n";
    echo "════════════════════════════════════════════════════════════\n\n";
    
    $totalPages = 5024544;
    $totalSeconds = $totalPages / $pagesPerSecond;
    $totalMinutes = $totalSeconds / 60;
    $totalHours = $totalMinutes / 60;
    
    echo "📊 إجمالي الصفحات: " . number_format($totalPages) . "\n";
    echo "⏱️ الوقت المتوقع: ";
    
    if ($totalHours < 1) {
        echo number_format($totalMinutes, 1) . " دقيقة\n";
    } else {
        $hours = floor($totalHours);
        $mins = round(($totalHours - $hours) * 60);
        echo "$hours ساعة و $mins دقيقة\n";
    }
    
} catch (PDOException $e) {
    echo "❌ خطأ: {$e->getMessage()}\n";
}
