<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\BookExtractedMetadata;
use Illuminate\Support\Facades\DB;

echo "🔍 فحص الأقسام المستخرجة من 1000 كتاب...\n\n";

// إحصائيات عامة
$total = BookExtractedMetadata::count();
$withSection = BookExtractedMetadata::whereNotNull('extracted_section_name')->count();
$withMatchedSection = BookExtractedMetadata::whereNotNull('matched_section_id')->count();

echo "📊 الإحصائيات:\n";
echo "════════════════════════════════════════\n";
echo "إجمالي السجلات المعالجة: {$total}\n";
echo "كتب تم استخراج اسم قسم منها: {$withSection}\n";
echo "كتب تم مطابقة قسم لها: {$withMatchedSection}\n\n";

// عرض الكتب التي تم استخراج قسم لها
if ($withSection > 0) {
    echo "📚 الكتب التي تم استخراج قسم لها:\n";
    echo "════════════════════════════════════════\n\n";
    
    $booksWithSections = BookExtractedMetadata::with(['book', 'matchedSection'])
        ->whereNotNull('extracted_section_name')
        ->get();
    
    foreach ($booksWithSections as $index => $metadata) {
        $num = $index + 1;
        echo "[{$num}] {$metadata->book->name}\n";
        echo "    القسم المستخرج: {$metadata->extracted_section_name}\n";
        
        if ($metadata->matched_section_id) {
            echo "    ✅ القسم المطابق: {$metadata->matchedSection->name} (ثقة: {$metadata->section_match_confidence}%)\n";
        } else {
            echo "    ❌ لم يتم العثور على مطابقة\n";
        }
        
        echo "    القسم الأصلي للكتاب: " . ($metadata->book->bookSection ? $metadata->book->bookSection->name : 'غير محدد') . "\n";
        echo "\n";
    }
}

// فحص الكتب بدون قسم مستخرج
$sampleWithoutSection = BookExtractedMetadata::with(['book'])
    ->whereNull('extracted_section_name')
    ->limit(5)
    ->get();

echo "\n📝 عينة من الكتب بدون قسم مستخرج (5 كتب):\n";
echo "════════════════════════════════════════\n\n";

foreach ($sampleWithoutSection as $index => $metadata) {
    $num = $index + 1;
    echo "[{$num}] {$metadata->book->name}\n";
    echo "    القسم الحالي: " . ($metadata->book->bookSection ? $metadata->book->bookSection->name : 'غير محدد') . "\n";
    echo "    الوصف: " . mb_substr($metadata->book->description, 0, 200) . "...\n\n";
}

echo "\n✅ تم الفحص بنجاح!\n";
