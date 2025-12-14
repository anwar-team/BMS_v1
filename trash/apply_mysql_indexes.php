<?php
/**
 * تطبيق MySQL Indexes لتسريع Logstash Query
 */

$host = '145.223.98.97';
$user = 'bms';
$pass = 'bms2025';
$db = 'bms';

echo "════════════════════════════════════════════════════════════\n";
echo "        تحسين MySQL Indexes لتسريع Logstash               \n";
echo "════════════════════════════════════════════════════════════\n\n";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ الاتصال بقاعدة البيانات نجح\n\n";
    
    // قائمة الـ indexes المطلوبة
    $indexes = [
        [
            'table' => 'pages',
            'name' => 'idx_pages_id_optimized',
            'columns' => 'id',
            'desc' => 'Index على pages.id للترتيب والفلترة'
        ],
        [
            'table' => 'pages',
            'name' => 'idx_pages_book_id',
            'columns' => 'book_id',
            'desc' => 'Index على pages.book_id للـ JOIN'
        ],
        [
            'table' => 'author_book',
            'name' => 'idx_author_book_main',
            'columns' => 'book_id, is_main',
            'desc' => 'Index مركب على author_book (book_id, is_main)'
        ],
        [
            'table' => 'author_book',
            'name' => 'idx_author_book_author',
            'columns' => 'author_id',
            'desc' => 'Index على author_book.author_id'
        ],
        [
            'table' => 'books',
            'name' => 'idx_books_section',
            'columns' => 'book_section_id',
            'desc' => 'Index على books.book_section_id'
        ]
    ];
    
    foreach ($indexes as $index) {
        echo "🔧 {$index['desc']}\n";
        echo "   الجدول: {$index['table']}, Index: {$index['name']}\n";
        
        // فحص إذا Index موجود
        $stmt = $pdo->query("
            SELECT COUNT(*) 
            FROM information_schema.statistics 
            WHERE table_schema = '$db' 
            AND table_name = '{$index['table']}' 
            AND index_name = '{$index['name']}'
        ");
        
        $exists = $stmt->fetchColumn() > 0;
        
        if ($exists) {
            echo "   ⏭️ Index موجود بالفعل\n\n";
            continue;
        }
        
        // إنشاء Index
        try {
            $sql = "ALTER TABLE {$index['table']} ADD INDEX {$index['name']} ({$index['columns']})";
            $pdo->exec($sql);
            echo "   ✅ تم إنشاء Index بنجاح!\n\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate key') !== false) {
                echo "   ⏭️ Index موجود (Duplicate)\n\n";
            } else {
                echo "   ❌ خطأ: {$e->getMessage()}\n\n";
            }
        }
    }
    
    echo "════════════════════════════════════════════════════════════\n";
    echo "              تحليل الجداول (ANALYZE TABLE)               \n";
    echo "════════════════════════════════════════════════════════════\n\n";
    
    $tables = ['pages', 'books', 'author_book', 'authors', 'book_sections'];
    
    foreach ($tables as $table) {
        echo "📊 تحليل جدول: $table... ";
        try {
            $pdo->exec("ANALYZE TABLE $table");
            echo "✅\n";
        } catch (PDOException $e) {
            echo "❌ {$e->getMessage()}\n";
        }
    }
    
    echo "\n════════════════════════════════════════════════════════════\n";
    echo "                   اختبار السرعة                          \n";
    echo "════════════════════════════════════════════════════════════\n\n";
    
    // اختبار Query
    $testSql = "
        SELECT 
          p.id,
          p.page_number,
          p.content,
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
    
    echo "🧪 تشغيل Query الاختباري...\n";
    $startTime = microtime(true);
    
    $stmt = $pdo->query($testSql);
    $results = $stmt->fetchAll();
    
    $endTime = microtime(true);
    $duration = $endTime - $startTime;
    
    echo "⏱️ الوقت: " . number_format($duration, 2) . " ثانية\n";
    echo "📄 عدد النتائج: " . count($results) . "\n\n";
    
    if ($duration < 1) {
        echo "✅ ممتاز! Query سريع جداً!\n";
        echo "🚀 Logstash سيكون بسرعة 10,000 صفحة/ثانية!\n";
    } elseif ($duration < 5) {
        echo "⚡ جيد! Query محسّن بشكل كبير\n";
    } else {
        echo "🐌 لا زال بطيء - يحتاج المزيد من التحسين\n";
    }
    
    // عرض EXPLAIN
    echo "\n📋 EXPLAIN Query:\n";
    echo "════════════════════════════════════════════════════════════\n";
    
    $explain = $pdo->query("EXPLAIN " . $testSql)->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($explain as $row) {
        echo "جدول: {$row['table']}\n";
        echo "  نوع: {$row['type']}\n";
        echo "  Index: " . ($row['key'] ?? 'بدون') . "\n";
        echo "  Rows: {$row['rows']}\n";
        echo "  Extra: {$row['Extra']}\n";
        echo "---\n";
    }
    
} catch (PDOException $e) {
    echo "❌ خطأ في الاتصال: {$e->getMessage()}\n";
}
