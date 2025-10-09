<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Book;
use App\Models\BookExtractedMetadata;

echo "🔍 فحص الكتب بدون أقسام...\n\n";

// عدد الكتب بدون قسم
$booksWithoutSection = Book::whereNull('book_section_id')->count();
echo "📚 عدد الكتب بدون قسم (book_section_id = NULL): {$booksWithoutSection}\n\n";

// عدد الكتب المعالجة من هذه الكتب
$processedWithoutSection = BookExtractedMetadata::whereHas('book', function($q) {
    $q->whereNull('book_section_id');
})->count();

echo "✅ عدد الكتب المعالجة من الكتب بدون قسم: {$processedWithoutSection}\n\n";

// كم كتاب تم استخراج قسم له
$extractedSections = BookExtractedMetadata::whereHas('book', function($q) {
    $q->whereNull('book_section_id');
})->whereNotNull('extracted_section_name')->count();

echo "📝 عدد الكتب التي تم استخراج قسم لها: {$extractedSections}\n\n";

// عينة من الكتب بدون قسم
echo "📋 عينة من 10 كتب بدون قسم:\n";
echo "════════════════════════════════════════\n\n";

$sample = Book::whereNull('book_section_id')
    ->with('extractedMetadata')
    ->limit(10)
    ->get();

foreach ($sample as $index => $book) {
    $num = $index + 1;
    echo "[{$num}] {$book->name} (ID: {$book->id})\n";
    
    if ($book->extractedMetadata) {
        echo "    ✅ تمت المعالجة\n";
        if ($book->extractedMetadata->extracted_section_name) {
            echo "    📌 القسم المستخرج: {$book->extractedMetadata->extracted_section_name}\n";
        } else {
            echo "    ❌ لم يتم استخراج قسم\n";
        }
    } else {
        echo "    ⏳ لم تتم المعالجة بعد\n";
    }
    
    // عرض جزء من الوصف
    if ($book->description) {
        $desc = mb_substr($book->description, 0, 150);
        echo "    الوصف: {$desc}...\n";
    }
    echo "\n";
}

echo "✅ تم الفحص!\n";
