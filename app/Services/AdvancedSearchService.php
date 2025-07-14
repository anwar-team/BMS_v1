<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Author;
use App\Models\BookSection;
use App\Models\Chapter;
use App\Models\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdvancedSearchService
{
    /**
     * Perform a full-text search across books, chapters, and pages
     *
     * @param string $query The search query
     * @param array $filters Additional filters to apply
     * @return array Search results grouped by type
     */
    public function search(string $query, array $filters = []): array
    {
        $results = [
            'books' => $this->searchBooks($query, $filters),
            'chapters' => $this->searchChapters($query, $filters),
            'pages' => $this->searchPages($query, $filters),
        ];

        return $results;
    }

    /**
     * Search for books matching the query
     *
     * @param string $query
     * @param array $filters
     * @return Collection
     */
    public function searchBooks(string $query, array $filters = []): Collection
    {
        $booksQuery = Book::query()
            ->where(function (Builder $builder) use ($query) {
                $builder->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            });

        $this->applyBookFilters($booksQuery, $filters);

        return $booksQuery->get();
    }

    /**
     * Search for chapters matching the query
     *
     * @param string $query
     * @param array $filters
     * @return Collection
     */
    public function searchChapters(string $query, array $filters = []): Collection
    {
        $chaptersQuery = Chapter::query()
            ->where('title', 'like', "%{$query}%");

        $this->applyChapterFilters($chaptersQuery, $filters);

        return $chaptersQuery->with('book')->get();
    }

    /**
     * Search for pages containing the query text
     *
     * @param string $query
     * @param array $filters
     * @return Collection
     */
    public function searchPages(string $query, array $filters = []): Collection
    {
        $pagesQuery = Page::query()
            ->where('content', 'like', "%{$query}%");

        $this->applyPageFilters($pagesQuery, $filters);

        return $pagesQuery->with(['book', 'chapter'])->get();
    }

    /**
     * Apply filters to the book query
     *
     * @param Builder $query
     * @param array $filters
     * @return void
     */
    protected function applyBookFilters(Builder $query, array $filters): void
    {
        // Filter by book section
        if (!empty($filters['section_id'])) {
            $query->where('book_section_id', $filters['section_id']);
        }

        // Filter by author
        if (!empty($filters['author_id'])) {
            $query->whereHas('authors', function (Builder $builder) use ($filters) {
                $builder->where('authors.id', $filters['author_id']);
            });
        }

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by visibility
        if (!empty($filters['visibility'])) {
            $query->where('visibility', $filters['visibility']);
        } else {
            // Default to public books only
            $query->where('visibility', 'public');
        }

        // Filter by published year
        if (!empty($filters['published_year'])) {
            $query->where('published_year', $filters['published_year']);
        }

        // Sort by most read
        if (!empty($filters['sort']) && $filters['sort'] === 'most_read') {
            // This would require a view_count or similar field
            // For now, we'll sort by id as a placeholder
            $query->orderBy('id', 'desc');
        }
    }

    /**
     * Apply filters to the chapter query
     *
     * @param Builder $query
     * @param array $filters
     * @return void
     */
    protected function applyChapterFilters(Builder $query, array $filters): void
    {
        // Filter by book
        if (!empty($filters['book_id'])) {
            $query->where('book_id', $filters['book_id']);
        }

        // Filter by volume
        if (!empty($filters['volume_id'])) {
            $query->where('volume_id', $filters['volume_id']);
        }

        // Filter by parent chapter (for subchapters)
        if (!empty($filters['parent_id'])) {
            $query->where('parent_id', $filters['parent_id']);
        }

        // Filter for main chapters only
        if (!empty($filters['main_only']) && $filters['main_only'] === true) {
            $query->whereNull('parent_id');
        }

        // Include only chapters from public books
        $query->whereHas('book', function (Builder $builder) use ($filters) {
            $builder->where('visibility', 'public');
            
            if (!empty($filters['book_section_id'])) {
                $builder->where('book_section_id', $filters['book_section_id']);
            }
        });
    }

    /**
     * Apply filters to the page query
     *
     * @param Builder $query
     * @param array $filters
     * @return void
     */
    protected function applyPageFilters(Builder $query, array $filters): void
    {
        // Filter by book
        if (!empty($filters['book_id'])) {
            $query->where('book_id', $filters['book_id']);
        }

        // Filter by chapter
        if (!empty($filters['chapter_id'])) {
            $query->where('chapter_id', $filters['chapter_id']);
        }

        // Filter by volume
        if (!empty($filters['volume_id'])) {
            $query->where('volume_id', $filters['volume_id']);
        }

        // Include only pages from public books
        $query->whereHas('book', function (Builder $builder) {
            $builder->where('visibility', 'public');
        });
    }

    /**
     * Perform Arabic morphological search
     * This is a placeholder for future implementation using Arabic NLP libraries
     *
     * @param string $root Arabic root word
     * @param array $filters
     * @return Collection
     */
    public function searchByArabicRoot(string $root, array $filters = []): Collection
    {
        // This would require integration with an Arabic morphological analysis library
        // For now, we'll use a simple like query as a placeholder
        return Page::query()
            ->where('content', 'like', "%{$root}%")
            ->whereHas('book', function (Builder $builder) {
                $builder->where('visibility', 'public');
            })
            ->with(['book', 'chapter'])
            ->get();
    }

    /**
     * Get popular books based on view count or other metrics
     *
     * @param int $limit
     * @return Collection
     */
    public function getMostReadBooks(int $limit = 10): Collection
    {
        // This would require a view_count or similar field
        // For now, we'll sort by id as a placeholder
        return Book::query()
            ->where('visibility', 'public')
            ->where('status', 'published')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recently added books
     *
     * @param int $limit
     * @return Collection
     */
    public function getNewBooks(int $limit = 10): Collection
    {
        return Book::query()
            ->where('visibility', 'public')
            ->where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get book categories with book counts
     *
     * @return Collection
     */
    public function getBookCategories(): Collection
    {
        return BookSection::query()
            ->withCount(['books' => function (Builder $query) {
                $query->where('visibility', 'public')
                    ->where('status', 'published');
            }])
            ->where('is_active', true)
            ->orderBy('books_count', 'desc')
            ->get();
    }
} 