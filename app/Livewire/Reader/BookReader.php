<?php

namespace App\Livewire\Reader;

use App\Models\Book;
use App\Models\Page;
use App\Models\Chapter;
use App\Models\Volume;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Mews\Purifier\Facades\Purifier;

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
    public int $fontPercent = 100;
    public bool $showMovements = true;
    public ?int $selectedVolume = null;
    public ?int $internalIndex = null;
    
    // Table of Contents interactive properties
    public array $expandedVolumes = [];
    public array $expandedChapters = [];
    public ?int $currentVolumeId = null;
    public ?int $currentChapterId = null;
    
    // Table of Contents search properties
    public string $tocSearch = '';
    public array $filteredTableOfContents = [];
    
    // Options menu properties
    public bool $showOptionsMenu = false;
    public bool $darkMode = false;
    
    // Mobile TOC properties
    public bool $showMobileToc = false;
    
    // Cache volumes to avoid N+1 queries
    public $volumes;

    // URL parameters for routing
    protected $queryString = [
        'pageNumber' => ['except' => 1, 'as' => 'page'],
        'search' => ['except' => '', 'as' => 'q'],
        'tocSearch' => ['except' => '', 'as' => 'toc_q'],
        'showMobileToc' => ['except' => false, 'as' => ''],
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
        
        // Initialize filtered table of contents
        $this->filteredTableOfContents = $this->tableOfContents;
        
        // Load current page and navigation
        $this->loadPage();
        
        // Load book statistics
        $this->loadBookStatistics();
        
        // Set selected volume if current page has one
        if ($this->currentPage && $this->currentPage->volume_id) {
            $this->selectedVolume = $this->currentPage->volume_id;
        }
        
        // Initialize internal index (use actual internal_index from database)
        if ($this->currentPage) {
            $this->internalIndex = $this->currentPage->internal_index ?? $this->currentPage->page_number;
        }
        
        // Initialize TOC expansion state with smart defaults
        $this->initializeTocExpansionState();
        
        // Apply initial font size
        $this->applyFontSize();
    }

    /**
     * Load book with required relationships
     * 
     * FIX #3: N+1 Query Problem - Eager load volumes
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
        
        // FIX #3: Cache volumes in property to avoid repeated queries
        // This prevents N+1 query problem in Blade
        $this->volumes = $this->book->volumes;

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
        // Load volumes with ONLY top-level chapters (parent_id = null)
        $volumes = Volume::where('book_id', $this->bookId)
            ->with([
                'topLevelChapters' => function($query) {
                    // The relationship already filters by parent_id = null
                    // Just ensure ordering
                    $query->orderBy('order');
                }
            ])
            ->orderBy('number')
            ->get();
        
        // Now load nested children for each top-level chapter recursively
        foreach ($volumes as $volume) {
            foreach ($volume->topLevelChapters as $chapter) {
                $this->loadChapterChildren($chapter);
            }
        }

        // If no volumes, load chapters directly
        if ($volumes->isEmpty()) {
            // Get only top-level chapters (parent_id = null)
            $chapters = Chapter::where('book_id', $this->bookId)
                ->whereNull('parent_id')
                ->orderBy('order')
                ->get();
            
            // Load nested children recursively for each top-level chapter
            foreach ($chapters as $chapter) {
                $this->loadChapterChildren($chapter);
            }

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
     * Recursively load children for a chapter
     * 
     * @param object $chapter
     * @return void
     */
    private function loadChapterChildren($chapter): void
    {
        // Load direct children only (not grandchildren yet)
        $chapter->load([
            'children' => function($query) {
                $query->orderBy('order');
            }
        ]);
        
        // Recursively load children for each child
        if ($chapter->children && $chapter->children->isNotEmpty()) {
            foreach ($chapter->children as $child) {
                $this->loadChapterChildren($child);
            }
        }
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

        // Apply nl2br to all content regardless of source_url to preserve line breaks
        // This ensures proper formatting for all books stored as plain text
        $content = $this->currentPage->content ?? '';
        $this->currentContent = !empty($content) ? nl2br($content) : '';
        
        // Set the internal_index value to show in the input field (use actual internal_index from database)
        $this->internalIndex = $this->currentPage->internal_index ?? $this->currentPage->page_number;

        // Load navigation info
        $this->loadNavigation();
        
        // Update selected volume
        if ($this->currentPage && $this->currentPage->volume_id) {
            $this->selectedVolume = $this->currentPage->volume_id;
        }
        
        // Update current section for TOC
        $this->updateCurrentSection();
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
     * Get safe content (XSS protected using HTML Purifier)
     *
     * FIX #1: XSS Vulnerability Protection
     * This prevents malicious JavaScript from being executed
     *
     * @return string
     */
    public function getSafeContentProperty(): string
    {
        // FIX: Use isset() for better performance (microseconds faster)
        if (!isset($this->currentContent) || trim($this->currentContent) === '') {
            return '';
        }

        // Clean content from any malicious code
        // Allows only safe HTML tags commonly used in book content
        $cleaned = Purifier::clean($this->currentContent, [
            'HTML.Allowed' => 'p,br,strong,em,u,b,i,h1,h2,h3,h4,h5,h6,ul,ol,li,blockquote,div,span,a[href|target],img[src|alt|width|height],sub,sup,table,thead,tbody,tr,td,th',
            'HTML.AllowedAttributes' => 'class,id,style,href,title,src,alt,width,height,target,colspan,rowspan',
            'CSS.AllowedProperties' => 'color,font-size,font-weight,text-align,margin,padding,text-decoration,display,width,height,border,background-color',
            'AutoFormat.RemoveEmpty' => false,
            'AutoFormat.AutoParagraph' => false,
            'Attr.AllowedFrameTargets' => ['_blank', '_self', '_parent', '_top'],
        ]);

        return $cleaned;
    }    /**
     * Get content without diacritics (cached for performance)
     * 
     * FIX #2: preg_replace Performance Optimization
     * Uses caching to avoid repeated regex operations
     * 
     * @return string
     */
    public function getContentWithoutMovementsProperty(): string
    {
        // FIX: Use isset() for better performance
        if (!isset($this->currentPage) || !isset($this->currentContent) || trim($this->currentContent) === '') {
            return '';
        }
        
        // Cache key unique to each page
        $cacheKey = "page_no_movements_{$this->currentPage->id}";
        
        // Get from cache or execute once and cache for 24 hours
        return Cache::remember($cacheKey, now()->addDay(), function() {
            // This expensive regex will only run once per page!
            return preg_replace(
                '/[\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}]/u', 
                '', 
                $this->safeContent
            );
        });
    }
    
    /**
     * Get processed content (with/without movements and XSS protected)
     * 
     * COMBINES FIX #1 and FIX #2
     * Returns safe, properly formatted content
     * 
     * @return string
     */
    public function getProcessedContentProperty(): string
    {
        if ($this->showMovements) {
            // Return safe content with diacritics
            return $this->safeContent;
        }
        
        // Return cached content without diacritics
        return $this->contentWithoutMovements;
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
     * Handle page number updates from input/slider
     * 
     * @return void
     */
    public function updatedPageNumber(): void
    {
        $this->gotoPage($this->pageNumber);
    }

    /**
     * Handle internal index updates from input field
     * 
     * @return void
     */
    public function updatedInternalIndex(): void
    {
        if ($this->internalIndex) {
            // First try to find page by internal_index
            $page = Page::where('book_id', $this->bookId)
                ->where('internal_index', $this->internalIndex)
                ->first();
            
            if ($page) {
                $this->gotoPage($page->page_number);
            } else {
                // Fallback: treat as page_number if internal_index not found
                if ($this->internalIndex >= 1 && $this->internalIndex <= $this->navigation['total_pages']) {
                    $this->gotoPage($this->internalIndex);
                }
            }
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
        $volume = Volume::where('book_id', $this->bookId)->find($volumeId);
        
        if ($volume) {
            // Find the first page of this volume
            $firstPage = Page::where('book_id', $this->bookId)
                ->where('volume_id', $volumeId)
                ->orderBy('page_number')
                ->first();
            
            if ($firstPage) {
                $this->gotoPage($firstPage->page_number);
            } else {
                // If no pages found with volume_id, try finding by chapter
                $firstChapter = Chapter::where('book_id', $this->bookId)
                    ->where('volume_id', $volumeId)
                    ->orderBy('order')
                    ->first();
                
                if ($firstChapter) {
                    $firstPageByChapter = Page::where('book_id', $this->bookId)
                        ->where('chapter_id', $firstChapter->id)
                        ->orderBy('page_number')
                        ->first();
                    
                    if ($firstPageByChapter) {
                        $this->gotoPage($firstPageByChapter->page_number);
                    }
                }
            }
        }
    }

    /**
     * Handle volume selection change
     * 
     * @param string $volumeId
     * @return void
     */
    public function changeVolume(string $volumeId): void
    {
        $this->gotoVolume((int) $volumeId);
    }

    /**
     * Handle selected volume updates
     * 
     * @return void
     */
    public function updatedSelectedVolume(): void
    {
        if ($this->selectedVolume) {
            $this->gotoVolume($this->selectedVolume);
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
        if ($this->fontPercent < 200) {
            $this->fontPercent += 10;
            $this->applyFontSize();
        }
    }

    /**
     * Decrease font size
     * 
     * @return void
     */
    public function decreaseFontSize(): void
    {
        if ($this->fontPercent > 50) {
            $this->fontPercent -= 10;
            $this->applyFontSize();
        }
    }

    /**
     * Apply the new font size to the content dynamically
     * 
     * @return void
     */
    public function applyFontSize(): void
    {
        // Apply the new font size to the content dynamically
        $this->dispatch('fontSizeChanged', $this->fontPercent);
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
     * Toggle volume expansion in TOC
     * 
     * @param int $volumeId
     * @return void
     */
    public function toggleVolume(int $volumeId): void
    {
        if (in_array($volumeId, $this->expandedVolumes)) {
            $this->expandedVolumes = array_diff($this->expandedVolumes, [$volumeId]);
        } else {
            $this->expandedVolumes[] = $volumeId;
        }
    }

    /**
     * Toggle chapter expansion in TOC
     * 
     * @param int $chapterId
     * @return void
     */
    public function toggleChapter(int $chapterId): void
    {
        if (in_array($chapterId, $this->expandedChapters)) {
            $this->expandedChapters = array_diff($this->expandedChapters, [$chapterId]);
        } else {
            $this->expandedChapters[] = $chapterId;
        }
    }

    /**
     * Update current section based on current page
     * 
     * @return void
     */
    public function updateCurrentSection(): void
    {
        if ($this->currentPage) {
            // Update current volume and chapter
            $this->currentVolumeId = $this->currentPage->volume_id;
            $this->currentChapterId = $this->currentPage->chapter_id;
            
            // Auto-expand current volume and chapter
            if ($this->currentVolumeId && !in_array($this->currentVolumeId, $this->expandedVolumes)) {
                $this->expandedVolumes[] = $this->currentVolumeId;
            }
            
            if ($this->currentChapterId && !in_array($this->currentChapterId, $this->expandedChapters)) {
                $this->expandedChapters[] = $this->currentChapterId;
                
                // Also expand parent chapters
                $this->expandParentChapters($this->currentChapterId);
            }
        }
    }

    /**
     * Expand parent chapters recursively
     * 
     * @param int $chapterId
     * @return void
     */
    private function expandParentChapters(int $chapterId): void
    {
        $chapter = Chapter::find($chapterId);
        if ($chapter && $chapter->parent_id) {
            if (!in_array($chapter->parent_id, $this->expandedChapters)) {
                $this->expandedChapters[] = $chapter->parent_id;
            }
            $this->expandParentChapters($chapter->parent_id);
        }
    }

    /**
     * Filter table of contents based on search
     * 
     * @return void
     */
    public function filterTableOfContents(): void
    {
        if (empty(trim($this->tocSearch))) {
            $this->filteredTableOfContents = $this->tableOfContents;
            return;
        }

        $searchQuery = trim($this->tocSearch);
        $filtered = $this->tableOfContents;
        
        // Arrays to track which volumes/chapters should be expanded
        $volumesToExpand = [];
        $chaptersToExpand = [];
        
        if ($filtered['type'] === 'volumes_with_chapters') {
            $filteredVolumes = collect($filtered['data'])->filter(function($volume) use ($searchQuery, &$volumesToExpand, &$chaptersToExpand) {
                // Check if volume title matches
                $volumeMatches = stripos($volume->title ?: 'الجزء ' . $volume->number, $searchQuery) !== false;
                
                // Check if any chapter matches and collect matching chapters
                $matchingChapters = $volume->chapters->filter(function($chapter) use ($searchQuery, &$chaptersToExpand) {
                    $matches = $this->chapterMatchesSearch($chapter, $searchQuery, $chaptersToExpand);
                    return $matches;
                });
                
                $chapterMatches = $matchingChapters->isNotEmpty();
                
                // If volume or any chapter matches, expand this volume
                if ($volumeMatches || $chapterMatches) {
                    $volumesToExpand[] = $volume->id;
                }
                
                return $volumeMatches || $chapterMatches;
            });
            
            $filtered['data'] = $filteredVolumes;
        } else {
            $filteredChapters = collect($filtered['data'])->filter(function($chapter) use ($searchQuery, &$chaptersToExpand) {
                return $this->chapterMatchesSearch($chapter, $searchQuery, $chaptersToExpand);
            });
            
            $filtered['data'] = $filteredChapters;
        }
        
        // Auto-expand volumes and chapters that contain search results
        $this->expandSearchResults($volumesToExpand, $chaptersToExpand);
        
        $this->filteredTableOfContents = $filtered;
    }
    
    /**
     * Check if chapter matches search recursively
     * 
     * @param object $chapter
     * @param string $searchQuery
     * @param array &$chaptersToExpand
     * @return bool
     */
    private function chapterMatchesSearch($chapter, string $searchQuery, array &$chaptersToExpand = []): bool
    {
        $matches = false;
        
        // Check chapter title
        if (stripos($chapter->title, $searchQuery) !== false) {
            $matches = true;
            $chaptersToExpand[] = $chapter->id;
        }
        
        // Check children recursively
        if ($chapter->children && $chapter->children->isNotEmpty()) {
            $childMatches = $chapter->children->filter(function($child) use ($searchQuery, &$chaptersToExpand) {
                return $this->chapterMatchesSearch($child, $searchQuery, $chaptersToExpand);
            })->isNotEmpty();
            
            // If any child matches, expand this parent chapter too
            if ($childMatches) {
                $matches = true;
                if (!in_array($chapter->id, $chaptersToExpand)) {
                    $chaptersToExpand[] = $chapter->id;
                }
            }
        }
        
        return $matches;
    }
    
    /**
     * Auto-expand volumes and chapters that contain search results
     * 
     * @param array $volumesToExpand
     * @param array $chaptersToExpand
     * @return void
     */
    private function expandSearchResults(array $volumesToExpand, array $chaptersToExpand): void
    {
        // Expand volumes that contain search results
        foreach ($volumesToExpand as $volumeId) {
            if (!in_array($volumeId, $this->expandedVolumes)) {
                $this->expandedVolumes[] = $volumeId;
            }
        }
        
        // Expand chapters that contain search results
        foreach ($chaptersToExpand as $chapterId) {
            if (!in_array($chapterId, $this->expandedChapters)) {
                $this->expandedChapters[] = $chapterId;
            }
        }
    }
    
    /**
     * Handle TOC search input changes
     * 
     * @return void
     */
    public function updatedTocSearch(): void
    {
        $this->filterTableOfContents();
    }
    
    /**
     * Clear TOC search
     * 
     * @return void
     */
    public function clearTocSearch(): void
    {
        $this->tocSearch = '';
        $this->filteredTableOfContents = $this->tableOfContents;
        
        // Reset expansion state to smart defaults when clearing search
        $this->initializeTocExpansionState();
    }
    
    /**
     * Toggle options menu
     * 
     * @return void
     */
    public function toggleOptionsMenu(): void
    {
        $this->showOptionsMenu = !$this->showOptionsMenu;
    }
    
    /**
     * Toggle dark mode
     * 
     * @return void
     */
    public function toggleDarkMode(): void
    {
        $this->darkMode = !$this->darkMode;
        $this->dispatch('darkModeToggled', $this->darkMode);
    }
    
    /**
     * Reset font size to default
     * 
     * @return void
     */
    public function resetFontSize(): void
    {
        $this->fontPercent = 100;
        $this->applyFontSize();
    }
    
    /**
     * Toggle mobile TOC visibility
     * 
     * @return void
     */
    public function toggleMobileToc(): void
    {
        $this->showMobileToc = !$this->showMobileToc;
    }
    
    /**
     * Close mobile TOC
     * 
     * @return void
     */
    public function closeMobileToc(): void
    {
        $this->showMobileToc = false;
    }

    /**
     * Initialize TOC expansion state with smart defaults
     * 
     * @return void
     */
    private function initializeTocExpansionState(): void
    {
        // Initialize arrays
        $this->expandedVolumes = [];
        $this->expandedChapters = [];
        
        // If we have table of contents data
        if (!empty($this->tableOfContents)) {
            // For books with volumes, auto-expand the first volume and current volume
            if ($this->tableOfContents['type'] === 'volumes_with_chapters' && !empty($this->tableOfContents['data'])) {
                $volumes = collect($this->tableOfContents['data']);
                
                // Always expand the first volume for better UX
                $firstVolume = $volumes->first();
                if ($firstVolume) {
                    $this->expandedVolumes[] = $firstVolume->id;
                }
                
                // If current page has a volume, expand it too (if different from first)
                if ($this->currentPage && $this->currentPage->volume_id) {
                    $this->currentVolumeId = $this->currentPage->volume_id;
                    if (!in_array($this->currentVolumeId, $this->expandedVolumes)) {
                        $this->expandedVolumes[] = $this->currentVolumeId;
                    }
                }
            }
            
            // Auto-expand current chapter and its parents
            if ($this->currentPage && $this->currentPage->chapter_id) {
                $this->currentChapterId = $this->currentPage->chapter_id;
                $this->expandedChapters[] = $this->currentChapterId;
                
                // Expand parent chapters recursively
                $this->expandParentChapters($this->currentChapterId);
            }
        }
        
        // For books without volumes but with chapters, expand first level chapters
        if ($this->tableOfContents['type'] === 'chapters_only' && !empty($this->tableOfContents['data'])) {
            $chapters = collect($this->tableOfContents['data']);
            
            // Auto-expand first few main chapters for better navigation
            $firstChapters = $chapters->take(3); // Expand first 3 main chapters
            foreach ($firstChapters as $chapter) {
                $this->expandedChapters[] = $chapter->id;
            }
            
            // Always expand current chapter if exists
            if ($this->currentPage && $this->currentPage->chapter_id) {
                $this->currentChapterId = $this->currentPage->chapter_id;
                if (!in_array($this->currentChapterId, $this->expandedChapters)) {
                    $this->expandedChapters[] = $this->currentChapterId;
                }
                
                // Expand parent chapters recursively
                $this->expandParentChapters($this->currentChapterId);
            }
        }
    }

    /**
     * Highlight search terms in text
     * 
     * @param string $text
     * @param string $searchQuery
     * @return string
     */
    public function highlightSearchTerm(string $text, string $searchQuery = null): string
    {
        if (empty($searchQuery) || empty(trim($this->tocSearch))) {
            return $text;
        }
        
        $searchTerm = trim($this->tocSearch);
        if (empty($searchTerm)) {
            return $text;
        }
        
        // Use case-insensitive search and highlight
        $pattern = '/(' . preg_quote($searchTerm, '/') . ')/ui';
        return preg_replace($pattern, '<span class="toc-search-highlight">$1</span>', $text);
    }
    
    /**
     * Check if chapter matches current search
     * 
     * @param object $chapter
     * @return bool
     */
    public function chapterMatchesCurrentSearch($chapter): bool
    {
        if (empty(trim($this->tocSearch))) {
            return false;
        }
        
        return stripos($chapter->title, trim($this->tocSearch)) !== false;
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