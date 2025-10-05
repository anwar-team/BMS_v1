<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Book;

echo "إحصائيات الكتب:\n";
echo str_repeat("=", 50) . "\n\n";

$total = Book::count();
$withShamela = Book::whereNotNull('shamela_id')->count();
$withoutShamela = Book::whereNull('shamela_id')->count();
$withoutShamelaWithDesc = Book::whereNull('shamela_id')
    ->whereNotNull('description')
    ->where('description', '!=', '')
    ->count();

echo "إجمالي الكتب: {$total}\n";
echo "كتب WITH shamela_id: {$withShamela}\n";
echo "كتب WITHOUT shamela_id: {$withoutShamela}\n";
echo "كتب بدون shamela_id ولديها وصف: {$withoutShamelaWithDesc}\n\n";

if ($withoutShamela === 0) {
    echo "⚠️  جميع الكتب لديها shamela_id!\n";
    echo "سنقوم باختبار الـ Matcher على كتب عشوائية فقط لاختبار الوظيفة.\n";
}
