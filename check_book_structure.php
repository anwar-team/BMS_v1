<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Book;
use App\Models\Volume;
use App\Models\Chapter;

echo "=== فحص كتاب بتفرعات كثيرة ===\n\n";

// البحث عن كتاب لديه فصول فرعية كثيرة
$bookId = 23; // كتاب أحكام القرآن (لديه 2968 فصل)

$book = Book::find($bookId);
echo "الكتاب: {$book->title} (ID: {$bookId})\n\n";

// جلب الأجزاء
$volumes = Volume::where('book_id', $bookId)
    ->orderBy('number')
    ->get(['id', 'number', 'title']);

echo "عدد الأجزاء: {$volumes->count()}\n\n";

foreach ($volumes as $volume) {
    echo "=== الجزء {$volume->number}: {$volume->title} (Volume ID: {$volume->id}) ===\n";
    
    // الفصول الرئيسية في هذا الجزء
    $rootChapters = Chapter::where('book_id', $bookId)
        ->where('volume_id', $volume->id)
        ->whereNull('parent_id')
        ->orderBy('order')
        ->get(['id', 'title', 'parent_id', 'level', 'order']);
    
    echo "عدد الفصول الرئيسية: {$rootChapters->count()}\n";
    
    // عرض أول 3 فصول رئيسية مع أبنائها
    echo "\nأول 3 فصول رئيسية مع فروعها:\n";
    $counter = 0;
    foreach ($rootChapters as $rootChapter) {
        if ($counter >= 3) break;
        
        echo "  ┌─ {$rootChapter->title} (ID: {$rootChapter->id}, Level: {$rootChapter->level}, Order: {$rootChapter->order})\n";
        
        // جلب الفصول الفرعية المباشرة
        $childChapters = Chapter::where('parent_id', $rootChapter->id)
            ->orderBy('order')
            ->get(['id', 'title', 'parent_id', 'level', 'order']);
        
        foreach ($childChapters as $child) {
            echo "  │  ├─ {$child->title} (ID: {$child->id}, Level: {$child->level}, Order: {$child->order})\n";
            
            // جلب الفصول الفرعية من المستوى الثاني
            $grandChildChapters = Chapter::where('parent_id', $child->id)
                ->orderBy('order')
                ->get(['id', 'title', 'parent_id', 'level', 'order']);
            
            foreach ($grandChildChapters as $grandChild) {
                echo "  │  │  ├─ {$grandChild->title} (ID: {$grandChild->id}, Level: {$grandChild->level}, Order: {$grandChild->order})\n";
            }
        }
        
        $counter++;
    }
    
    echo "\n";
    
    // كسر الحلقة بعد أول جزء للاختصار
    if ($volume->number >= 2) break;
}

// فحص مشكلة التكرار المحتملة
echo "\n=== فحص مشكلة التكرار المحتملة ===\n";

// هل هناك فصول لها نفس parent_id ونفس order؟
$duplicateOrders = Chapter::select('book_id', 'parent_id', 'order', \DB::raw('COUNT(*) as count'))
    ->where('book_id', $bookId)
    ->groupBy('book_id', 'parent_id', 'order')
    ->having('count', '>', 1)
    ->get();

if ($duplicateOrders->count() > 0) {
    echo "تم اكتشاف فصول مكررة بنفس parent_id و order:\n";
    foreach ($duplicateOrders->take(5) as $dup) {
        echo "  - Parent ID: {$dup->parent_id}, Order: {$dup->order}, Count: {$dup->count}\n";
    }
} else {
    echo "لا توجد فصول مكررة بنفس parent_id و order.\n";
}

// هل هناك فصول لها نفس title في نفس المستوى؟
$duplicateTitles = Chapter::select('book_id', 'parent_id', 'title', \DB::raw('COUNT(*) as count'))
    ->where('book_id', $bookId)
    ->groupBy('book_id', 'parent_id', 'title')
    ->having('count', '>', 1)
    ->get();

if ($duplicateTitles->count() > 0) {
    echo "\nتم اكتشاف فصول مكررة بنفس parent_id و title:\n";
    foreach ($duplicateTitles->take(5) as $dup) {
        echo "  - Parent ID: {$dup->parent_id}, Title: {$dup->title}, Count: {$dup->count}\n";
    }
} else {
    echo "\nلا توجد فصول مكررة بنفس parent_id و title.\n";
}
