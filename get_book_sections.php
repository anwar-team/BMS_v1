<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "جدول الأقسام (book_sections):\n";
echo str_repeat("=", 80) . "\n\n";

$sections = DB::table('book_sections')
    ->select('id', 'name', 'parent_id', 'is_active')
    ->orderBy('id')
    ->limit(50)
    ->get();

echo "عدد الأقسام: " . $sections->count() . "\n\n";

foreach ($sections as $section) {
    $parentInfo = $section->parent_id ? " (تحت قسم: {$section->parent_id})" : " (قسم رئيسي)";
    $active = $section->is_active ? "✓" : "✗";
    echo "[{$section->id}] {$section->name} {$parentInfo} [{$active}]\n";
}

echo "\n" . str_repeat("=", 80) . "\n";

// إحصائيات
$totalSections = DB::table('book_sections')->count();
$activeSections = DB::table('book_sections')->where('is_active', true)->count();
$parentSections = DB::table('book_sections')->whereNull('parent_id')->count();
$childSections = DB::table('book_sections')->whereNotNull('parent_id')->count();

echo "\nإحصائيات:\n";
echo "- إجمالي الأقسام: {$totalSections}\n";
echo "- الأقسام النشطة: {$activeSections}\n";
echo "- الأقسام الرئيسية: {$parentSections}\n";
echo "- الأقسام الفرعية: {$childSections}\n";

// عدد الكتب في كل قسم
echo "\n" . str_repeat("=", 80) . "\n";
echo "\nعدد الكتب في كل قسم:\n\n";

$sectionsWithBooks = DB::table('book_sections as bs')
    ->leftJoin('books as b', 'b.book_section_id', '=', 'bs.id')
    ->select('bs.id', 'bs.name', DB::raw('COUNT(b.id) as books_count'))
    ->groupBy('bs.id', 'bs.name')
    ->orderByDesc('books_count')
    ->limit(20)
    ->get();

foreach ($sectionsWithBooks as $section) {
    echo "[{$section->id}] {$section->name}: {$section->books_count} كتاب\n";
}
