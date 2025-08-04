<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Author;
use App\Models\BookSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    /**
     * عرض الصفحة الرئيسية لنظام إدارة الكتب
     * 
     * هذه الدالة تقوم بجلب البيانات التالية:
     * 1. أقسام الكتب: 6 أقسام للعرض الرئيسي
     * 2. آخر الكتب: 10 كتب مع مؤلفيها وأقسامها
     * 3. المؤلفين: 10 مؤلفين مع عدد كتبهم المنشورة
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        /**
         * 1. جلب أقسام الكتب للعرض الرئيسي
         * - استخدام scope مخصص في Model للحصول على 6 أقسام فقط
         * - تحسين الأداء بعدم جلب جميع الأقسام
         */
        $sections = BookSection::getForHomepage(6);
        
        /**
         * 2. جلب أحدث الكتب المنشورة والمرئية للعامة
         * - with(['authors', 'bookSection']): تحميل العلاقات مسبقاً لتجنب N+1 queries
         * - published(): scope للكتب المنشورة فقط (status = 'published')
         * - public(): scope للكتب المرئية للعامة (visibility = 'public')
         * - latest(): ترتيب حسب تاريخ الإنشاء (الأحدث أولاً)
         * - paginate(10): تقسيم النتائج إلى صفحات، 10 كتب لكل صفحة
         */
        $books = Book::with(['authors', 'bookSection'])
            ->published()
            ->public()
            ->latest()
            ->paginate(10);
        
        /**
         * 3. جلب المؤلفين مع عدد كتبهم المنشورة
         * - هذا القسم يحتوي على تاريخ التطوير والإصلاحات المختلفة
         */
        
        /*
        ═══════════════════════════════════════════════════════════════════
        تاريخ الكود القديم والمحاولات السابقة (محفوظ للمرجع)
        ═══════════════════════════════════════════════════════════════════
        
        الكود القديم الأول - كان يستخدم having لإظهار المؤلفين الذين لديهم كتب فقط:
        $authors = Author::withCount(['books' => function($query) {
            $query->where('status', 'published')
                  ->where('visibility', 'public');
        }])
            ->having('books_count', '>', 0)
            ->orderByDesc('books_count')
            ->paginate(10);
        
        تجربة بسيطة للاختبار - لفهم سبب عدم ظهور المؤلفين:
        $authors = Author::withCount('books')
            ->orderBy('full_name')
            ->paginate(10);
        */
        
        /**
         * الكود الصحيح المُحسن لجلب المؤلفين للصفحة الرئيسية
         * 
         * 1. withCount(['books' => function($query)]): يحسب عدد الكتب لكل مؤلف
         * 2. التصفية داخل withCount: فقط الكتب المنشورة والمرئية للعامة
         * 3. orderByDesc('books_count'): ترتيب حسب عدد الكتب (الأكثر كتباً أولاً)
         * 4. orderBy('full_name'): ترتيب ثانوي حسب الاسم أبجدياً
         * 5. paginate(10): عرض 10 مؤلفين في الصفحة الواحدة
         * 
         * ملاحظة: لا نستخدم having('books_count', '>', 0) هنا لأننا نريد عرض
         * جميع المؤلفين حتى لو لم يكن لديهم كتب منشورة (للتحضير المستقبلي)
         */
        $authors = Author::withCount(['books' => function($query) {
            $query->where('status', 'published')
                  ->where('visibility', 'public');
        }])
            ->orderByDesc('books_count')
            ->orderBy('full_name')
            ->paginate(10);
        
        // تسجيل عدد المؤلفين لمراقبة الأداء والتأكد من عمل الاستعلام
        Log::info('Authors count: ' . $authors->total());

        /**
         * 4. إرجاع البيانات إلى الـ View
         * - العرض: components.superduper.pages.home
         * - البيانات المرسلة: $sections, $books, $authors
         * - كل متغير يحتوي على collection مع pagination للكتب والمؤلفين
         */
        return view('components.superduper.pages.home', compact('sections', 'books', 'authors'));
    }
}