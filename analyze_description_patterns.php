<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Book;

echo "تحليل أوصاف الكتب للبحث عن أنماط القسم\n";
echo str_repeat("=", 80) . "\n\n";

// جلب 20 كتاب عشوائي
$books = Book::whereNotNull('description')
    ->where('description', '!=', '')
    ->inRandomOrder()
    ->limit(20)
    ->get();

$patterns = [
    'القسم' => 0,
    'التصنيف' => 0,
    'الموضوع' => 0,
    'المجال' => 0,
    'الفن' => 0,
];

foreach ($books as $index => $book) {
    echo "كتاب #" . ($index + 1) . ": {$book->title}\n";
    echo str_repeat("-", 80) . "\n";
    
    // عرض الوصف الكامل
    echo $book->description . "\n\n";
    
    // البحث عن الكلمات المفتاحية
    foreach ($patterns as $keyword => &$count) {
        if (mb_stripos($book->description, $keyword) !== false) {
            $count++;
            echo "✓ يحتوي على: {$keyword}\n";
        }
    }
    
    echo "\n" . str_repeat("=", 80) . "\n\n";
}

// الإحصائيات
echo "إحصائيات الأنماط المكتشفة:\n";
echo str_repeat("=", 80) . "\n";
foreach ($patterns as $keyword => $count) {
    $percentage = round(($count / $books->count()) * 100, 1);
    echo "{$keyword}: {$count} / {$books->count()} ({$percentage}%)\n";
}
