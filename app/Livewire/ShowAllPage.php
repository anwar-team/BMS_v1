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
        'search' => ['except' => '']
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
        
        // إعداد القسم الحالي إذا كان موجود
        if ($section) {
            $this->currentSection = BookSection::where('slug', $section)->first();
            if (!$this->currentSection) {
                abort(404, 'القسم غير موجود');
            }
        }
        
        $this->updateTitle();
    }

    private function updateTitle()
    {
        if ($this->currentSection) {
            $this->title = "كتب قسم: {$this->currentSection->name}";
        } elseif ($this->type === 'authors') {
            $this->title = 'جميع المؤلفين';
        } else {
            $this->title = 'جميع الكتب';
        }
    }

    public function getDataProperty()
    {
        if ($this->type === 'books') {
            return $this->getBooksData();
        } else {
            return $this->getAuthorsData();
        }
    }

    private function getBooksData()
    {
        $query = Book::with(['authors', 'bookSection']);

        if ($this->currentSection) {
            $query->where('book_section_id', $this->currentSection->id);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                  ->orWhere('description', 'like', "%{$this->search}%")
                  ->orWhereHas('authors', function ($authorQuery) {
                      $authorQuery->where('full_name', 'like', "%{$this->search}%");
                  });
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($this->perPage);
    }

    private function getAuthorsData()
    {
        $query = Author::withCount('books');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('full_name', 'like', "%{$this->search}%")
                  ->orWhere('biography', 'like', "%{$this->search}%")
                  ->orWhere('madhhab', 'like', "%{$this->search}%");
            });
        }

        return $query->orderBy('full_name')->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.show-all-page');
    }
}