<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Author;
use App\Models\BookSection;
use Illuminate\Http\Request;

class ShowAllController extends Controller
{
    /**
     * عرض جميع العناصر (كتب أو مؤلفين) مع إمكانية التصفية والبحث
     * 
     * هذا الـ Controller يتعامل مع صفحة عرض جميع البيانات ويدعم:
     * 1. عرض الكتب أو المؤلفين حسب المعامل type
     * 2. تصفية الكتب حسب القسم
     * 3. البحث في العناوين/الأسماء
     * 4. تحديد عدد العناصر في الصفحة (25/50/100)
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        /**
         * 1. استلام ومعالجة المعاملات من الطلب
         */
        // استلام المعاملات من الطلب
        $type = $request->get('type', 'books'); // books أو authors
        $sectionSlug = $request->get('section'); // slug القسم للتصفية
        $perPage = $request->get('per_page', 50); // عدد العناصر في الصفحة
        
        /**
         * 2. التحقق من صحة المدخلات
         */
        // التحقق من صحة نوع العرض
        if (!in_array($type, ['books', 'authors'])) {
            abort(404, 'نوع العرض غير صحيح');
        }
        
        // التحقق من صحة عدد العناصر في الصفحة
        $perPage = in_array($perPage, [25, 50, 100]) ? $perPage : 50;
        
        /**
         * 3. تحضير متغيرات البيانات والمعالجة حسب النوع
         */
        // متغيرات البيانات الأساسية
        $title = 'جميع الكتب'; // العنوان الافتراضي
        $data = [];
        $currentSection = null;
        
        switch ($type) {
            case 'books':
                // معالجة عرض الكتب مع التصفية والبحث
                $result = $this->getBooksData($request, $sectionSlug, $perPage);
                $data = $result['data'];
                $title = $result['title'];
                $currentSection = $result['section'];
                break;
                
            case 'authors':
                // معالجة عرض المؤلفين مع البحث
                $result = $this->getAuthorsData($request, $perPage);
                $data = $result['data'];
                $title = $result['title'];
                break;
        }

        /**
         * 4. إرجاع البيانات إلى الـ View
         * compact يرسل جميع المتغيرات المطلوبة للعرض
         */
        return view('components.superduper.pages.show-all', compact(
            'data', 
            'type', 
            'title', 
            'sectionSlug', 
            'currentSection',
            'perPage'
        ));
    }

    /**
     * جلب بيانات الكتب مع التصفية والبحث
     * 
     * الوظائف:
     * 1. بناء استعلام الكتب مع تحميل العلاقات
     * 2. تصفية حسب القسم إذا تم تحديده
     * 3. البحث في عناوين الكتب
     * 4. تطبيق الترقيم مع الحفاظ على معاملات البحث
     * 
     * @param Request $request
     * @param string|null $sectionSlug
     * @param int $perPage
     * @return array
     */
    private function getBooksData(Request $request, $sectionSlug, $perPage)
    {
        /**
         * 1. بناء الاستعلام الأساسي للكتب
         * - with(['authors', 'bookSection']): تحميل العلاقات مسبقاً لتجنب N+1 queries
         * - تصفية الكتب: منشورة ومرئية للعامة فقط
         */
        // بناء الاستعلام الأساسي للكتب مع تحميل العلاقات لتجنب N+1 queries
        $query = Book::with(['authors', 'bookSection'])
                    ->where('status', 'published')
                    ->where('visibility', 'public');
        
        $title = 'جميع الكتب';
        $section = null;
        
        /**
         * 2. تصفية حسب القسم إذا تم تحديده
         * - البحث عن القسم بـ slug
         * - تطبيق التصفية أو إرجاع 404 إذا لم يوجد
         */
        // تصفية حسب القسم إذا تم تحديده
        if ($sectionSlug) {
            $section = BookSection::findBySlug($sectionSlug);
            if ($section) {
                $query->where('book_section_id', $section->id);
                $title = "كتب قسم: {$section->name}";
            } else {
                abort(404, 'القسم غير موجود');
            }
        }

        /**
         * 3. تطبيق البحث في العناوين
         * - البحث الجزئي باستخدام LIKE
         * - يدعم البحث بالعربية والإنجليزية
         */
        // البحث في العناوين إذا تم إدخال نص بحث
        if ($request->filled('search')) {
            $searchTerm = $request->get('search');
            $query->where('title', 'LIKE', '%' . $searchTerm . '%');
        }

        /**
         * 4. تطبيق الترتيب والترقيم
         * - ترتيب حسب تاريخ الإنشاء (الأحدث أولاً)
         * - withQueryString(): للحفاظ على معاملات البحث في روابط التنقل
         */
        // ترتيب النتائج حسب التاريخ (الأحدث أولاً) وتطبيق الترقيم
        $data = $query->latest('created_at')
                     ->paginate($perPage)
                     ->withQueryString(); // للحفاظ على معاملات البحث في روابط التنقل
        
        return [
            'data' => $data,
            'title' => $title,
            'section' => $section
        ];
    }

    /**
     * جلب بيانات المؤلفين مع عدد الكتب والبحث
     * 
     * الوظائف:
     * 1. بناء استعلام المؤلفين مع عدد الكتب المنشورة
     * 2. البحث في أسماء المؤلفين
     * 3. ترتيب حسب عدد الكتب ثم الاسم
     * 4. إظهار جميع المؤلفين (حتى بدون كتب) - مطابق للصفحة الرئيسية
     * 
     * ملاحظة: تم إزالة شرط having('books_count', '>', 0) لتطابق النتائج مع الصفحة الرئيسية
     * 
     * @param Request $request
     * @param int $perPage
     * @return array
     */
    private function getAuthorsData(Request $request, $perPage)
    {
        /**
         * 1. بناء الاستعلام للمؤلفين مع عدد الكتب
         * - withCount(['books' => function($q)]): حساب عدد الكتب لكل مؤلف
         * - التصفية داخل withCount: فقط الكتب المنشورة والمرئية
         */
        // بناء الاستعلام للمؤلفين مع عدد الكتب المنشورة فقط
        $query = Author::withCount(['books' => function($q) {
            $q->where('status', 'published')
              ->where('visibility', 'public');
        }]);
        
        /**
         * 2. تطبيق البحث في أسماء المؤلفين
         * - البحث في حقل full_name
         * - يدعم البحث الجزئي
         */
        // البحث في أسماء المؤلفين إذا تم إدخال نص بحث
        if ($request->filled('search')) {
            $searchTerm = $request->get('search');
            $query->where('full_name', 'LIKE', '%' . $searchTerm . '%');
        }
        
        /**
         * 3. تطبيق الترتيب والترقيم
         * - إزالة having('books_count', '>', 0) لإظهار جميع المؤلفين
         * - ترتيب أول: حسب عدد الكتب (الأكثر كتباً أولاً)
         * - ترتيب ثاني: حسب الاسم أبجدياً
         * - withQueryString(): الحفاظ على معاملات البحث
         */
        // ترتيب المؤلفين حسب عدد الكتب (الأكثر كتباً أولاً) ثم الاسم
        $data = $query->orderByDesc('books_count') // إظهار جميع المؤلفين بدون شرط having
                     ->orderBy('full_name')
                     ->paginate($perPage)
                     ->withQueryString();
        
        /**
         * 4. إرجاع البيانات في array
         * - data: النتائج مع الترقيم
         * - title: عنوان الصفحة
         */
        return [
            'data' => $data,
            'title' => 'جميع المؤلفين'
        ];
    }
}
