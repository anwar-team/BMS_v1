<?php

namespace App\Livewire;

use App\Models\Author;
use App\Models\BookSection;
use App\Services\AdvancedSearchService;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

class AdvancedSearch extends Component
{
    use WithPagination;
    
    #[Url]
    public string $query = '';
    
    #[Url]
    public ?int $sectionId = null;
    
    #[Url]
    public ?int $authorId = null;
    
    #[Url]
    public ?string $searchType = 'all';
    
    #[Url]
    public ?string $sort = 'relevance';
    
    public $results = [];
    public $totalResults = 0;
    
    protected $queryString = [
        'query' => ['except' => ''],
        'sectionId' => ['except' => null],
        'authorId' => ['except' => null],
        'searchType' => ['except' => 'all'],
        'sort' => ['except' => 'relevance'],
    ];
    
    public function mount()
    {
        if (!empty($this->query)) {
            $this->search();
        }
    }
    
    public function updatedQuery()
    {
        $this->resetPage();
    }
    
    public function updatedSectionId()
    {
        $this->resetPage();
        if (!empty($this->query)) {
            $this->search();
        }
    }
    
    public function updatedAuthorId()
    {
        $this->resetPage();
        if (!empty($this->query)) {
            $this->search();
        }
    }
    
    public function updatedSearchType()
    {
        $this->resetPage();
        if (!empty($this->query)) {
            $this->search();
        }
    }
    
    public function updatedSort()
    {
        $this->resetPage();
        if (!empty($this->query)) {
            $this->search();
        }
    }
    
    public function search()
    {
        if (empty($this->query)) {
            $this->results = [];
            $this->totalResults = 0;
            return;
        }
        
        $filters = [
            'section_id' => $this->sectionId,
            'author_id' => $this->authorId,
            'sort' => $this->sort,
        ];
        
        $searchService = new AdvancedSearchService();
        
        if ($this->searchType === 'all' || $this->searchType === 'books') {
            $this->results['books'] = $searchService->searchBooks($this->query, $filters);
        }
        
        if ($this->searchType === 'all' || $this->searchType === 'chapters') {
            $this->results['chapters'] = $searchService->searchChapters($this->query, $filters);
        }
        
        if ($this->searchType === 'all' || $this->searchType === 'pages') {
            $this->results['pages'] = $searchService->searchPages($this->query, $filters);
        }
        
        // Calculate total results
        $this->totalResults = 0;
        foreach ($this->results as $type => $items) {
            $this->totalResults += count($items);
        }
    }
    
    public function render()
    {
        $sections = BookSection::where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();
            
        $authors = Author::orderBy('fname')
            ->limit(50)
            ->get();
            
        return view('livewire.advanced-search', [
            'sections' => $sections,
            'authors' => $authors,
            'results' => $this->results,
            'totalResults' => $this->totalResults,
        ]);
    }
} 