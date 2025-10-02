<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Book;
use App\Models\Volume;
use App\Models\Chapter;

echo "=== اختبار دالة buildTableOfContents المحسنة ===\n\n";

// محاكاة الدالة المحسنة
function getUniqueChaptersTree($bookId, $volumeId = null)
{
    // جلب الفصول الرئيسية فقط (parent_id = NULL)
    $query = Chapter::where('book_id', $bookId)
        ->whereNull('parent_id');
    
    // إذا كان هناك volume_id محدد، نجلب الفصول الخاصة به فقط
    if ($volumeId !== null) {
        $query->where('volume_id', $volumeId);
    }
    
    // ترتيب حسب order
    $chapters = $query->orderBy('order')->get();
    
    echo "    عدد الفصول قبل إزالة التكرار: {$chapters->count()}\n";
    
    // إزالة التكرار: الاحتفاظ بأول فصل فقط لكل (title, order, parent_id)
    $uniqueChapters = $chapters->unique(function ($chapter) {
        return $chapter->parent_id . '|' . $chapter->order . '|' . $chapter->title;
    });
    
    echo "    عدد الفصول بعد إزالة التكرار: {$uniqueChapters->count()}\n";
    
    // جلب الفصول الفرعية بشكل متكرر لكل فصل رئيسي
    foreach ($uniqueChapters as $chapter) {
        $chapter->children = getUniqueChildrenChapters($chapter->id);
    }
    
    return $uniqueChapters->values();
}

function getUniqueChildrenChapters($parentId)
{
    // جلب الفصول الفرعية المباشرة
    $children = Chapter::where('parent_id', $parentId)
        ->orderBy('order')
        ->get();
    
    // إزالة التكرار
    $uniqueChildren = $children->unique(function ($chapter) {
        return $chapter->parent_id . '|' . $chapter->order . '|' . $chapter->title;
    });
    
    // جلب الفصول الفرعية بشكل متكرر
    foreach ($uniqueChildren as $child) {
        $child->children = getUniqueChildrenChapters($child->id);
    }
    
    return $uniqueChildren->values();
}

// اختبار على كتاب أحكام القرآن
$bookId = 23;
$book = Book::find($bookId);
echo "الكتاب: {$book->title} (ID: {$bookId})\n\n";

$volumes = Volume::where('book_id', $bookId)
    ->orderBy('number')
    ->get();

echo "عدد الأجزاء: {$volumes->count()}\n\n";

foreach ($volumes->take(2) as $volume) {
    echo "=== الجزء {$volume->number}: {$volume->title} ===\n";
    $volume->chapters = getUniqueChaptersTree($bookId, $volume->id);
    echo "    عدد الفصول النهائي: {$volume->chapters->count()}\n";
    
    // عرض أول 3 فصول
    echo "    أول 3 فصول:\n";
    foreach ($volume->chapters->take(3) as $chapter) {
        echo "      - {$chapter->title} (عدد الفروع: {$chapter->children->count()})\n";
    }
    echo "\n";
}
