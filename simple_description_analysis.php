#!/usr/bin/env php
<?php

/**
 * Simple Book Description Analysis
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Book Description Analysis Report\n";
echo "================================\n\n";

// 1. Basic Stats
$totalBooks = DB::table('books')->count();
$booksWithDesc = DB::table('books')
    ->whereNotNull('description')
    ->where('description', '!=', '')
    ->count();

echo "Total Books: " . number_format($totalBooks) . "\n";
echo "Books with Description: " . number_format($booksWithDesc) . " (" . round($booksWithDesc/$totalBooks*100, 2) . "%)\n\n";

// 2. Sample Descriptions
echo "Sample Book Descriptions:\n";
echo "-------------------------\n\n";

$samples = DB::table('books')
    ->select('id', 'title', 'description')
    ->whereNotNull('description')
    ->where('description', '!=', '')
    ->limit(10)
    ->get();

foreach ($samples as $i => $book) {
    echo "Book #" . ($i+1) . " (ID: {$book->id})\n";
    echo "Title: {$book->title}\n";
    echo "Description:\n{$book->description}\n";
    echo str_repeat("-", 80) . "\n\n";
}

// 3. Keyword Analysis
echo "\nKeyword Analysis:\n";
echo "-----------------\n";

$keywords = [
    'المؤلف' => 0,
    'تأليف' => 0,
    'الطبعة' => 0,
    'الناشر' => 0,
    'تحقيق' => 0,
    'المحقق' => 0,
];

foreach ($keywords as $keyword => $count) {
    $count = DB::table('books')
        ->whereNotNull('description')
        ->where('description', 'LIKE', "%{$keyword}%")
        ->count();
    
    $percentage = $booksWithDesc > 0 ? round($count/$booksWithDesc*100, 2) : 0;
    echo "{$keyword}: {$count} books ({$percentage}%)\n";
}

echo "\nDone!\n";
