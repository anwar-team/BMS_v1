<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use App\Models\BookSection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchAllController extends Controller
{
    /**
     * البحث في المؤلفين
     */
    public function searchAuthors(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $limit = min($request->get('limit', 20), 50); // حد أقصى 50

        if (strlen($query) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'يجب أن يكون البحث على الأقل حرفين',
                'data' => []
            ]);
        }

        // استعلام محسن مع left join لحساب عدد الكتب
        $authors = DB::table('authors')
            ->select([
                'authors.id',
                'authors.full_name',
                'authors.biography',
                'authors.birth_date',
                'authors.death_date',
                'authors.madhhab',
                DB::raw('COUNT(CASE WHEN books.status = "published" AND books.visibility = "public" THEN books.id END) as books_count')
            ])
            ->leftJoin('author_book', 'authors.id', '=', 'author_book.author_id')
            ->leftJoin('books', function($join) {
                $join->on('author_book.book_id', '=', 'books.id')
                     ->where('books.status', '=', 'published')
                     ->where('books.visibility', '=', 'public');
            })
            ->where('authors.full_name', 'LIKE', "%{$query}%")
            ->groupBy([
                'authors.id',
                'authors.full_name',
                'authors.biography',
                'authors.birth_date',
                'authors.death_date',
                'authors.madhhab'
            ])
            ->orderBy('authors.full_name')
            ->limit($limit)
            ->get()
            ->map(function ($author) {
                return [
                    'id' => $author->id,
                    'name' => $author->full_name,
                    'display_name' => $author->full_name,
                    'biography' => $author->biography ? 
                        (strlen($author->biography) > 100 ? 
                            mb_substr($author->biography, 0, 100) . '...' : 
                            $author->biography) : null,
                    'years' => $this->formatAuthorDates($author->birth_date, $author->death_date),
                    'madhhab' => $author->madhhab,
                    'books_count' => $author->books_count,
                    'type' => 'author'
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $authors,
            'total' => $authors->count(),
            'query' => $query
        ]);
    }

    /**
     * البحث في عناوين الكتب
     */
    public function searchBookTitles(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $sectionId = $request->get('section_id');
        $limit = min($request->get('limit', 20), 50);

        if (strlen($query) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'يجب أن يكون البحث على الأقل حرفين',
                'data' => []
            ]);
        }

        $booksQuery = Book::query()
            ->select([
                'books.id',
                'books.title',
                'books.description',
                'books.pages_count',
                'books.volumes_count',
                'books.book_section_id',
                'book_sections.name as section_name'
            ])
            ->join('book_sections', 'books.book_section_id', '=', 'book_sections.id')
            ->where('books.title', 'LIKE', "%{$query}%")
            ->where('books.status', 'published')
            ->where('books.visibility', 'public');

        // فلترة حسب القسم إذا تم تحديده
        if ($sectionId) {
            $booksQuery->where('books.book_section_id', $sectionId);
        }

        $books = $booksQuery
            ->with([
                'authors:id,full_name',
                'bookSection:id,name'
            ])
            ->orderBy('books.title')
            ->limit($limit)
            ->get()
            ->map(function ($book) {
                return [
                    'id' => $book->id,
                    'title' => $book->title,
                    'display_name' => $book->title,
                    'description' => $book->description ? 
                        (strlen($book->description) > 150 ? 
                            mb_substr($book->description, 0, 150) . '...' : 
                            $book->description) : null,
                    'authors' => $book->authors->pluck('full_name')->toArray(),
                    'authors_text' => $book->authors->pluck('full_name')->join('، '),
                    'section' => [
                        'id' => $book->bookSection->id,
                        'name' => $book->bookSection->name
                    ],
                    'pages_count' => $book->pages_count,
                    'volumes_count' => $book->volumes_count,
                    'type' => 'book'
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $books,
            'total' => $books->count(),
            'query' => $query,
            'section_id' => $sectionId
        ]);
    }

    /**
     * الحصول على جميع الأقسام
     */
    public function getBookSections(): JsonResponse
    {
        $sections = BookSection::query()
            ->select(['id', 'name', 'parent_id'])
            ->where('is_active', true)
            ->withCount(['books as books_count'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function ($section) {
                return [
                    'id' => $section->id,
                    'name' => $section->name,
                    'books_count' => $section->books_count,
                    'parent_id' => $section->parent_id
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $sections
        ]);
    }

    /**
     * البحث الموحد (مؤلفين + عناوين الكتب)
     */
    public function searchUnified(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $type = $request->get('type', 'books'); // authors, books, or both
        $sectionId = $request->get('section_id');
        $limit = min($request->get('limit', 15), 30);

        if (strlen($query) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'يجب أن يكون البحث على الأقل حرفين',
                'data' => []
            ]);
        }

        $results = collect();

        // البحث في المؤلفين
        if ($type === 'authors' || $type === 'both') {
            $authorsRequest = new Request(['q' => $query, 'limit' => $limit]);
            $authorsResponse = $this->searchAuthors($authorsRequest);
            $authorsData = json_decode($authorsResponse->getContent(), true);
            
            if ($authorsData['success']) {
                $results = $results->concat($authorsData['data']);
            }
        }

        // البحث في عناوين الكتب
        if ($type === 'books' || $type === 'both') {
            $booksRequest = new Request([
                'q' => $query, 
                'limit' => $limit,
                'section_id' => $sectionId
            ]);
            $booksResponse = $this->searchBookTitles($booksRequest);
            $booksData = json_decode($booksResponse->getContent(), true);
            
            if ($booksData['success']) {
                $results = $results->concat($booksData['data']);
            }
        }

        // ترتيب النتائج حسب النوع والاسم
        $sortedResults = $results->sortBy([
            ['type', 'asc'], // المؤلفين أولاً
            ['display_name', 'asc']
        ])->values();

        return response()->json([
            'success' => true,
            'data' => $sortedResults->take($limit),
            'total' => $sortedResults->count(),
            'query' => $query,
            'type' => $type,
            'section_id' => $sectionId
        ]);
    }

    /**
     * اقتراحات البحث السريع
     */
    public function searchSuggestions(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $type = $request->get('type', 'books');

        if (strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        $suggestions = collect();

        if ($type === 'authors') {
            // اقتراحات المؤلفين
            $authorSuggestions = Author::query()
                ->select(['id', 'full_name'])
                ->where('full_name', 'LIKE', "%{$query}%")
                ->limit(8)
                ->get()
                ->map(function ($author) {
                    return [
                        'id' => $author->id,
                        'text' => $author->full_name,
                        'type' => 'author'
                    ];
                });
            
            $suggestions = $suggestions->concat($authorSuggestions);
        } else {
            // اقتراحات عناوين الكتب
            $bookSuggestions = Book::query()
                ->select(['id', 'title'])
                ->where('title', 'LIKE', "%{$query}%")
                ->where('status', 'published')
                ->where('visibility', 'public')
                ->limit(8)
                ->get()
                ->map(function ($book) {
                    return [
                        'id' => $book->id,
                        'text' => $book->title,
                        'type' => 'book'
                    ];
                });
            
            $suggestions = $suggestions->concat($bookSuggestions);
        }

        return response()->json([
            'success' => true,
            'data' => $suggestions->take(10)
        ]);
    }

    /**
     * إحصائيات البحث
     */
    public function getSearchStats(): JsonResponse
    {
        $stats = [
            'total_authors' => Author::count(),
            'total_books' => Book::where('status', 'published')
                ->where('visibility', 'public')
                ->count(),
            'total_sections' => BookSection::where('is_active', true)->count(),
            'total_pages' => Book::where('status', 'published')
                ->where('visibility', 'public')
                ->sum('pages_count')
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * تنسيق تواريخ المؤلف
     */
    private function formatAuthorDates($birthDate, $deathDate): ?string
    {
        if (!$birthDate && !$deathDate) {
            return null;
        }

        $birth = $birthDate ? date('Y', strtotime($birthDate)) . 'م' : '?';
        $death = $deathDate ? date('Y', strtotime($deathDate)) . 'م' : 'معاصر';

        if ($birth === '?' && $death === 'معاصر') {
            return 'معاصر';
        }

        if ($death === 'معاصر') {
            return "ولد {$birth}";
        }

        return "({$birth} - {$death})";
    }

    /**
     * تنسيق سنوات المؤلف (للتوافق مع الكود القديم)
     */
    private function formatAuthorYears($author): ?string
    {
        if (isset($author->birth_date) && isset($author->death_date)) {
            return $this->formatAuthorDates($author->birth_date, $author->death_date);
        }

        if (isset($author->is_living) && $author->is_living) {
            return isset($author->birth_year) ? "ولد {$author->birth_year}هـ" : 'معاصر';
        }

        $birth = isset($author->birth_year) ? "{$author->birth_year}هـ" : '?';
        $death = isset($author->death_year) ? "{$author->death_year}هـ" : '?';

        if ($birth === '?' && $death === '?') {
            return null;
        }

        return "({$birth} - {$death})";
    }
}