<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Book;
use App\Models\BookSection;

echo "🔍 فحص أوصاف الكتب بدون أقسام...\n\n";

// عينة من الكتب المعالجة حديثاً
$books = Book::whereNull('book_section_id')
    ->whereHas('extractedMetadata')
    ->limit(20)
    ->get();

echo "📚 عينة من 20 كتاب معالج:\n";
echo "════════════════════════════════════════\n\n";

foreach ($books as $index => $book) {
    $num = $index + 1;
    echo "[{$num}] {$book->name}\n";
    echo "الوصف:\n";
    echo substr($book->description, 0, 500) . "\n";
    echo "─────────────────────────────────────\n\n";
    
    if ($num == 5) break; // فقط 5 كتب للمراجعة
}

// فحص جميع أنماط الأقسام الموجودة
echo "\n📋 البحث عن كلمات دالة على الأقسام:\n";
echo "════════════════════════════════════════\n\n";

$keywords = [
    'القسم',
    'التصنيف',
    'الموضوع',
    'قسم',
    'تصنيف',
    'موضوع',
    'الباب',
    'الفن',
    'العلم'
];

foreach ($keywords as $keyword) {
    $count = Book::whereNull('book_section_id')
        ->where('description', 'LIKE', "%{$keyword}%")
        ->count();
    
    if ($count > 0) {
        echo "✓ '{$keyword}': {$count} كتاب\n";
        
        // عينة
        $sample = Book::whereNull('book_section_id')
            ->where('description', 'LIKE', "%{$keyword}%")
            ->first();
        
        if ($sample) {
            // استخراج السطر الذي يحتوي على الكلمة
            $lines = explode("\n", $sample->description);
            foreach ($lines as $line) {
                if (stripos($line, $keyword) !== false) {
                    echo "   مثال: {$line}\n";
                    break;
                }
            }
        }
        echo "\n";
    }
}

// عرض قائمة الأقسام المتوفرة
echo "\n📑 الأقسام المتوفرة في قاعدة البيانات:\n";
echo "════════════════════════════════════════\n\n";

$sections = BookSection::orderBy('name')->get();
foreach ($sections as $section) {
    echo "- {$section->name}\n";
}

echo "\n✅ تم الفحص!\n";
