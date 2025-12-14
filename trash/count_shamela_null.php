<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Book;

echo "🔍 فحص الكتب بدون shamela_id...\n\n";

$booksWithoutShamela = Book::whereNull('shamela_id')->count();

echo "📚 عدد الكتب التي shamela_id = NULL: {$booksWithoutShamela}\n\n";

// كم منهم بدون قسم
$withoutSection = Book::whereNull('shamela_id')->whereNull('book_section_id')->count();
echo "   - بدون قسم (book_section_id = NULL): {$withoutSection}\n";

// كم منهم لديهم قسم
$withSection = Book::whereNull('shamela_id')->whereNotNull('book_section_id')->count();
echo "   - لديهم قسم: {$withSection}\n\n";

echo "✅ سيتم معالجة جميع الـ {$booksWithoutShamela} كتاب!\n";
