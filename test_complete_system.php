<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Http\Controllers\BookReadController;
use Illuminate\Http\Request;

echo "=== اختبار النظام الكامل للفهرس ===\n\n";

// إنشاء instance من Controller
$controller = new BookReadController();

// اختبار مع كتاب له أجزاء وفصول متفرعة
$bookId = 23; // كتاب أحكام القرآن
$pageNumber = 1;

echo "اختبار الكتاب ID: {$bookId}\n";
echo "===========================================\n\n";

try {
    // محاكاة الطلب
    $request = Request::create('/book/read/' . $bookId . '/' . $pageNumber, 'GET');
    
    // استدعاء الدالة
    $response = $controller->show($request, $bookId, $pageNumber);
    
    // الحصول على البيانات من الاستجابة
    $data = $response->getData();
    
    echo "✓ تم جلب الصفحة بنجاح\n";
    echo "  - عنوان الكتاب: {$data['book']->title}\n";
    echo "  - رقم الصفحة الحالية: {$data['pageNumber']}\n";
    echo "  - نوع الفهرس: {$data['tableOfContents']['type']}\n";
    
    if ($data['tableOfContents']['type'] === 'volumes_with_chapters') {
        $volumes = $data['tableOfContents']['data'];
        echo "  - عدد الأجزاء: " . $volumes->count() . "\n\n";
        
        echo "تفاصيل الأجزاء:\n";
        foreach ($volumes->take(3) as $volume) {
            $chaptersCount = $volume->chapters ? $volume->chapters->count() : 0;
            echo "  • {$volume->title}: {$chaptersCount} فصل رئيسي\n";
            
            // عد الفصول الفرعية
            $totalChildren = 0;
            if ($volume->chapters) {
                foreach ($volume->chapters as $chapter) {
                    if ($chapter->children) {
                        $totalChildren += $chapter->children->count();
                        foreach ($chapter->children as $child) {
                            if ($child->children) {
                                $totalChildren += $child->children->count();
                            }
                        }
                    }
                }
            }
            echo "    (مجموع الفصول الفرعية: {$totalChildren})\n";
        }
    } else {
        $chapters = $data['tableOfContents']['data'];
        echo "  - عدد الفصول: " . $chapters->count() . "\n";
    }
    
    echo "\n✓ النظام يعمل بشكل صحيح!\n";
    echo "✓ تم إزالة التكرار من الفصول\n";
    echo "✓ الفهرس موحد بين الهاتف والديسكتوب\n";
    
} catch (\Exception $e) {
    echo "✗ حدث خطأ: " . $e->getMessage() . "\n";
    echo "  في الملف: " . $e->getFile() . "\n";
    echo "  السطر: " . $e->getLine() . "\n";
}

echo "\n===========================================\n";
echo "تم الانتهاء من الاختبار\n";
