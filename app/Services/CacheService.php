<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Author;
use App\Models\BookSection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CacheService
{
    /**
     * Cache TTL (Time To Live) in seconds
     */
    const CACHE_TTL = [
        'authors' => 3600,          // ساعة واحدة
        'books' => 1800,            // 30 دقيقة
        'stats' => 600,             // 10 دقائق
        'trending' => 900,          // 15 دقيقة
        'sections' => 7200,         // ساعتان
        'search' => 1800,           // 30 دقيقة
    ];

    /**
     * Get all book sections with caching
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllSections()
    {
        return Cache::remember('all_sections', self::CACHE_TTL['sections'], function () {
            return BookSection::orderBy('name')->get();
        });
    }

    /**
     * Get popular authors
     * 
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPopularAuthors($limit = 20)
    {
        return Cache::remember("popular_authors_{$limit}", self::CACHE_TTL['authors'], function () use ($limit) {
            return Author::withCount('books')
                ->having('books_count', '>', 0)
                ->orderBy('books_count', 'desc')
                ->take($limit)
                ->get();
        });
    }

    /**
     * Get trending books (latest books)
     * 
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTrendingBooks($limit = 12)
    {
        return Cache::remember("trending_books_{$limit}", self::CACHE_TTL['trending'], function () use ($limit) {
            return Book::with(['authors', 'bookSection'])
                ->where('created_at', '>', now()->subDays(7))
                ->latest()
                ->take($limit)
                ->get();
        });
    }

    /**
     * Get latest books
     * 
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLatestBooks($limit = 12)
    {
        return Cache::remember("latest_books_{$limit}", self::CACHE_TTL['books'], function () use ($limit) {
            return Book::with(['authors', 'bookSection'])
                ->latest()
                ->take($limit)
                ->get();
        });
    }

    /**
     * Get random featured books
     * 
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFeaturedBooks($limit = 12)
    {
        return Cache::remember("featured_books_{$limit}", self::CACHE_TTL['books'], function () use ($limit) {
            return Book::with(['authors', 'bookSection'])
                ->inRandomOrder()
                ->take($limit)
                ->get();
        });
    }

    /**
     * Get site statistics
     * 
     * @return array
     */
    public function getSiteStats()
    {
        return Cache::remember('site_stats', self::CACHE_TTL['stats'], function () {
            return [
                'total_books' => Book::count(),
                'total_authors' => Author::count(),
                'total_sections' => BookSection::count(),
                'books_this_month' => Book::whereMonth('created_at', now()->month)->count(),
                'authors_this_month' => Author::whereMonth('created_at', now()->month)->count(),
            ];
        });
    }

    /**
     * Get book details with caching
     * 
     * @param int $id
     * @return \App\Models\Book
     */
    public function getBookDetails($id)
    {
        return Cache::remember("book_details_{$id}", self::CACHE_TTL['books'], function () use ($id) {
            return Book::with([
                'authors',
                'bookSection',
            ])->findOrFail($id);
        });
    }

    /**
     * Get author details with caching
     * 
     * @param int $id
     * @return \App\Models\Author
     */
    public function getAuthorDetails($id)
    {
        return Cache::remember("author_details_{$id}", self::CACHE_TTL['authors'], function () use ($id) {
            return Author::with(['books' => function ($query) {
                $query->latest()->take(10);
            }])->findOrFail($id);
        });
    }

    /**
     * Get books by section with caching
     * 
     * @param int $sectionId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getBooksBySection($sectionId, $limit = 20)
    {
        return Cache::remember("books_section_{$sectionId}_{$limit}", self::CACHE_TTL['books'], function () use ($sectionId, $limit) {
            return Book::where('book_section_id', $sectionId)
                ->with(['authors', 'bookSection'])
                ->latest()
                ->take($limit)
                ->get();
        });
    }

    /**
     * Get books by author with caching
     * 
     * @param int $authorId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getBooksByAuthor($authorId, $limit = 20)
    {
        return Cache::remember("books_author_{$authorId}_{$limit}", self::CACHE_TTL['books'], function () use ($authorId, $limit) {
            return Book::whereHas('authors', function ($query) use ($authorId) {
                $query->where('authors.id', $authorId);
            })
            ->with(['authors', 'bookSection'])
            ->latest()
            ->take($limit)
            ->get();
        });
    }

    /**
     * Clear book-related caches
     * 
     * @param int|null $bookId
     * @return void
     */
    public function clearBookCaches($bookId = null)
    {
        if ($bookId) {
            Cache::forget("book_details_{$bookId}");
        }

        // Clear trending and latest books
        for ($i = 10; $i <= 20; $i++) {
            Cache::forget("trending_books_{$i}");
            Cache::forget("latest_books_{$i}");
            Cache::forget("featured_books_{$i}");
        }

        // Clear stats
        Cache::forget('site_stats');
    }

    /**
     * Clear author-related caches
     * 
     * @param int|null $authorId
     * @return void
     */
    public function clearAuthorCaches($authorId = null)
    {
        if ($authorId) {
            Cache::forget("author_details_{$authorId}");
            
            // Clear author's books
            for ($i = 10; $i <= 30; $i++) {
                Cache::forget("books_author_{$authorId}_{$i}");
            }
        }

        // Clear popular authors
        for ($i = 10; $i <= 30; $i++) {
            Cache::forget("popular_authors_{$i}");
        }

        // Clear stats
        Cache::forget('site_stats');
    }

    /**
     * Clear category-related caches
     * 
     * @param int|null $sectionId
     * @return void
     */
    public function clearSectionCaches($sectionId = null)
    {
        if ($sectionId) {
            // Clear section's books
            for ($i = 10; $i <= 30; $i++) {
                Cache::forget("books_section_{$sectionId}_{$i}");
            }
        }

        Cache::forget('all_sections');
        Cache::forget('site_stats');
    }

    /**
     * Clear all application caches
     * 
     * @return void
     */
    public function clearAllCaches()
    {
        Cache::flush();
    }

    /**
     * Warm up the cache with frequently accessed data
     * 
     * @return array
     */
    public function warmCache()
    {
        $warmed = [];

        // Warm popular authors
        $this->getPopularAuthors(20);
        $warmed[] = 'popular_authors';

        // Warm trending books
        $this->getTrendingBooks(12);
        $warmed[] = 'trending_books';

        // Warm latest books
        $this->getLatestBooks(12);
        $warmed[] = 'latest_books';

        // Warm featured books
        $this->getFeaturedBooks(12);
        $warmed[] = 'featured_books';

        // Warm stats
        $this->getSiteStats();
        $warmed[] = 'site_stats';

        // Warm sections
        $this->getAllSections();
        $warmed[] = 'sections';

        return $warmed;
    }

    /**
     * Get cache statistics
     * 
     * @return array
     */
    public function getCacheStats()
    {
        $keys = [
            'all_sections',
            'popular_authors_20',
            'trending_books_12',
            'latest_books_12',
            'featured_books_12',
            'site_stats',
        ];

        $stats = [
            'total_keys' => 0,
            'cached_keys' => 0,
            'missing_keys' => 0,
            'keys' => [],
        ];

        foreach ($keys as $key) {
            $exists = Cache::has($key);
            $stats['total_keys']++;
            
            if ($exists) {
                $stats['cached_keys']++;
                $stats['keys'][$key] = 'cached';
            } else {
                $stats['missing_keys']++;
                $stats['keys'][$key] = 'missing';
            }
        }

        return $stats;
    }
}
