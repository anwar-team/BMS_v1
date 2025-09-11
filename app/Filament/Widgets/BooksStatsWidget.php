<?php

namespace App\Filament\Widgets;

use App\Models\Book;
use App\Models\BookSection;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class BooksStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    
    // تعطيل التحديث التلقائي لتوفير الأداء
    protected static ?string $pollingInterval = null;
    
    // Cache للبيانات لمدة 10 دقائق
    protected static string $cacheKey = 'books_stats_widget_data';
    protected static int $cacheDuration = 600; // 10 دقائق

    protected function getStats(): array
    {
        // استخدام Cache لتجنب إعادة حساب البيانات في كل مرة
        return Cache::remember(static::$cacheKey, static::$cacheDuration, function () {
            // استخدام query واحد بدلاً من 4 queries منفصلة لتحسين الأداء
            $stats = Book::selectRaw('
                COUNT(*) as total_books,
                SUM(CASE WHEN status = "published" THEN 1 ELSE 0 END) as published_books,
                SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as recent_books
            ', [now()->subDays(30)])->first();
            
            $totalSections = BookSection::count();

            return [
                Stat::make(__('resource.book.total_books'), $stats->total_books)
                    ->description(__('resource.book.total_system_books'))
                    ->descriptionIcon('heroicon-m-book-open')
                    ->color('primary'),

                Stat::make(__('resource.book.published_books'), $stats->published_books)
                    ->description(__('resource.book.available_books_for_readers'))
                    ->descriptionIcon('heroicon-m-check-circle')
                    ->color('success'),

                Stat::make(__('resource.book.book_sections'), $totalSections)
                    ->description(__('resource.book.available_sections'))
                    ->descriptionIcon('heroicon-m-folder')
                    ->color('info'),

                Stat::make(__('resource.book.new_books'), $stats->recent_books)
                    ->description(__('resource.book.books_last_30_days'))
                    ->descriptionIcon('heroicon-m-sparkles')
                    ->color('warning'),
            ];
        });
    }
}