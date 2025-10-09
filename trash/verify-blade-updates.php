<?php

/**
 * التحقق من تحديثات ملف Blade
 * يتأكد من أن جميع التعديلات تمت بنجاح
 */

$bladePath = __DIR__ . '/resources/views/ultra-fast-search/views/ultra-fast.blade.php';

echo "=== التحقق من تحديثات Blade ===\n\n";

if (!file_exists($bladePath)) {
    echo "❌ الملف غير موجود!\n";
    exit(1);
}

$content = file_get_contents($bladePath);

// 1. تحقق من وجود النظام الجديد
echo "1. التحقق من عنوان النظام الجديد...\n";
if (strpos($content, 'نوع البحث (النظام الجديد)') !== false) {
    echo "   ✅ العنوان الجديد موجود\n";
} else {
    echo "   ❌ العنوان الجديد غير موجود\n";
}

// 2. تحقق من وجود الأنواع الثلاثة الجديدة
echo "\n2. التحقق من أنواع البحث الثلاثة...\n";

$types = [
    'flexible_match' => 'البحث المرن',
    'exact_match' => 'البحث المطابق',
    'morphological' => 'البحث الصرفي'
];

foreach ($types as $value => $label) {
    if (strpos($content, "value=\"$value\"") !== false && strpos($content, $label) !== false) {
        echo "   ✅ $label ($value) موجود\n";
    } else {
        echo "   ❌ $label ($value) غير موجود\n";
    }
}

// 3. تحقق من حذف الأنواع القديمة
echo "\n3. التحقق من حذف الأنواع القديمة...\n";

$oldTypes = [
    'name="searchMode"',
    'exact_phrase',
    'phrase_proximity',
    'all_words',
    'any_word',
    'name="proximityMode"'
];

$foundOld = false;
foreach ($oldTypes as $old) {
    if (strpos($content, $old) !== false) {
        echo "   ⚠️  تحذير: '$old' مازال موجود في الملف\n";
        $foundOld = true;
    }
}

if (!$foundOld) {
    echo "   ✅ جميع الأنواع القديمة تم حذفها\n";
}

// 4. تحقق من استخدام searchType في JavaScript
echo "\n4. التحقق من JavaScript...\n";

$jsChecks = [
    'input[name="searchType"]' => 'selector الجديد',
    'search_type: searchType' => 'إرسال searchType للـ API',
    'displayResults(results, pagination, searchTime, searchType)' => 'دالة displayResults المُحدّثة'
];

foreach ($jsChecks as $check => $label) {
    if (strpos($content, $check) !== false) {
        echo "   ✅ $label موجود\n";
    } else {
        echo "   ❌ $label غير موجود\n";
    }
}

// 5. تحقق من حذف دالة updateSearchModeHelp
echo "\n5. التحقق من حذف الدوال القديمة...\n";
if (strpos($content, 'updateSearchModeHelp()') === false) {
    echo "   ✅ دالة updateSearchModeHelp تم حذفها\n";
} else {
    echo "   ⚠️  دالة updateSearchModeHelp مازالت موجودة\n";
}

// 6. تحقق من الأيقونات
echo "\n6. التحقق من الأيقونات...\n";
$icons = ['🔄', '🎯', '🌳'];
foreach ($icons as $icon) {
    if (strpos($content, $icon) !== false) {
        echo "   ✅ أيقونة $icon موجودة\n";
    } else {
        echo "   ❌ أيقونة $icon غير موجودة\n";
    }
}

// 7. إحصائيات عامة
echo "\n=== إحصائيات عامة ===\n";
echo "حجم الملف: " . number_format(strlen($content)) . " حرف\n";
echo "عدد الأسطر: " . substr_count($content, "\n") . " سطر\n";

echo "\n✅ انتهى التحقق!\n";
