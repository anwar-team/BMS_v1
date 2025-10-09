<?php

/**
 * سكريبت لتحديث ملف Blade ليستخدم النظام الجديد
 */

$filePath = __DIR__ . '/resources/views/ultra-fast-search/views/ultra-fast.blade.php';
$content = file_get_contents($filePath);

// النسخة الاحتياطية
file_put_contents($filePath . '.backup', $content);

// استبدال قسم "طبيعة البحث"
$oldSection = <<<'HTML'
                                                    <!-- طبيعة البحث -->
                                                    <div class="mb-4">
                                                        <h3 class="text-sm font-medium text-gray-700 mb-2 text-right">طبيعة البحث</h3>
                                                        <div class="space-y-1">
                                                            <label class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-md cursor-pointer">
                                                                <input type="radio" name="searchMode" value="flexible" class="text-emerald-600 focus:ring-emerald-500">
                                                                <div class="flex items-center gap-2 flex-1 text-right">
                                                                    <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                                                                    <span class="text-sm">البحث المرن</span>
                                                                </div>
                                                            </label>
                                                            <label class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-md cursor-pointer">
                                                                <input type="radio" name="searchMode" value="exact_phrase" class="text-blue-600 focus:ring-blue-500">
                                                                <div class="flex items-center gap-2 flex-1 text-right">
                                                                    <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                                                                    <span class="text-sm">مطابقة العبارة تماماً</span>
                                                                </div>
                                                            </label>
                                                            <label class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-md cursor-pointer">
                                                                <input type="radio" name="searchMode" value="phrase_proximity" class="text-purple-600 focus:ring-purple-500" checked>
                                                                <div class="flex items-center gap-2 flex-1 text-right">
                                                                    <span class="w-2 h-2 bg-purple-500 rounded-full"></span>
                                                                    <span class="text-sm">عبارة مع تباعد مسموح</span>
                                                                </div>
                                                            </label>
                                                            <label class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-md cursor-pointer">
                                                                <input type="radio" name="searchMode" value="all_words" class="text-orange-600 focus:ring-orange-500">
                                                                <div class="flex items-center gap-2 flex-1 text-right">
                                                                    <span class="w-2 h-2 bg-orange-500 rounded-full"></span>
                                                                    <span class="text-sm">جميع الكلمات مطلوبة</span>
                                                                </div>
                                                            </label>
                                                            <label class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-md cursor-pointer">
                                                                <input type="radio" name="searchMode" value="any_word" class="text-red-600 focus:ring-red-500">
                                                                <div class="flex items-center gap-2 flex-1 text-right">
                                                                    <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                                                                    <span class="text-sm">أي كلمة من الكلمات</span>
                                                                </div>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- تباعد الكلمات -->
                                                    <div class="border-t border-gray-200 pt-3">
                                                        <h3 class="text-sm font-medium text-gray-700 mb-2 text-right">تباعد الكلمات</h3>
                                                        <div class="space-y-1">
                                                            <label class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-md cursor-pointer">
                                                                <input type="radio" name="proximityMode" value="any_order" class="text-gray-600 focus:ring-gray-500" checked>
                                                                <span class="text-sm text-right flex-1">أي ترتيب</span>
                                                            </label>
                                                            <label class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-md cursor-pointer">
                                                                <input type="radio" name="proximityMode" value="consecutive" class="text-gray-600 focus:ring-gray-500">
                                                                <span class="text-sm text-right flex-1">متتالية (ورا بعض)</span>
                                                            </label>
                                                            <label class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-md cursor-pointer">
                                                                <input type="radio" name="proximityMode" value="same_paragraph" class="text-gray-600 focus:ring-gray-500">
                                                                <span class="text-sm text-right flex-1">نفس الفقرة</span>
                                                            </label>
                                                        </div>
                                                    </div>
HTML;

$newSection = <<<'HTML'
                                                    <!-- نوع البحث (النظام الجديد) -->
                                                    <div class="mb-4">
                                                        <h3 class="text-sm font-medium text-gray-700 mb-3 text-right">🔍 نوع البحث</h3>
                                                        <div class="space-y-2">
                                                            <!-- البحث المرن (الافتراضي) -->
                                                            <label class="search-type-card flex flex-col gap-2 p-3 hover:bg-emerald-50 rounded-lg cursor-pointer border-2 border-transparent hover:border-emerald-300 transition-all">
                                                                <div class="flex items-center gap-3">
                                                                    <input type="radio" name="searchType" value="flexible_match" class="text-emerald-600 focus:ring-emerald-500" checked>
                                                                    <div class="flex items-center gap-2 flex-1 text-right">
                                                                        <span class="text-2xl">🔄</span>
                                                                        <div>
                                                                            <span class="text-sm font-medium block">البحث المرن</span>
                                                                            <span class="text-xs text-gray-500">يسمح باللواصق (ال، و، ف)</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="text-xs text-gray-600 pr-8">
                                                                    مثال: "صلاة" = "الصلاة" = "وصلاة"
                                                                </div>
                                                            </label>
                                                            
                                                            <!-- البحث المطابق -->
                                                            <label class="search-type-card flex flex-col gap-2 p-3 hover:bg-blue-50 rounded-lg cursor-pointer border-2 border-transparent hover:border-blue-300 transition-all">
                                                                <div class="flex items-center gap-3">
                                                                    <input type="radio" name="searchType" value="exact_match" class="text-blue-600 focus:ring-blue-500">
                                                                    <div class="flex items-center gap-2 flex-1 text-right">
                                                                        <span class="text-2xl">🎯</span>
                                                                        <div>
                                                                            <span class="text-sm font-medium block">البحث المطابق</span>
                                                                            <span class="text-xs text-gray-500">دقيق 100%</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="text-xs text-gray-600 pr-8">
                                                                    مثال: "صلاة" ≠ "الصلاة"
                                                                </div>
                                                            </label>
                                                            
                                                            <!-- البحث الصرفي -->
                                                            <label class="search-type-card flex flex-col gap-2 p-3 hover:bg-purple-50 rounded-lg cursor-pointer border-2 border-transparent hover:border-purple-300 transition-all">
                                                                <div class="flex items-center gap-3">
                                                                    <input type="radio" name="searchType" value="morphological" class="text-purple-600 focus:ring-purple-500">
                                                                    <div class="flex items-center gap-2 flex-1 text-right">
                                                                        <span class="text-2xl">🌳</span>
                                                                        <div>
                                                                            <span class="text-sm font-medium block">البحث الصرفي</span>
                                                                            <span class="text-xs text-gray-500">الجذور والمشتقات</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="text-xs text-gray-600 pr-8">
                                                                    مثال: "صلى" → صلاة، يصلي، مصلى
                                                                </div>
                                                            </label>
                                                        </div>
                                                    </div>
HTML;

$content = str_replace($oldSection, $newSection, $content);

// استبدال search_mode ب search_type في JavaScript
$content = preg_replace(
    '/search_mode:\s*searchMode,/i',
    'search_type: searchType,',
    $content
);

$content = preg_replace(
    '/let\s+searchMode\s*=\s*[^;]+;/i',
    'let searchType = document.querySelector(\'input[name="searchType"]:checked\')?.value || \'flexible_match\';',
    $content
);

// إزالة proximity
$content = preg_replace(
    '/proximity:\s*[^,}]+,?/i',
    '',
    $content
);

// حفظ الملف
file_put_contents($filePath, $content);

echo "✅ تم تحديث ملف Blade بنجاح!\n";
echo "📄 النسخة الاحتياطية: " . $filePath . ".backup\n";
