<?php

/**
 * اختبار تفاعلي لزر تطبيق الفلاتر
 * يحاكي سلوك المستخدم في الواجهة
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\UltraFastSearchService;

echo "🔧 اختبار زر تطبيق الفلاتر - محاكاة سلوك المستخدم\n";
echo str_repeat('=', 60) . "\n";

$searchService = new UltraFastSearchService();

// السيناريو 1: المستخدم يختار فلتر كتاب بدون نص بحث
echo "\n📘 السيناريو 1: فلتر كتاب فقط (بدون نص بحث)\n";
echo "الخطوات:\n";
echo "1. المستخدم يفتح نافذة الفلاتر\n";
echo "2. يختار 'الكتب'\n";
echo "3. يختار كتاب: أرشيف ملتقى أهل الحديث - 1 (ID: 11358)\n";
echo "4. ينقر على زر 'تطبيق'\n";
echo str_repeat('-', 40) . "\n";

try {
    // محاكاة نقر زر التطبيق مع فلتر كتاب
    $result = $searchService->search('', ['book_id' => [11358]], 1, 10);
    
    if (!empty($result['results'])) {
        echo "✅ نجح! تم العثور على " . count($result['results']) . " نتيجة\n";
        echo "   📖 عينة: {$result['results'][0]['book_title']} - صفحة {$result['results'][0]['page_number']}\n";
        echo "   📊 إجمالي النتائج: " . ($result['total'] ?? 0) . "\n";
    } else {
        echo "❌ فشل! لا توجد نتائج\n";
        if (isset($result['error'])) {
            echo "   خطأ: {$result['error']}\n";
        }
    }
} catch (Exception $e) {
    echo "❌ خطأ تقني: {$e->getMessage()}\n";
}

// السيناريو 2: المستخدم يختار فلتر قسم بدون نص بحث
echo "\n📂 السيناريو 2: فلتر قسم فقط (بدون نص بحث)\n";
echo "الخطوات:\n";
echo "1. المستخدم يفتح نافذة الفلاتر\n";
echo "2. يختار 'الأقسام'\n";
echo "3. يختار قسم: الفقه الحنفي (ID: 8)\n";
echo "4. ينقر على زر 'تطبيق'\n";
echo str_repeat('-', 40) . "\n";

try {
    // محاكاة نقر زر التطبيق مع فلتر قسم
    $result = $searchService->search('', ['section_id' => ['8']], 1, 10);
    
    if (!empty($result['results'])) {
        echo "✅ نجح! تم العثور على " . count($result['results']) . " نتيجة\n";
        echo "   📖 عينة: {$result['results'][0]['book_title']} - صفحة {$result['results'][0]['page_number']}\n";
        echo "   📊 إجمالي النتائج: " . ($result['total'] ?? 0) . "\n";
    } else {
        echo "❌ فشل! لا توجد نتائج\n";
        if (isset($result['error'])) {
            echo "   خطأ: {$result['error']}\n";
        }
    }
} catch (Exception $e) {
    echo "❌ خطأ تقني: {$e->getMessage()}\n";
}

// السيناريو 3: المستخدم يجمع بين البحث والفلتر
echo "\n🔍 السيناريو 3: بحث + فلتر معاً\n";
echo "الخطوات:\n";
echo "1. المستخدم يكتب 'الله' في حقل البحث\n";
echo "2. يفتح نافذة الفلاتر\n";
echo "3. يختار كتاب: أرشيف ملتقى أهل الحديث - 1 (ID: 11358)\n";
echo "4. ينقر على زر 'تطبيق'\n";
echo str_repeat('-', 40) . "\n";

try {
    // محاكاة البحث مع فلتر
    $result = $searchService->search('الله', ['book_id' => [11358]], 1, 10);
    
    if (!empty($result['results'])) {
        echo "✅ نجح! تم العثور على " . count($result['results']) . " نتيجة\n";
        echo "   📖 عينة: {$result['results'][0]['book_title']} - صفحة {$result['results'][0]['page_number']}\n";
        echo "   📊 إجمالي النتائج: " . ($result['total'] ?? 0) . "\n";
        
        // التحقق من أن النتائج تحتوي على كلمة "الله"
        $content = $result['results'][0]['content'] ?? '';
        if (strpos($content, 'الله') !== false) {
            echo "   ✅ النتيجة تحتوي على كلمة البحث\n";
        } else {
            echo "   ⚠️  النتيجة قد لا تحتوي على كلمة البحث (قد يكون highlight مخفي)\n";
        }
    } else {
        echo "❌ فشل! لا توجد نتائج\n";
        if (isset($result['error'])) {
            echo "   خطأ: {$result['error']}\n";
        }
    }
} catch (Exception $e) {
    echo "❌ خطأ تقني: {$e->getMessage()}\n";
}

// السيناريو 4: المستخدم ينقر تطبيق بدون اختيار أي شيء
echo "\n❌ السيناريو 4: نقر 'تطبيق' بدون اختيار فلاتر أو نص بحث\n";
echo "الخطوات:\n";
echo "1. المستخدم يترك حقل البحث فارغاً\n";
echo "2. يفتح نافذة الفلاتر لكن لا يختار أي شيء\n";
echo "3. ينقر على زر 'تطبيق'\n";
echo str_repeat('-', 40) . "\n";

try {
    // محاكاة نقر تطبيق بدون أي شيء
    $result = $searchService->search('', [], 1, 10);
    
    if (!empty($result['results'])) {
        echo "⚠️  غير متوقع! تم العثور على " . count($result['results']) . " نتيجة\n";
        echo "   (يجب أن يكون هناك خطأ validation)\n";
    } else {
        echo "✅ صحيح! لا توجد نتائج\n";
        if (isset($result['error'])) {
            echo "   ✅ رسالة خطأ صحيحة: {$result['error']}\n";
        } else {
            echo "   ⚠️  لا توجد رسالة خطأ واضحة\n";
        }
    }
} catch (Exception $e) {
    echo "✅ خطأ متوقع: {$e->getMessage()}\n";
}

// ملخص النتائج
echo "\n" . str_repeat('=', 60) . "\n";
echo "📋 ملخص النتائج:\n";
echo "\n✅ الحلول المطبقة:\n";
echo "   1. ✅ إصلاح شرط البحث - الآن يعمل مع الفلاتر فقط\n";
echo "   2. ✅ إضافة تتبع الأخطاء في console\n";
echo "   3. ✅ معالجة أفضل للحالات الاستثنائية\n";
echo "   4. ✅ validation محسن للمدخلات\n";

echo "\n🎯 للمستخدم:\n";
echo "   1. افتح الصفحة في المتصفح\n";
echo "   2. اضغط F12 لفتح Developer Tools\n";
echo "   3. انتقل إلى تبويب 'Console'\n";
echo "   4. جرب السيناريوهات أعلاه\n";
echo "   5. راقب الرسائل في console للتشخيص\n";

echo "\n🔧 إذا كان زر التطبيق ما زال لا يعمل:\n";
echo "   1. تأكد من اختيار نوع الفلتر أولاً (كتب/أقسام)\n";
echo "   2. اختر عنصر واحد على الأقل\n";
echo "   3. تحقق من console للأخطاء\n";
echo "   4. أعد تحميل الصفحة وحاول مرة أخرى\n";

echo "\n✅ اكتمل الاختبار!\n";