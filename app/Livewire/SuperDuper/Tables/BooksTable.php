<?php

namespace App\Livewire\SuperDuper\Tables;

use App\Models\Book;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * مكون Livewire لعرض جدول الكتب
 * 
 * الميزات:
 * - عرض الكتب مع الترقيم
 * - البحث في العناوين والأوصاف
 * - التصفية حسب الحالة والناشر
 * - الترتيب حسب التاريخ والعنوان
 * - الحفاظ على التصميم الأصلي
 * 
 * @package App\Livewire\SuperDuper\Tables
 */
class BooksTable extends Component
{
    use WithPagination;

    // إعدادات الترقيم
    protected $paginationTheme = 'tailwind';
    
    // خصائص البحث والتصفية
    public $search = '';
    public $statusFilter = 'published';
    public $publisherFilter = null;
    public $perPage = 15;
    
    // خصائص الترتيب
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    
    // خصائص إضافية
    public $publishers = [];
    public $isLoading = false;
    
    // Query String للمشاركة والتنقل
    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'published', 'as' => 'status'],
        'publisherFilter' => ['except' => null, 'as' => 'publisher'],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];
    
    /**
     * تهيئة المكون
     */
    public function mount()
    {
        // جلب قائمة الناشرين للتصفية
        $this->publishers = cache()->remember('publishers_list', now()->addHours(2), function () {
            return \App\Models\Publisher::select(['id', 'name'])
                ->whereHas('books', function($query) {
                    $query->where('status', 'published')
                          ->where('visibility', 'public');
                })
                ->orderBy('name')
                ->get();
        });
    }
    
    /**
     * إعادة تعيين الترقيم عند تحديث البحث
     */
    public function updatingSearch()
    {
        $this->resetPage();
        $this->isLoading = true;
    }
    
    /**
     * إعادة تعيين الترقيم عند تحديث التصفية
     */
    public function updatingStatusFilter()
    {
        $this->resetPage();
    }
    
    /**
     * إعادة تعيين الترقيم عند تحديث تصفية الناشر
     */
    public function updatingPublisherFilter()
    {
        $this->resetPage();
    }
    
    /**
     * تطبيق الترتيب
     * 
     * @param string $field حقل الترتيب
     */
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            // عكس اتجاه الترتيب إذا كان نفس الحقل
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            // تعيين حقل جديد مع الترتيب التنازلي كافتراضي
            $this->sortField = $field;
            $this->sortDirection = 'desc';
        }
        
        $this->resetPage();
    }
    
    /**
     * مسح جميع المرشحات
     */
    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = 'published';
        $this->publisherFilter = null;
        $this->sortField = 'created_at';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }
    
    /**
     * جلب الكتب مع التصفية والترتيب
     * 
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getBooksProperty()
    {
        $query = Book::with(['authors', 'mainAuthors', 'publisher'])
            ->where('visibility', 'public');
        
        // تطبيق تصفية الحالة
        if ($this->statusFilter && $this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }
        
        // تطبيق تصفية الناشر
        if ($this->publisherFilter) {
            $query->where('publisher_id', $this->publisherFilter);
        }
        
        // تطبيق البحث
        if ($this->search) {
            $query->where(function($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhereHas('authors', function($authorQuery) {
                      $authorQuery->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }
        
        // تطبيق الترتيب
        $query->orderBy($this->sortField, $this->sortDirection);
        
        return $query->paginate($this->perPage);
    }
    
    /**
     * Handle the incoming request (for route usage)
     * 
     * @return \Illuminate\View\View
     */
    public function __invoke()
    {
        return $this->render();
    }
    
    /**
     * عرض المكون
     * 
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.superduper.tables.books-table', [
            'books' => $this->books,
        ]);
    }
}