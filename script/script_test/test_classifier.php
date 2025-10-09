<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Book;
use App\Services\BookSectionClassifier;

echo "🧪 اختبار المُصنِّف الذكي للأقسام...\n\n";

$classifier = new BookSectionClassifier();

// اختيار 10 كتب بدون أقسام
$books = Book::whereNull('book_section_id')
    ->whereNotNull('description')
    ->where('description', '!=', '')
    ->limit(10)
    ->get();

echo "📚 اختبار على 10 كتب:\n";
echo "════════════════════════════════════════\n\n";

$successCount = 0;

foreach ($books as $index => $book) {
    $num = $index + 1;
    echo "[{$num}] {$book->name}\n";
    
    $result = $classifier->classify($book);
    
    if ($result && $result['confidence'] >= 50) {
        echo "✅ القسم المقترح: {$result['section_name']}\n";
        echo "   الثقة: {$result['confidence']}%\n";
        echo "   النقاط: {$result['score']}\n";
        $successCount++;
    } else {
        echo "❌ لم يتم العثور على تصنيف مناسب\n";
        if ($result) {
            echo "   أفضل خيار: {$result['section_name']} (ثقة: {$result['confidence']}%)\n";
        }
    }
    
    // عرض جزء من الوصف
    $desc = mb_substr($book->description, 0, 150);
    echo "   الوصف: {$desc}...\n";
    echo "\n";
}

echo "════════════════════════════════════════\n";
echo "✅ النتيجة: {$successCount}/10 كتب تم تصنيفها بنجاح\n";
