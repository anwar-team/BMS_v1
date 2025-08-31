<?php

namespace App\Livewire\Reader;

use App\Models\Book;
use App\Models\Page;
use App\Models\Chapter;
use App\Models\Volume;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class BookReader extends Component
{
    // Public properties for Livewire binding
    public int $bookId;
    public int $pageNumber = 1;
    public ?Book $book = null;
    public ?Page $currentPage = null;
    public string $currentContent = '';
    public array $navigation = [];
    public array $tableOfContents = [];
    public array $bookStats = [];
    public string $search = '';
    public array $searchResults = [];
    public bool $showSearchResults = false;
    public int $fontSize = 100;
    public bool $showMovements = false;

    // URL parameters for routing
    protected $queryString = [
        'pageNumber' => ['except' => 1, 'as' => 'page'],
        'search' => ['except' => '', 'as' => 'q'],
    ];

    /**
     * Mount the component with book ID and optional page number
     * 
     * @param int $bookId
     * @param int|null $pageNumber
     * @return void
     */
    public function mount(int $bookId, ?int $pageNumber = null): void
    {
        $this->bookId = $bookId;
        $this->pageNumber = $this->coercePage($pageNumber ?? 1);
        
        // Load book with relationships
        $this->loadBook();
        
        // Load table of contents
        $this->loadTableOfContents();
        
        // Load current page and navigation
        $this->loadPage();
        
        // Load book statistics
        $this->loadBookStatistics();
    }

    /**
     * Load book with required relationships
     * 
     * @return void
     */
    private function loadBook(): void
    {
        $this->book = Book::with([
            'authors' => function($query) {
                $query->orderByPivot('display_order', 'asc');
            },
            'bookSection',
            'volumes' => function($query) {
                $query->orderBy('number');
            }
        ])->findOrFail($this->bookId);

        // Check if book is published and visible
        if ($this->book->status !== 'published' || $this->book->visibility !== 'public') {
            abort(404, 'الكتاب غير متاح للقراءة');
        }
    }

    /**
     * Load table of contents with caching
     * 
     * @return void
     */
    private function loadTableOfContents(): void
    {
        $cacheKey = "book_toc_{$this->bookId}";
        
        $this->tableOfContents = Cache::remember($cacheKey, now()->addHours(6), function () {
            return $this->buildTableOfContents();
        });
    }

    /**
     * Build table of contents structure
     * 
     * @return array
     */
    private function buildTableOfContents(): array
    {
        // Load volumes with chapters
        $volumes = Volume::where('book_id', $this->bookId)
            ->with([
                'chapters' => function($query) {
                    $query->whereNull('parent_id')
                        ->orderBy('order')
                        ->with([
                            'children' => function($subQuery) {
                                $subQuery->orderBy('order')
                                    ->with('children');
                            }
                        ]);
                }
            ])
            ->orderBy('number')
            ->get();

        // If no volumes, load chapters directly
        if ($volumes->isEmpty()) {
            $chapters = Chapter::where('book_id', $this->bookId)
                ->whereNull('parent_id')
                ->orderBy('order')
                ->with([
                    'children' => function($query) {
                        $query->orderBy('order')
                            ->with('children');
                    }
                ])
                ->get();

            return [
                'type' => 'chapters_only',
                'data' => $chapters
            ];
        }

        return [
            'type' => 'volumes_with_chapters',
            'data' => $volumes
        ];
    }

    /**
     * Load current page and set content based on source_url
     * 
     * @return void
     */
    public function loadPage(): void
    {
        // Find current page
        $this->currentPage = Page::where('book_id', $this->bookId)
            ->where('page_number', $this->pageNumber)
            ->with(['chapter', 'volume'])
            ->first();

        if (!$this->currentPage) {
            // If page not found, get first available page
            $this->currentPage = Page::where('book_id', $this->bookId)
                ->orderBy('page_number')
                ->with(['chapter', 'volume'])
                ->first();
            
            if (!$this->currentPage) {
                abort(404, 'لا توجد صفحات متاحة لهذا الكتاب');
            }
            
            $this->pageNumber = $this->currentPage->page_number;
        }

        // Set content based on source_url existence
        // If source_url exists, use html_content, otherwise use content
        if (!empty($this->book->source_url)) {
            $this->currentContent = $this->currentPage->html_content ?? $this->currentPage->content ?? '';
        } else {
            $this->currentContent = $this->currentPage->content ?? '';
        }

        // Load navigation info
        $this->loadNavigation();
    }

    /**
     * Load navigation information
     * 
     * @return void
     */
    private function loadNavigation(): void
    {
        // Get previous and next pages
        $previousPage = Page::where('book_id', $this->bookId)
            ->where('page_number', '<', $this->pageNumber)
            ->orderBy('page_number', 'desc')
            ->first();

        $nextPage = Page::where('book_id', $this->bookId)
            ->where('page_number', '>', $this->pageNumber)
            ->orderBy('page_number')
            ->first();

        // Get total pages
        $totalPages = Page::where('book_id', $this->bookId)->count();

        // Calculate progress percentage
        $progressPercentage = $totalPages > 0 ? round(($this->pageNumber / $totalPages) * 100, 1) : 0;

        $this->navigation = [
            'previous_page' => $previousPage,
            'next_page' => $nextPage,
            'total_pages' => $totalPages,
            'current_page_number' => $this->pageNumber,
            'progress_percentage' => $progressPercentage
        ];
    }

    /**
     * Load book statistics
     * 
     * @return void
     */
    private function loadBookStatistics(): void
    {
        $stats = DB::table('pages')
            ->where('book_id', $this->bookId)
            ->selectRaw('
                COUNT(*) as total_pages,
                MIN(page_number) as first_page,
                MAX(page_number) as last_page
            ')
            ->first();

        $volumesCount = Volume::where('book_id', $this->bookId)->count();
        $chaptersCount = Chapter::where('book_id', $this->bookId)->count();

        $this->bookStats = [
            'total_pages' => $stats->total_pages ?? 0,
            'first_page' => $stats->first_page ?? 1,
            'last_page' => $stats->last_page ?? 1,
            'volumes_count' => $volumesCount,
            'chapters_count' => $chaptersCount
        ];
    }

    /**
     * Ensure page number is within valid bounds
     * 
     * @param int $pageNumber
     * @return int
     */
    private function coercePage(int $pageNumber): int
    {
        if ($pageNumber < 1) {
            return 1;
        }
        
        // We'll validate against actual pages after loading the book
        return $pageNumber;
    }

    /**
     * Navigate to a specific page
     * 
     * @param int $pageNumber
     * @return void
     */
    public function gotoPage(int $pageNumber): void
    {
        if ($pageNumber >= 1 && $pageNumber <= $this->navigation['total_pages']) {
            $this->pageNumber = $pageNumber;
            $this->loadPage();
            
            // Update URL without page reload
            $this->dispatch('url-update', [
                'url' => route('book.read', ['bookId' => $this->bookId, 'pageNumber' => $this->pageNumber])
            ]);
        }
    }

    /**
     * Navigate to previous page
     * 
     * @return void
     */
    public function previousPage(): void
    {
        if ($this->navigation['previous_page']) {
            $this->gotoPage($this->navigation['previous_page']->page_number);
        }
    }

    /**
     * Navigate to next page
     * 
     * @return void
     */
    public function nextPage(): void
    {
        if ($this->navigation['next_page']) {
            $this->gotoPage($this->navigation['next_page']->page_number);
        }
    }

    /**
     * Navigate to a specific volume's first page
     * 
     * @param int $volumeId
     * @return void
     */
    public function gotoVolume(int $volumeId): void
    {
        $volume = Volume::with('pages')->find($volumeId);
        
        if ($volume && $volume->pages->isNotEmpty()) {
            $firstPage = $volume->pages->min('page_number');
            $this->gotoPage($firstPage);
        }
    }

    /**
     * Navigate to a specific chapter's first page
     * 
     * @param int $chapterId
     * @return void
     */
    public function gotoChapter(int $chapterId): void
    {
        $chapter = Chapter::with('pages')->find($chapterId);
        
        if ($chapter && $chapter->pages->isNotEmpty()) {
            $firstPage = $chapter->pages->min('page_number');
            $this->gotoPage($firstPage);
        }
    }

    /**
     * Perform search in book content
     * 
     * @return void
     */
    public function performSearch(): void
    {
        if (empty(trim($this->search))) {
            $this->searchResults = [];
            $this->showSearchResults = false;
            return;
        }

        $searchQuery = trim($this->search);
        
        $results = Page::where('book_id', $this->bookId)
            ->where(function($query) use ($searchQuery) {
                $query->where('content', 'LIKE', '%' . $searchQuery . '%')
                      ->orWhere('html_content', 'LIKE', '%' . $searchQuery . '%');
            })
            ->select('page_number', 'content', 'html_content', 'internal_index')
            ->orderBy('page_number')
            ->limit(20)
            ->get()
            ->map(function($page) use ($searchQuery) {
                // Choose content based on source_url
                $content = !empty($this->book->source_url) 
                    ? ($page->html_content ?? $page->content ?? '')
                    : ($page->content ?? '');
                
                $content = strip_tags($content);
                $position = mb_stripos($content, $searchQuery);
                
                if ($position !== false) {
                    $start = max(0, $position - 100);
                    $excerpt = mb_substr($content, $start, 200);
                    
                    // Highlight search term
                    $excerpt = preg_replace(
                        '/(' . preg_quote($searchQuery, '/') . ')/ui',
                        '<mark>$1</mark>',
                        $excerpt
                    );
                } else {
                    $excerpt = mb_substr($content, 0, 200);
                }
                
                return [
                    'page_number' => $page->page_number,
                    'internal_index' => $page->internal_index,
                    'excerpt' => $excerpt . '...'
                ];
            });

        $this->searchResults = $results->toArray();
        $this->showSearchResults = true;
    }

    /**
     * Clear search results
     * 
     * @return void
     */
    public function clearSearch(): void
    {
        $this->search = '';
        $this->searchResults = [];
        $this->showSearchResults = false;
    }

    /**
     * Update search when search input changes
     * 
     * @return void
     */
    public function updatedSearch(): void
    {
        if (!empty(trim($this->search))) {
            $this->performSearch();
        } else {
            $this->clearSearch();
        }
    }

    /**
     * Increase font size
     * 
     * @return void
     */
    public function increaseFontSize(): void
    {
        if ($this->fontSize < 200) {
            $this->fontSize += 10;
        }
    }

    /**
     * Decrease font size
     * 
     * @return void
     */
    public function decreaseFontSize(): void
    {
        if ($this->fontSize > 50) {
            $this->fontSize -= 10;
        }
    }

    /**
     * Toggle movements display
     * 
     * @return void
     */
    public function toggleMovements(): void
    {
        $this->showMovements = !$this->showMovements;
    }

    /**
     * Render the component
     * 
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.reader.book-reader')
            ->layout('components.superduper.main');
    }
}