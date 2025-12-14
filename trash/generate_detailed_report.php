<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\BookExtractedMetadata;
use Illuminate\Support\Facades\DB;

echo "📊 تقرير مفصل لـ 100 كتاب معالج\n";
echo "════════════════════════════════════════════════════════════════\n\n";

// جلب آخر 100 كتاب معالج
$metadata = BookExtractedMetadata::with(['book', 'matchedSection', 'matchedAuthor', 'matchedPublisher'])
    ->orderBy('updated_at', 'desc')
    ->limit(100)
    ->get();

// إحصائيات عامة
$totalBooks = $metadata->count();
$withSections = $metadata->whereNotNull('matched_section_id')->count();
$withAuthors = $metadata->whereNotNull('matched_author_id')->count();
$withPublishers = $metadata->whereNotNull('matched_publisher_id')->count();

$avgSectionConfidence = $metadata->whereNotNull('section_match_confidence')->avg('section_match_confidence');
$avgAuthorConfidence = $metadata->whereNotNull('author_match_confidence')->avg('author_match_confidence');
$avgPublisherConfidence = $metadata->whereNotNull('publisher_match_confidence')->avg('publisher_match_confidence');

echo "📈 الإحصائيات العامة:\n";
echo "════════════════════════════════════════════════════════════════\n";
echo "إجمالي الكتب المعالجة: {$totalBooks}\n";
echo "كتب مع أقسام: {$withSections} (" . round($withSections/$totalBooks*100, 1) . "%)\n";
echo "كتب مع مؤلفين: {$withAuthors} (" . round($withAuthors/$totalBooks*100, 1) . "%)\n";
echo "كتب مع ناشرين: {$withPublishers} (" . round($withPublishers/$totalBooks*100, 1) . "%)\n\n";

echo "متوسط الثقة:\n";
echo "  - الأقسام: " . round($avgSectionConfidence * 100, 1) . "%\n";
echo "  - المؤلفين: " . round($avgAuthorConfidence * 100, 1) . "%\n";
echo "  - الناشرين: " . round($avgPublisherConfidence * 100, 1) . "%\n\n";

// توزيع الأقسام
echo "📚 توزيع الكتب حسب الأقسام:\n";
echo "════════════════════════════════════════════════════════════════\n";
$sectionDistribution = $metadata->whereNotNull('matched_section_id')
    ->groupBy('matched_section_id')
    ->map(function($group) {
        return [
            'name' => $group->first()->matchedSection->name ?? 'غير محدد',
            'count' => $group->count()
        ];
    })
    ->sortByDesc('count');

foreach ($sectionDistribution as $section) {
    echo "  • {$section['name']}: {$section['count']} كتاب\n";
}

echo "\n\n📋 تفاصيل الكتب الـ 100:\n";
echo "════════════════════════════════════════════════════════════════\n\n";

$appliedCount = 0;
$needsReviewCount = 0;
$highConfidenceCount = 0;

foreach ($metadata as $index => $meta) {
    $num = $index + 1;
    $book = $meta->book;
    
    echo "[{$num}] {$book->name}\n";
    echo "    ID: {$book->id}\n";
    
    // القسم
    if ($meta->matched_section_id) {
        $confidence = round($meta->section_match_confidence * 100, 1);
        $icon = $meta->section_match_confidence >= 0.80 ? '✅' : '⚠️';
        echo "    {$icon} القسم: {$meta->matchedSection->name} (ثقة: {$confidence}%)\n";
        if ($meta->extracted_section_name && strpos($meta->extracted_section_name, 'تصنيف تلقائي') !== false) {
            echo "       (تصنيف ذكي تلقائي)\n";
        }
    } else {
        echo "    ❌ القسم: لم يتم التصنيف\n";
    }
    
    // المؤلف
    if ($meta->matched_author_id) {
        $confidence = round($meta->author_match_confidence * 100, 1);
        $icon = $meta->author_match_confidence >= 0.80 ? '✅' : '⚠️';
        echo "    {$icon} المؤلف: {$meta->matchedAuthor->full_name} (ثقة: {$confidence}%)\n";
    } else if ($meta->extracted_author_name) {
        echo "    ⚠️ المؤلف المستخرج: {$meta->extracted_author_name} (لم يتم العثور على مطابقة)\n";
    } else {
        echo "    ❌ المؤلف: لم يتم الاستخراج\n";
    }
    
    // الناشر
    if ($meta->matched_publisher_id) {
        $confidence = round($meta->publisher_match_confidence * 100, 1);
        $icon = $meta->publisher_match_confidence >= 0.80 ? '✅' : '⚠️';
        echo "    {$icon} الناشر: {$meta->matchedPublisher->name} (ثقة: {$confidence}%)\n";
    } else if ($meta->extracted_publisher_name) {
        echo "    ⚠️ الناشر المستخرج: {$meta->extracted_publisher_name} (لم يتم العثور على مطابقة)\n";
    } else {
        echo "    ❌ الناشر: لم يتم الاستخراج\n";
    }
    
    // الحالة الكلية
    $overallConfidence = $meta->getOverallConfidenceAttribute();
    if ($overallConfidence >= 0.80) {
        echo "    🌟 ثقة إجمالية عالية: " . round($overallConfidence * 100, 1) . "%\n";
        $highConfidenceCount++;
    }
    
    if ($meta->is_applied) {
        echo "    ✔️ تم التطبيق تلقائياً\n";
        $appliedCount++;
    } else if ($meta->needs_review) {
        echo "    🔍 يحتاج مراجعة يدوية\n";
        $needsReviewCount++;
    }
    
    echo "\n";
}

echo "\n════════════════════════════════════════════════════════════════\n";
echo "📊 ملخص الحالات:\n";
echo "════════════════════════════════════════════════════════════════\n";
echo "✅ تم التطبيق تلقائياً: {$appliedCount} كتاب (" . round($appliedCount/$totalBooks*100, 1) . "%)\n";
echo "🔍 يحتاج مراجعة: {$needsReviewCount} كتاب (" . round($needsReviewCount/$totalBooks*100, 1) . "%)\n";
echo "🌟 ثقة عالية (≥80%): {$highConfidenceCount} كتاب (" . round($highConfidenceCount/$totalBooks*100, 1) . "%)\n";

echo "\n✅ تم إنشاء التقرير بنجاح!\n";
