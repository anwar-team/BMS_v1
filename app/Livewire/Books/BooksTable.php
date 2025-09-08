<?php

namespace App\Livewire\Books;

use App\Models\Book;
use App\Models\BookSection;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * BooksTable Component
 * 
 * Manages the books listing with filtering by category, search functionality,
 * and pagination. Supports deep-linkable URLs with query parameters.
 */
class BooksTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // Component state properties
    public ?string $categorySlug = null;    // Current category filter (slug)
    public string $search = '';             // Search query
    public int $perPage = 20;              // Items per page

    // Computed properties
    public ?BookSection $currentCategory = null;  // Resolved category object
    public string $title = 'جميع الكتب';           // Page title

    /**
     * Query string parameters for deep-linking
     * Maintains state in URL for bookmarkable links and browser navigation
     */
    protected $queryString = [
        'categorySlug' => ['except' => null, 'as' => 'category'],
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    /**
     * Component initialization
     * Resolves category from URL parameters and sets up initial state
     */
    public function mount(?string $category = null)
    {
        // Set category from route parameter or query string
        if ($category) {
            $this->categorySlug = $category;
        }

        // Resolve category object if slug provided
        $this->resolveCategory();

        // Update title based on current filter
        $this->updateTitle();
    }

    /**
     * Resolve category object from slug
     * Validates that the category exists, throws 404 if not found
     */
    private function resolveCategory(): void
    {
        if ($this->categorySlug) {
            $this->currentCategory = BookSection::where('slug', $this->categorySlug)->first();
            
            if (!$this->currentCategory) {
                abort(404, 'القسم المطلوب غير موجود');
            }
        } else {
            $this->currentCategory = null;
        }
    }

    /**
     * Update page title based on current filters
     */
    private function updateTitle(): void
    {
        if ($this->currentCategory) {
            $this->title = "كتب قسم: {$this->currentCategory->name}";
        } else {
            $this->title = 'جميع الكتب';
        }
    }

    /**
     * Build the books query with filters applied
     * Includes category filtering, search, and eager loading for performance
     */
    private function getBooksQuery()
    {
        $query = Book::with(['authors', 'bookSection'])
            ->published()
            ->public()
            ->latest();

        // Apply category filter if set
        if ($this->currentCategory) {
            $query->where('book_section_id', $this->currentCategory->id);
        }

        // Apply search filter if provided
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                  ->orWhere('description', 'like', "%{$this->search}%")
                  ->orWhereHas('authors', function ($authorQuery) {
                      $authorQuery->where('full_name', 'like', "%{$this->search}%");
                  });
            });
        }

        return $query;
    }

    /**
     * Clear all filters and reset to default state
     * Redirects to clean URL without query parameters
     */
    public function clearFilters()
    {
        $this->categorySlug = null;
        $this->search = '';
        $this->currentCategory = null;
        $this->resetPage();
        $this->updateTitle();

        // Redirect to clean URL
        return $this->redirect(route('books.index'), navigate: true);
    }

    /**
     * Handle search input changes
     * Resets pagination when search changes
     */
    public function updatedSearch()
    {
        $this->resetPage();
    }

    /**
     * Handle category filter changes
     * Resolves new category and updates title
     */
    public function updatedCategorySlug()
    {
        $this->resolveCategory();
        $this->updateTitle();
        $this->resetPage();
    }

    /**
     * Render the component
     * Returns paginated books with query string preservation
     */
    public function render()
    {
        $books = $this->getBooksQuery()
            ->paginate($this->perPage)
            ->withQueryString();

        return view('livewire.books.books-table', [
            'books' => $books,
        ]);
    }
}
