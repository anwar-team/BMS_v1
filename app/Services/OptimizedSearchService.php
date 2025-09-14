<?php

namespace App\Services;

use App\Models\Page;
use Illuminate\Pagination\LengthAwarePaginator;

class OptimizedSearchService
{
    /**
     * Perform fast search using Scout with minimal database queries
     *
     * @param string $query
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function search(string $query, array $filters = [], int $page = 1, int $perPage = 15): array
    {
        // استخدام البحث المحسن في Elasticsearch مع pagination سريع
        $builder = Page::search($query);
        
        // Apply filters if provided
        if (!empty($filters['author_id'])) {
            $builder->where('author_ids', $filters['author_id']);
        }
        
        if (!empty($filters['section_id'])) {
            $builder->where('book_section_id', $filters['section_id']);
        }
        
        // Get paginated results efficiently
        $results = $builder->paginate($perPage, 'page', $page);
        
        // Transform results quickly
        $transformedResults = collect($results->items())->map(function ($page) use ($query) {
            return [
                'id' => $page->id,
                'page_number' => $page->page_number,
                'content' => $this->formatContent($page->content ?? '', $query),
                'book_title' => $page->book_title ?? 'غير محدد',
                'author_name' => $page->author_name ?? 'غير محدد',
                'book_id' => $page->book_id,
                'book_section_id' => $page->book_section_id ?? null,
            ];
        });
        
        return [
            'results' => $transformedResults,
            'total' => $results->total(),
            'current_page' => $results->currentPage(),
            'per_page' => $results->perPage(),
            'last_page' => $results->lastPage(),
            'from' => $results->firstItem(),
            'to' => $results->lastItem()
        ];
    }
    
    /**
     * Format content with highlighting
     *
     * @param string $content
     * @param string $query
     * @return string
     */
    protected function formatContent(string $content, string $query): string
    {
        // تنظيف سريع للمحتوى
        $content = strip_tags($content);
        
        if (!empty($query)) {
            // البحث عن النص بطريقة سريعة
            $position = mb_stripos($content, $query);
            if ($position !== false) {
                // استخراج مقطع حول النص المطلوب
                $start = max(0, $position - 80);
                $excerpt = mb_substr($content, $start, 160);
                
                // تمييز النص بطريقة محسنة
                $highlighted = str_ireplace(
                    $query,
                    '<mark class="highlight">' . $query . '</mark>',
                    $excerpt
                );
                
                return $highlighted . '...';
            }
        }
        
        // عرض أول 150 حرف إذا لم يجد النص
        return mb_substr($content, 0, 150) . '...';
    }
    
    /**
     * Get search count quickly
     *
     * @param string $query
     * @param array $filters
     * @return int
     */
    public function searchCount(string $query, array $filters = []): int
    {
        $builder = Page::search($query);
        
        if (!empty($filters['author_id'])) {
            $builder->where('author_ids', $filters['author_id']);
        }
        
        if (!empty($filters['section_id'])) {
            $builder->where('book_section_id', $filters['section_id']);
        }
        
        return $builder->count();
    }
    
    /**
     * Get simple suggestions
     *
     * @param string $query
     * @param int $limit
     * @return array
     */
    public function getSuggestions(string $query, int $limit = 5): array
    {
        if (strlen($query) < 2) {
            return [];
        }
        
        // Get a few results to extract common terms
        $results = Page::search($query)->take($limit)->get();
        
        $suggestions = [];
        foreach ($results as $page) {
            $content = strip_tags($page->content);
            $words = explode(' ', $content);
            
            foreach ($words as $word) {
                $word = trim($word, '.,!?()[]{}');
                if (mb_strlen($word) > 3 && mb_stripos($word, $query) === 0) {
                    $suggestions[] = $word;
                    if (count($suggestions) >= $limit) break 2;
                }
            }
        }
        
        return array_unique($suggestions);
    }
}