<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Book;
use App\Models\BookSection;
use Illuminate\Database\Eloquent\Builder;

class BooksTable extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $section = null;
    public $author_id = null;
    public $publisher_id = null;
    public $showSearch = true;
    public $showFilters = true;
    public $title = 'الكتب';
    public $showPagination = true;
    public $showPerPageSelector = true;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'section' => ['except' => null],
    ];



    public function mount($section = null, $author_id = null, $publisher_id = null, $showSearch = true, $showFilters = true, $title = 'الكتب', $perPage = 10, $showPagination = true, $showPerPageSelector = true)
    {
        $this->section = $section;
        $this->author_id = $author_id;
        $this->publisher_id = $publisher_id;
        $this->showSearch = $showSearch;
        $this->showFilters = $showFilters;
        $this->title = $title;
        $this->perPage = $perPage;
        $this->showPagination = $showPagination;
        $this->showPerPageSelector = $showPerPageSelector;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function updatingSection()
    {
        $this->resetPage();
    }

    public function getBooks()
    {
        $query = Book::with(['authors', 'bookSection', 'publisher']);

        // تطبيق فلتر القسم إذا كان محدداً
        if ($this->section) {
            $sectionModel = BookSection::where('slug', $this->section)->first();
            if ($sectionModel) {
                $query->where('book_section_id', $sectionModel->id);
            }
        }

        // تطبيق فلتر المؤلف إذا كان محدداً
        if ($this->author_id) {
            $query->whereHas('authors', function (Builder $authorQuery) {
                $authorQuery->where('authors.id', $this->author_id);
            });
        }

        // تطبيق فلتر الناشر إذا كان محدداً
        if ($this->publisher_id) {
            $query->where('publisher_id', $this->publisher_id);
        }

        // تطبيق البحث
        if ($this->search) {
            $query->where(function (Builder $q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhereHas('authors', function (Builder $authorQuery) {
                      $authorQuery->where('full_name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        return $query->orderBy('created_at', 'desc')
                    ->paginate($this->perPage);
    }

    public function getCurrentSection()
    {
        if ($this->section) {
            return BookSection::where('slug', $this->section)->first();
        }
        return null;
    }

    public function highlightText($text, $search)
    {
        if (empty($search) || empty($text)) {
            return $text;
        }
        
        return preg_replace('/(' . preg_quote($search, '/') . ')/iu', 
            '<mark class="bg-yellow-200 text-yellow-900 px-1 rounded font-medium">$1</mark>', 
            $text
        );
    }

    public function render()
    {
        return view('livewire.books-table', [
            'books' => $this->getBooks(),
        ]);
    }
}