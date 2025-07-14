<?php

namespace App\Livewire;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\Page;
use Livewire\Component;
use Livewire\Attributes\Url;

class BookReader extends Component
{
    public Book $book;
    
    #[Url]
    public ?int $chapterId = null;
    
    #[Url]
    public ?int $pageId = null;
    
    public ?Chapter $activeChapter = null;
    public ?Page $activePage = null;
    
    public function mount($slug)
    {
        // Load the book by slug
        $this->book = Book::where('slug', $slug)->firstOrFail();
        
        // If no chapter is selected, get the first one
        if (!$this->chapterId) {
            $firstChapter = $this->book->chapters()->orderBy('order')->first();
            if ($firstChapter) {
                $this->chapterId = $firstChapter->id;
            }
        }
        
        $this->loadChapterAndPage();
    }
    
    public function loadChapterAndPage()
    {
        // Load the active chapter
        if ($this->chapterId) {
            $this->activeChapter = Chapter::where('book_id', $this->book->id)
                ->where('id', $this->chapterId)
                ->first();
                
            // If no page is selected, get the first page of the chapter
            if (!$this->pageId && $this->activeChapter) {
                $firstPage = Page::where('book_id', $this->book->id)
                    ->where('chapter_id', $this->activeChapter->id)
                    ->orderBy('page_number')
                    ->first();
                    
                if ($firstPage) {
                    $this->pageId = $firstPage->id;
                }
            }
        }
        
        // Load the active page
        if ($this->pageId) {
            $this->activePage = Page::where('book_id', $this->book->id)
                ->where('id', $this->pageId)
                ->first();
        }
    }
    
    public function selectChapter($chapterId)
    {
        $this->chapterId = $chapterId;
        $this->pageId = null;
        $this->loadChapterAndPage();
    }
    
    public function selectPage($pageId)
    {
        $this->pageId = $pageId;
        $this->loadChapterAndPage();
    }
    
    public function nextPage()
    {
        if (!$this->activePage) {
            return;
        }
        
        $nextPage = Page::where('book_id', $this->book->id)
            ->where('chapter_id', $this->activeChapter->id)
            ->where('page_number', '>', $this->activePage->page_number)
            ->orderBy('page_number')
            ->first();
            
        if ($nextPage) {
            $this->pageId = $nextPage->id;
            $this->loadChapterAndPage();
        } else {
            // Try to load the first page of the next chapter
            $nextChapter = Chapter::where('book_id', $this->book->id)
                ->where('order', '>', $this->activeChapter->order)
                ->orderBy('order')
                ->first();
                
            if ($nextChapter) {
                $this->chapterId = $nextChapter->id;
                $this->pageId = null;
                $this->loadChapterAndPage();
            }
        }
    }
    
    public function previousPage()
    {
        if (!$this->activePage) {
            return;
        }
        
        $prevPage = Page::where('book_id', $this->book->id)
            ->where('chapter_id', $this->activeChapter->id)
            ->where('page_number', '<', $this->activePage->page_number)
            ->orderBy('page_number', 'desc')
            ->first();
            
        if ($prevPage) {
            $this->pageId = $prevPage->id;
            $this->loadChapterAndPage();
        } else {
            // Try to load the last page of the previous chapter
            $prevChapter = Chapter::where('book_id', $this->book->id)
                ->where('order', '<', $this->activeChapter->order)
                ->orderBy('order', 'desc')
                ->first();
                
            if ($prevChapter) {
                $this->chapterId = $prevChapter->id;
                $lastPage = Page::where('book_id', $this->book->id)
                    ->where('chapter_id', $prevChapter->id)
                    ->orderBy('page_number', 'desc')
                    ->first();
                    
                if ($lastPage) {
                    $this->pageId = $lastPage->id;
                }
                
                $this->loadChapterAndPage();
            }
        }
    }
    
    public function render()
    {
        $chapters = $this->book->chapters()
            ->whereNull('parent_id') // Get only main chapters
            ->orderBy('order')
            ->with(['children' => function($query) {
                $query->orderBy('order');
            }])
            ->get();
            
        $mainAuthors = $this->book->mainAuthors()->get();
        
        return view('livewire.book-reader', [
            'book' => $this->book,
            'chapters' => $chapters,
            'activeChapter' => $this->activeChapter,
            'activePage' => $this->activePage,
            'mainAuthors' => $mainAuthors
        ]);
    }
}
