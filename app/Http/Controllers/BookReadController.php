<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Page;
use App\Models\Chapter;
use App\Models\Volume;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookReadController extends Controller
{
    /**
     * عرض صفحة قراءة الكتاب
     * 
     * @param Request $request
     * @param int $bookId معرف الكتاب
     * @param int $pageNumber رقم الصفحة (اختياري)
     * @return \Illuminate\View\View
     */
    public function show(Request $request, $bookId, $pageNumber = 1)
    {
        // جلب الكتاب مع العلاقات المطلوبة
        $book = Book::with([
            'authors' => function($query) {
                $query->orderByPivot('display_order', 'asc');
            },
            'bookSection',
            'volumes' => function($query) {
                $query->orderBy('number');
            }
        ])->findOrFail($bookId);

        // التحقق من أن الكتاب منشور ومرئي للعامة
        if ($book->status !== 'published' || $book->visibility !== 'public') {
            abort(404, 'الكتاب غير متاح للقراءة');
        }

        // جلب الصفحة المطلوبة
        $currentPage = Page::where('book_id', $bookId)
            ->where('page_number', $pageNumber)
            ->with(['chapter', 'volume'])
            ->first();

        if (!$currentPage) {
            // إذا لم توجد الصفحة، جلب أول صفحة متاحة
            $currentPage = Page::where('book_id', $bookId)
                ->orderBy('page_number')
                ->with(['chapter', 'volume'])
                ->first();
            
            if (!$currentPage) {
                abort(404, 'لا توجد صفحات متاحة لهذا الكتاب');
            }
            
            $pageNumber = $currentPage->page_number;
        }

        // جلب الفهرس الشجري للكتاب
        $tableOfContents = $this->buildTableOfContents($bookId);

        // جلب معلومات التنقل
        $navigationInfo = $this->getNavigationInfo($bookId, $pageNumber);

        // جلب إحصائيات الكتاب
        $bookStats = $this->getBookStatistics($bookId);

        return view('pages.book-read', compact(
            'book',
            'currentPage',
            'tableOfContents',
            'navigationInfo',
            'bookStats',
            'pageNumber'
        ));
    }

    /**
     * بناء الفهرس الشجري للكتاب
     * 
     * @param int $bookId
     * @return array
     */
    private function buildTableOfContents($bookId)
    {
        // جلب الأجزاء مع الفصول الرئيسية والفرعية
        $volumes = Volume::where('book_id', $bookId)
            ->with([
                'chapters' => function($query) {
                    $query->whereNull('parent_id')
                        ->orderBy('order')
                        ->with([
                            'children' => function($subQuery) {
                                $subQuery->orderBy('order')
                                    ->with('children'); // للمستويات الفرعية العميقة
                            }
                        ]);
                }
            ])
            ->orderBy('number')
            ->get();

        // إذا لم توجد أجزاء، جلب الفصول مباشرة
        if ($volumes->isEmpty()) {
            $chapters = Chapter::where('book_id', $bookId)
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
     * الحصول على معلومات التنقل
     * 
     * @param int $bookId
     * @param int $currentPageNumber
     * @return array
     */
    private function getNavigationInfo($bookId, $currentPageNumber)
    {
        // جلب الصفحة السابقة والتالية
        $previousPage = Page::where('book_id', $bookId)
            ->where('page_number', '<', $currentPageNumber)
            ->orderBy('page_number', 'desc')
            ->first();

        $nextPage = Page::where('book_id', $bookId)
            ->where('page_number', '>', $currentPageNumber)
            ->orderBy('page_number')
            ->first();

        // جلب إجمالي عدد الصفحات
        $totalPages = Page::where('book_id', $bookId)->count();

        // حساب النسبة المئوية للتقدم
        $progressPercentage = $totalPages > 0 ? round(($currentPageNumber / $totalPages) * 100, 1) : 0;

        return [
            'previous_page' => $previousPage,
            'next_page' => $nextPage,
            'total_pages' => $totalPages,
            'current_page_number' => $currentPageNumber,
            'progress_percentage' => $progressPercentage
        ];
    }

    /**
     * الحصول على إحصائيات الكتاب
     * 
     * @param int $bookId
     * @return array
     */
    private function getBookStatistics($bookId)
    {
        $stats = DB::table('pages')
            ->where('book_id', $bookId)
            ->selectRaw('
                COUNT(*) as total_pages,
                MIN(page_number) as first_page,
                MAX(page_number) as last_page
            ')
            ->first();

        $volumesCount = Volume::where('book_id', $bookId)->count();
        $chaptersCount = Chapter::where('book_id', $bookId)->count();

        return [
            'total_pages' => $stats->total_pages ?? 0,
            'first_page' => $stats->first_page ?? 1,
            'last_page' => $stats->last_page ?? 1,
            'volumes_count' => $volumesCount,
            'chapters_count' => $chaptersCount
        ];
    }

    /**
     * البحث في محتوى الكتاب
     * 
     * @param Request $request
     * @param int $bookId
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request, $bookId)
    {
        $request->validate([
            'query' => 'required|string|min:2|max:100'
        ]);

        $searchQuery = $request->get('query');
        
        $results = Page::where('book_id', $bookId)
            ->where('content', 'LIKE', '%' . $searchQuery . '%')
            ->select('page_number', 'content')
            ->orderBy('page_number')
            ->limit(20)
            ->get()
            ->map(function($page) use ($searchQuery) {
                // استخراج جزء من النص حول الكلمة المبحوث عنها
                $content = strip_tags($page->content);
                $position = mb_stripos($content, $searchQuery);
                
                if ($position !== false) {
                    $start = max(0, $position - 100);
                    $excerpt = mb_substr($content, $start, 200);
                    
                    // تمييز الكلمة المبحوث عنها
                    $excerpt = preg_replace(
                        '/(' . preg_quote($searchQuery, '/') . ')/ui',
                        '<mark>$1</mark>',
                        $excerpt
                    );
                } else {
                    $excerpt = mb_substr(strip_tags($page->content), 0, 200);
                }
                
                return [
                    'page_number' => $page->page_number,
                    'excerpt' => $excerpt . '...'
                ];
            });

        return response()->json([
            'success' => true,
            'results' => $results,
            'total' => $results->count()
        ]);
    }

    /**
     * الانتقال إلى صفحة معينة
     * 
     * @param Request $request
     * @param int $bookId
     * @param int $pageNumber
     * @return \Illuminate\Http\RedirectResponse
     */
    public function goToPage(Request $request, $bookId, $pageNumber)
    {
        // التحقق من وجود الصفحة
        $pageExists = Page::where('book_id', $bookId)
            ->where('page_number', $pageNumber)
            ->exists();

        if (!$pageExists) {
            return redirect()->route('book.read', ['bookId' => $bookId])
                ->with('error', 'الصفحة المطلوبة غير موجودة');
        }

        return redirect()->route('book.read', [
            'bookId' => $bookId,
            'pageNumber' => $pageNumber
        ]);
    }
}