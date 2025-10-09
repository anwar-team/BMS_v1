<?php

/**
 * 🔍 فحص شامل لنظام الملاحظات والشكاوى
 * 
 * هذا السكريبت يتحقق من أن جميع مكونات النظام موجودة وتعمل بشكل صحيح
 */

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🔍 فحص نظام الملاحظات والشكاوى\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$checks = [];
$passed = 0;
$failed = 0;

// Helper function
function check($name, $condition, $success, $failure) {
    global $passed, $failed, $checks;
    
    if ($condition) {
        echo "✓ {$name}: {$success}\n";
        $passed++;
        $checks[$name] = true;
    } else {
        echo "✗ {$name}: {$failure}\n";
        $failed++;
        $checks[$name] = false;
    }
}

echo "1️⃣ فحص الملفات الأساسية\n";
echo "─────────────────────────────────────────\n";

// Check Migration
check(
    'Migration',
    file_exists(__DIR__ . '/database/migrations/2025_10_09_095500_create_feedback_complaints_table.php'),
    'موجود',
    'غير موجود!'
);

// Check Model
check(
    'Model',
    file_exists(__DIR__ . '/app/Models/FeedbackComplaint.php'),
    'موجود',
    'غير موجود!'
);

// Check Controller
check(
    'Controller',
    file_exists(__DIR__ . '/app/Http/Controllers/FeedbackComplaintController.php'),
    'موجود',
    'غير موجود!'
);

// Check Filament Resource
check(
    'Filament Resource',
    file_exists(__DIR__ . '/app/Filament/Resources/FeedbackComplaintResource.php'),
    'موجود',
    'غير موجود!'
);

// Check List Page
check(
    'List Page',
    file_exists(__DIR__ . '/app/Filament/Resources/FeedbackComplaintResource/Pages/ListFeedbackComplaints.php'),
    'موجود',
    'غير موجود!'
);

// Check Edit Page
check(
    'Edit Page',
    file_exists(__DIR__ . '/app/Filament/Resources/FeedbackComplaintResource/Pages/EditFeedbackComplaint.php'),
    'موجود',
    'غير موجود!'
);

// Check Widget
check(
    'Stats Widget',
    file_exists(__DIR__ . '/app/Filament/Resources/FeedbackComplaintResource/Widgets/FeedbackStatsOverview.php'),
    'موجود',
    'غير موجود!'
);

// Check Feedback Panel
check(
    'Feedback Panel View',
    file_exists(__DIR__ . '/resources/views/partials/feedback-panel.blade.php'),
    'موجود',
    'غير موجود!'
);

// Check Welcome Modal
check(
    'Welcome Modal View',
    file_exists(__DIR__ . '/resources/views/partials/welcome-modal.blade.php'),
    'موجود',
    'غير موجود!'
);

echo "\n2️⃣ فحص قاعدة البيانات\n";
echo "─────────────────────────────────────────\n";

try {
    require __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    // Check if table exists
    $tableExists = \Illuminate\Support\Facades\Schema::hasTable('feedback_complaints');
    check(
        'جدول feedback_complaints',
        $tableExists,
        'موجود في قاعدة البيانات',
        'غير موجود! يرجى تشغيل: php artisan migrate'
    );
    
    if ($tableExists) {
        // Check columns
        $columns = [
            'id' => 'ID',
            'type' => 'النوع',
            'subject' => 'الموضوع',
            'message' => 'الرسالة',
            'name' => 'الاسم',
            'email' => 'البريد',
            'status' => 'الحالة',
            'priority' => 'الأولوية',
            'admin_notes' => 'ملاحظات المسؤول',
            'ip_address' => 'IP Address',
            'user_agent' => 'User Agent',
        ];
        
        foreach ($columns as $col => $label) {
            check(
                "عمود {$label}",
                \Illuminate\Support\Facades\Schema::hasColumn('feedback_complaints', $col),
                'موجود',
                'غير موجود!'
            );
        }
        
        // Check data
        $count = \App\Models\FeedbackComplaint::count();
        echo "\nℹ عدد الرسائل في قاعدة البيانات: {$count}\n";
        
        if ($count > 0) {
            $pending = \App\Models\FeedbackComplaint::pending()->count();
            $inProgress = \App\Models\FeedbackComplaint::inProgress()->count();
            $resolved = \App\Models\FeedbackComplaint::resolved()->count();
            $feedbacks = \App\Models\FeedbackComplaint::feedback()->count();
            $complaints = \App\Models\FeedbackComplaint::complaint()->count();
            
            echo "  - قيد الانتظار: {$pending}\n";
            echo "  - قيد المعالجة: {$inProgress}\n";
            echo "  - محلول: {$resolved}\n";
            echo "  - ملاحظات: {$feedbacks}\n";
            echo "  - شكاوى: {$complaints}\n";
        }
    }
    
} catch (\Exception $e) {
    echo "✗ خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage() . "\n";
    $failed++;
}

echo "\n3️⃣ فحص الـ Routes\n";
echo "─────────────────────────────────────────\n";

try {
    // Check routes file
    $routesContent = file_get_contents(__DIR__ . '/routes/web.php');
    
    check(
        'Route: feedback.store',
        strpos($routesContent, "Route::post('/feedback'") !== false,
        'موجود',
        'غير موجود!'
    );
    
    check(
        'FeedbackComplaintController',
        strpos($routesContent, 'FeedbackComplaintController') !== false,
        'مُستورد',
        'غير مُستورد!'
    );
    
} catch (\Exception $e) {
    echo "✗ خطأ في فحص Routes: " . $e->getMessage() . "\n";
    $failed++;
}

echo "\n4️⃣ فحص الـ Views\n";
echo "─────────────────────────────────────────\n";

try {
    // Check app.blade.php includes
    $appLayout = file_get_contents(__DIR__ . '/resources/views/components/layouts/app.blade.php');
    
    check(
        'Include: feedback-panel',
        strpos($appLayout, "@include('partials.feedback-panel')") !== false,
        'موجود في app.blade.php',
        'غير موجود في app.blade.php!'
    );
    
    check(
        'Include: welcome-modal',
        strpos($appLayout, "@include('partials.welcome-modal')") !== false,
        'موجود في app.blade.php',
        'غير موجود في app.blade.php!'
    );
    
    check(
        'CSRF Meta Tag',
        strpos($appLayout, 'name="csrf-token"') !== false,
        'موجود',
        'غير موجود!'
    );
    
    // Check feedback-panel content
    $feedbackPanel = file_get_contents(__DIR__ . '/resources/views/partials/feedback-panel.blade.php');
    
    check(
        'Floating Button',
        strpos($feedbackPanel, 'id="feedbackFloatingButton"') !== false,
        'موجود',
        'غير موجود!'
    );
    
    check(
        'Feedback Form',
        strpos($feedbackPanel, 'id="feedbackForm"') !== false,
        'موجود',
        'غير موجود!'
    );
    
    check(
        'CSRF Token in Form',
        strpos($feedbackPanel, '@csrf') !== false,
        'موجود',
        'غير موجود!'
    );
    
    check(
        'AJAX Submit Handler',
        strpos($feedbackPanel, "addEventListener('submit'") !== false,
        'موجود',
        'غير موجود!'
    );
    
    check(
        'CSRF Header in Fetch',
        strpos($feedbackPanel, 'X-CSRF-TOKEN') !== false,
        'موجود',
        'غير موجود!'
    );
    
} catch (\Exception $e) {
    echo "✗ خطأ في فحص Views: " . $e->getMessage() . "\n";
    $failed++;
}

echo "\n5️⃣ فحص Filament Resource\n";
echo "─────────────────────────────────────────\n";

try {
    $resourceContent = file_get_contents(__DIR__ . '/app/Filament/Resources/FeedbackComplaintResource.php');
    
    check(
        'Form Definition',
        strpos($resourceContent, 'public static function form(Form $form)') !== false,
        'موجود',
        'غير موجود!'
    );
    
    check(
        'Table Definition',
        strpos($resourceContent, 'public static function table(Table $table)') !== false,
        'موجود',
        'غير موجود!'
    );
    
    check(
        'Navigation Badge',
        strpos($resourceContent, 'getNavigationBadge()') !== false,
        'موجود',
        'غير موجود!'
    );
    
    check(
        'Widgets',
        strpos($resourceContent, 'getWidgets()') !== false,
        'موجود',
        'غير موجود!'
    );
    
    // Check syntax
    exec('php -l ' . __DIR__ . '/app/Filament/Resources/FeedbackComplaintResource.php 2>&1', $output, $returnCode);
    check(
        'Syntax Check',
        $returnCode === 0,
        'لا توجد أخطاء',
        'يوجد أخطاء في الكود!'
    );
    
} catch (\Exception $e) {
    echo "✗ خطأ في فحص Filament Resource: " . $e->getMessage() . "\n";
    $failed++;
}

// Summary
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📊 ملخص النتائج\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$total = $passed + $failed;
$percentage = $total > 0 ? round(($passed / $total) * 100) : 0;

echo "✓ نجح: {$passed}\n";
echo "✗ فشل: {$failed}\n";
echo "📈 النسبة: {$percentage}%\n\n";

if ($percentage === 100) {
    echo "🎉 ممتاز! جميع الفحوصات نجحت. النظام جاهز للاستخدام.\n";
} elseif ($percentage >= 80) {
    echo "⚠ جيد! معظم الفحوصات نجحت، لكن هناك بعض المشاكل.\n";
} else {
    echo "✗ تحذير! فشل عدد كبير من الفحوصات. يرجى مراجعة الأخطاء أعلاه.\n";
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📝 روابط الاختبار:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "- صفحة الاختبار: http://localhost/test-feedback\n";
echo "- اختبار JavaScript: http://localhost/test-feedback-js.html\n";
echo "- لوحة الإدارة: http://localhost/admin/feedback-complaints\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

exit($failed > 0 ? 1 : 0);
