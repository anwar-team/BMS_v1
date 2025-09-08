<?php

namespace App\Livewire;

use App\Models\Book;
use App\Models\Author;
use App\Models\BookSection;
use Livewire\Component;
use Livewire\WithPagination;

class ShowAllPage extends Component
{
    use WithPagination;

    // خصائص المكون
    public $type = 'books'; // books أو authors
    public $sectionSlug = null; // slug القسم للتصفية
    public $perPage = 50; // عدد العناصر في الصفحة
    public $search = ''; // نص البحث
    
    // خصائص محسوبة
    public $title = 'جميع الكتب';
    public $currentSection = null;

    protected $queryString = [
        'type' => ['except' => 'books'],
        'sectionSlug' => ['except' => ''],
        'perPage' => ['except' => 50],
        'search' => ['except' => ''],
        'page' => ['except' => 1]
    ];

    /**
     * تهيئة المكون
     */
    public function mount($type = 'books', $section = null)
    {
        // التحقق من صحة نوع العرض
        if (!in_array($type, ['books', 'authors'])) {
            abort(404, 'نوع العرض غير صحيح');
        }
        
        $this->type = $type;
        $this->sectionSlug = $section;
        
        // التحقق من معاملات البحث من الـ URL
        if (request()->has('q')) {
            $this->search = request()->get('q', '');
        }
        
        // التحقق من صحة عدد العناصر في الصفحة
        $this->perPage = in_array($this->perPage, [25, 50, 100]) ? $this->perPage : 50;
        
        $this->updateTitle();
    }

    /**
     * تحديث العنوان والقسم الحالي
     */
    public function updateTitle()
    {
        if ($this->type === 'books') {
            if ($this->sectionSlug) {
                $section = BookSection::findBySlug($this->sectionSlug);
                if ($section) {
                    $this->currentSection = $section;
                    $this->title = "كتب قسم: {$section->name}";
                } else {
                    abort(404, 'القسم غير موجود');
                }
            } else {
                $this->title = 'جميع الكتب';
                $this->currentSection = null;
            }
        } else {
            $this->title = 'جميع المؤلفين';
        }
    }

    /**
     * تحديث نوع العرض
     */
    public function updatedType()
    {
        $this->resetPage();
        $this->search = '';
        $this->sectionSlug = null;
        $this->updateTitle();
    }

    /**
     * تحديث القسم
     */
    public function updatedSectionSlug()
    {
        $this->resetPage();
        $this->updateTitle();
    }

    /**
     * تحديث البحث
     */
    public function updatedSearch()
    {
        $this->resetPage();
    }

    /**
     * تحديث عدد العناصر في الصفحة
     */
    public function updatedPerPage()
    {
        $this->resetPage();
    }

    /**
     * مسح الفلاتر
     */
    public function clearFilters()
    {
        $this->search = '';
        $this->sectionSlug = null;
        $this->resetPage();
        $this->updateTitle();
    }

    /**
     * جلب بيانات الكتب
     */
    private function getBooksData()
    {
        // بناء الاستعلام الأساسي للكتب مع تحميل العلاقات لتجنب N+1 queries
        $query = Book::with(['authors', 'bookSection'])
                    ->where('status', 'published')
                    ->where('visibility', 'public');
        
        // تصفية حسب القسم إذا تم تحديده
        if ($this->sectionSlug && $this->currentSection) {
            $query->where('book_section_id', $this->currentSection->id);
        }

        // البحث المحسن في عدة حقول إذا تم إدخال نص بحث
        if (!empty($this->search)) {
            $searchTerm = trim($this->search);
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('description', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhereHas('authors', function($authorQuery) use ($searchTerm) {
                      $authorQuery->where('full_name', 'LIKE', '%' . $searchTerm . '%');
                  })
                  ->orWhereHas('bookSection', function($sectionQuery) use ($searchTerm) {
                      $sectionQuery->where('name', 'LIKE', '%' . $searchTerm . '%');
                  });
            });
        }

        // ترتيب النتائج حسب التاريخ (الأحدث أولاً) وتطبيق الترقيم
        return $query->latest('created_at')
                     ->paginate($this->perPage);
    }

    /**
     * جلب بيانات المؤلفين
     */
    private function getAuthorsData()
    {
        // بناء الاستعلام للمؤلفين مع عدد الكتب المنشورة فقط
        $query = Author::withCount(['books' => function($q) {
            $q->where('status', 'published')
              ->where('visibility', 'public');
        }]);
        
        // البحث المحسن في عدة حقول للمؤلفين إذا تم إدخال نص بحث
        if (!empty($this->search)) {
            $searchTerm = trim($this->search);
            $query->where(function($q) use ($searchTerm) {
                $q->where('full_name', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('biography', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('madhhab', 'LIKE', '%' . $searchTerm . '%');
            });
        }
        
        // ترتيب المؤلفين حسب عدد الكتب (الأكثر كتباً أولاً) ثم الاسم
        return $query->orderByDesc('books_count')
                     ->orderBy('full_name')
                     ->paginate($this->perPage);
    }

    /**
     * رندر المكون
     */
    public function render()
    {
        // جلب البيانات حسب النوع
        if ($this->type === 'books') {
            $data = $this->getBooksData();
        } else {
            $data = $this->getAuthorsData();
        }

        return view('livewire.show-all-page', [
            'data' => $data,
            'type' => $this->type,
            'title' => $this->title,
            'sectionSlug' => $this->sectionSlug,
            'currentSection' => $this->currentSection,
            'perPage' => $this->perPage
        ]);
    }
}