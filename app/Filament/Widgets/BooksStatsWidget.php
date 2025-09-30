<?php

namespace App\Filament\Widgets;

use App\Models\Book;
use App\Models\Author;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class BooksStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    
    // Disable automatic refresh
    protected static ?string $pollingInterval = null;
    
    // Cache data for 15 minutes
    protected static string $cacheKey = 'books_stats_widget_data';
    protected static int $cacheDuration = 900; // 15 minutes

    protected function getStats(): array
    {
        // Use Cache to avoid recalculating data every time
        return Cache::remember(static::$cacheKey, static::$cacheDuration, function () {
            // Use one optimized query instead of 4 separate queries
            $bookStats = Book::selectRaw('
                COUNT(*) as total_books,
                COUNT(CASE WHEN created_at >= ? THEN 1 END) as recent_books
            ', [now()->subDays(30)])->first();
            
            $publishedBooks = Book::where('status', 'published')->count();
            $totalAuthors = Author::count();

            return [
                Stat::make('إجمالي الكتب', $bookStats->total_books)
                    ->description('جميع الكتب في النظام')
                    ->descriptionIcon('heroicon-m-book-open')
                    ->color('primary'),

                Stat::make('الكتب المنشورة', $publishedBooks)
                    ->description('الكتب المتاحة للقراء')
                    ->descriptionIcon('heroicon-m-check-circle')
                    ->color('success'),

                Stat::make('المؤلفون', $totalAuthors)
                    ->description('المؤلفون المسجلون')
                    ->descriptionIcon('heroicon-m-user-group')
                    ->color('info'),

                Stat::make('الكتب الجديدة', $bookStats->recent_books)
                    ->description('الكتب الجديدة خلال 30 يوماً')
                    ->descriptionIcon('heroicon-m-plus-circle')
                    ->color('warning'),
            ];
        });
    }
}