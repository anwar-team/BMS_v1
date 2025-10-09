<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo str_repeat("=", 120) . "\n";
echo "قائمة الـ 50 كتاب المعالجة مع تفاصيل الاستخراج\n";
echo str_repeat("=", 120) . "\n\n";

$books = DB::table('book_extracted_metadata as bem')
    ->join('books as b', 'b.id', '=', 'bem.book_id')
    ->leftJoin('authors as a', 'a.id', '=', 'bem.matched_author_id')
    ->leftJoin('publishers as p', 'p.id', '=', 'bem.matched_publisher_id')
    ->select(
        'b.id',
        'b.title',
        'bem.extracted_author_name',
        'a.full_name as matched_author',
        'bem.author_match_confidence',
        'bem.extracted_publisher_name',
        'p.name as matched_publisher',
        'bem.publisher_match_confidence',
        'bem.is_applied',
        'bem.needs_review',
        'bem.processing_status'
    )
    ->orderBy('bem.id', 'desc')
    ->limit(50)
    ->get();

foreach ($books as $i => $book) {
    $num = $i + 1;
    
    echo "[$num] {$book->title}\n";
    echo "    ID: {$book->id}\n";
    
    // المؤلف
    if ($book->extracted_author_name) {
        $authorIcon = $book->author_match_confidence >= 0.80 ? '✅' : '⚠️';
        $conf = round($book->author_match_confidence * 100);
        
        echo "    📖 مؤلف مستخرج: {$book->extracted_author_name}\n";
        
        if ($book->matched_author) {
            echo "       مطابق مع: {$book->matched_author} {$authorIcon} ({$conf}%)\n";
        } else {
            echo "       ❌ لم يتم العثور على مطابقة\n";
        }
    }
    
    // الناشر
    if ($book->extracted_publisher_name) {
        $pubIcon = $book->publisher_match_confidence >= 0.80 ? '✅' : '⚠️';
        $pconf = round($book->publisher_match_confidence * 100);
        
        echo "    🏢 ناشر مستخرج: {$book->extracted_publisher_name}\n";
        
        if ($book->matched_publisher) {
            echo "       مطابق مع: {$book->matched_publisher} {$pubIcon} ({$pconf}%)\n";
        } else {
            echo "       ❌ لم يتم العثور على مطابقة\n";
        }
    }
    
    // الحالة
    if ($book->is_applied) {
        echo "    ✅ الحالة: تم التطبيق على الكتاب\n";
    } elseif ($book->needs_review) {
        echo "    ⚠️  الحالة: يحتاج مراجعة يدوية\n";
    } else {
        echo "    ⏳ الحالة: جاهز للتطبيق\n";
    }
    
    echo "\n";
}

echo str_repeat("=", 120) . "\n";

// إحصائيات
$totalBooks = $books->count();
$withAuthor = $books->where('extracted_author_name', '!=', null)->count();
$withPublisher = $books->where('extracted_publisher_name', '!=', null)->count();
$applied = $books->where('is_applied', 1)->count();
$needsReview = $books->where('needs_review', 1)->count();

echo "\n📊 ملخص الإحصائيات:\n";
echo "   - إجمالي الكتب: {$totalBooks}\n";
echo "   - مؤلفين مستخرجين: {$withAuthor} (" . round(($withAuthor/$totalBooks)*100, 1) . "%)\n";
echo "   - ناشرين مستخرجين: {$withPublisher} (" . round(($withPublisher/$totalBooks)*100, 1) . "%)\n";
echo "   - تم التطبيق: {$applied} (" . round(($applied/$totalBooks)*100, 1) . "%)\n";
echo "   - يحتاج مراجعة: {$needsReview} (" . round(($needsReview/$totalBooks)*100, 1) . "%)\n";

echo "\n";
