<?php

namespace App\Livewire;

use App\Models\Book;
use App\Models\Author;
use App\Models\BookSection;
use App\Models\Banner\Content as Banner;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;

class HomePage extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    /**
     * عرض الصفحة الرئيسية لنظام إدارة الكتب
     * 
     * هذا الـ Component يقوم بجلب البيانات التالية:
     * 1. أقسام الكتب: 6 أقسام للعرض الرئيسي
     * 2. آخر الكتب: 10 كتب مع مؤلفيها وأقسامها
     * 3. المؤلفين: 10 مؤلفين مع عدد كتبهم المنشورة
     * 
     * @return \Illuminate\View\View
     */
    public function render()
    {
        /**
         * 1. جلب بانرز الصفحة الرئيسية
         * - استخدام العلاقة مع الـ category للحصول على home-banner فقط
         * - تحميل الـ media مسبقاً لتجنب N+1 queries
         * - ترتيب حسب الـ sort وأخذ 5 بانرز كحد أقصى
         */
        $banners = Banner::whereHas('category', function($query) {
            $query->where('slug', 'home-banner');
        })
        ->active()
        ->orderBy('sort')
        ->with(['media'])
        ->take(5)
        ->get();

        /**
         * 2. جلب أقسام الكتب للعرض الرئيسي
         * - استخدام scope مخصص في Model للحصول على 6 أقسام فقط
         * - تحسين الأداء بعدم جلب جميع الأقسام
         */
        $sections = BookSection::getForHomepage(6);
        
        /**
         * 3. جلب أحدث الكتب المنشورة والمرئية للعامة
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
            ->paginate(10, ['*'], 'books_page');
        
        /**
         * 4. جلب المؤلفين مع عدد كتبهم المنشورة
         * 
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
            ->paginate(10, ['*'], 'authors_page');
        
        // تسجيل عدد المؤلفين لمراقبة الأداء والتأكد من عمل الاستعلام
        Log::info('Authors count: ' . $authors->total());

        /**
         * 5. إرجاع البيانات إلى الـ View
         * - العرض: livewire.home-page
         * - البيانات المرسلة: $banners, $sections, $books, $authors
         * - كل متغير يحتوي على collection مع pagination للكتب والمؤلفين
         */
        return view('livewire.home-page', compact('banners', 'sections', 'books', 'authors'));
    }

    /**
     * Reset pagination when component is updated
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * دوال التنقل المخصصة للكتب والمؤلفين
     */
    public function previousPage($pageName = 'page')
    {
        $this->setPage(max(1, $this->getPage($pageName) - 1), $pageName);
    }

    public function nextPage($pageName = 'page')
    {
        $this->setPage($this->getPage($pageName) + 1, $pageName);
    }

    /**
     * Re-initialize Swiper after banners data changes
     */
    public function refreshBanners()
    {
        $this->dispatch('init-swiper');
    }
}