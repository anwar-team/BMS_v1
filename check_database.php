<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Book;
use App\Models\Volume;
use App\Models\Chapter;

echo "=== فحص قاعدة البيانات للكتب والفهارس ===\n\n";

// عدد الكتب الإجمالي
$totalBooks = Book::count();
echo "عدد الكتب الإجمالي: {$totalBooks}\n\n";

// فحص أول 3 كتب
$books = Book::take(3)->get(['id', 'title']);

foreach ($books as $book) {
    echo "====================================\n";
    echo "الكتاب: {$book->title} (ID: {$book->id})\n";
    echo "====================================\n";
    
    // عدد الأجزاء
    $volumesCount = Volume::where('book_id', $book->id)->count();
    echo "عدد الأجزاء: {$volumesCount}\n";
    
    if ($volumesCount > 0) {
        $volumes = Volume::where('book_id', $book->id)
            ->orderBy('number')
            ->get(['id', 'number', 'title', 'book_id']);
        
        echo "\nالأجزاء:\n";
        foreach ($volumes as $volume) {
            echo "  - الجزء {$volume->number}: {$volume->title} (Volume ID: {$volume->id})\n";
            
            // عدد الفصول في هذا الجزء
            $volumeChapters = Chapter::where('volume_id', $volume->id)->count();
            echo "    عدد الفصول في هذا الجزء: {$volumeChapters}\n";
        }
    }
    
    // عدد الفصول الإجمالي
    $totalChapters = Chapter::where('book_id', $book->id)->count();
    echo "\nعدد الفصول الإجمالي: {$totalChapters}\n";
    
    // الفصول الرئيسية (parent_id = NULL)
    $rootChapters = Chapter::where('book_id', $book->id)
        ->whereNull('parent_id')
        ->count();
    echo "عدد الفصول الرئيسية (parent_id = NULL): {$rootChapters}\n";
    
    // الفصول الفرعية (parent_id != NULL)
    $childChapters = Chapter::where('book_id', $book->id)
        ->whereNotNull('parent_id')
        ->count();
    echo "عدد الفصول الفرعية (parent_id != NULL): {$childChapters}\n";
    
    // عينة من الفصول (أول 5 فصول)
    $sampleChapters = Chapter::where('book_id', $book->id)
        ->orderBy('order')
        ->take(10)
        ->get(['id', 'title', 'parent_id', 'volume_id', 'order', 'level']);
    
    if ($sampleChapters->isNotEmpty()) {
        echo "\nعينة من الفصول (أول 10):\n";
        foreach ($sampleChapters as $chapter) {
            $parentInfo = $chapter->parent_id ? " (Parent ID: {$chapter->parent_id})" : " (جذري)";
            $volumeInfo = $chapter->volume_id ? " [Volume ID: {$chapter->volume_id}]" : " [بدون جزء]";
            echo "  - {$chapter->title} (ID: {$chapter->id}, Order: {$chapter->order}, Level: {$chapter->level}){$parentInfo}{$volumeInfo}\n";
        }
    }
    
    echo "\n";
}

// فحص إجمالي توزيع الفصول حسب parent_id
echo "\n=== إحصائيات عامة ===\n";
$totalChaptersAll = Chapter::count();
$rootChaptersAll = Chapter::whereNull('parent_id')->count();
$childChaptersAll = Chapter::whereNotNull('parent_id')->count();

echo "إجمالي الفصول في قاعدة البيانات: {$totalChaptersAll}\n";
echo "الفصول الرئيسية (parent_id = NULL): {$rootChaptersAll}\n";
echo "الفصول الفرعية (parent_id != NULL): {$childChaptersAll}\n";
