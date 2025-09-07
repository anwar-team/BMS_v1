<?php

namespace App\Livewire\SuperDuper\Tables;

use App\Models\Author;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class AuthorsTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // خصائص البحث والتصفية
    public $search = '';
    public $sortBy = 'books_count';
    public $sortDirection = 'desc';
    public $showOnlyWithBooks = false;
    public $selectedMadhhab = '';
    public $perPage = 15;

    // خصائص للتخزين المؤقت
    public $madhhabOptions = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'sortBy' => ['except' => 'books_count'],
        'sortDirection' => ['except' => 'desc'],
        'showOnlyWithBooks' => ['except' => false],
        'selectedMadhhab' => ['except' => ''],
        'perPage' => ['except' => 15],
    ];

    public function mount()
    {
        // تحميل خيارات المذاهب من التخزين المؤقت
        $this->madhhabOptions = Cache::remember('madhhab_options', 3600, function () {
            return Author::whereNotNull('madhhab')
                ->where('madhhab', '!=', '')
                ->distinct()
                ->pluck('madhhab')
                ->sort()
                ->values()
                ->toArray();
        });
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedShowOnlyWithBooks()
    {
        $this->resetPage();
    }

    public function updatedSelectedMadhhab()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->selectedMadhhab = '';
        $this->showOnlyWithBooks = false;
        $this->sortBy = 'books_count';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    public function getAuthorsProperty()
    {
        return Author::query()
            ->withCount(['books' => function (Builder $query) {
                $query->where('status', 'published')
                      ->where('visibility', 'public');
            }])
            ->when($this->search, function (Builder $query) {
                $query->where('full_name', 'like', '%' . $this->search . '%')
                      ->orWhere('biography', 'like', '%' . $this->search . '%');
            })
            ->when($this->selectedMadhhab, function (Builder $query) {
                $query->where('madhhab', $this->selectedMadhhab);
            })
            ->when($this->showOnlyWithBooks, function (Builder $query) {
                $query->having('books_count', '>', 0);
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->when($this->sortBy !== 'full_name', function (Builder $query) {
                $query->orderBy('full_name', 'asc'); // ترتيب ثانوي
            })
            ->paginate($this->perPage);
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
        return view('livewire.superduper.tables.authors-table', [
            'authors' => $this->authors,
        ]);
    }
}