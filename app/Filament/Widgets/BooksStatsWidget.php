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
                Stat::make('إجمالي الكتب', $stats->total_books)
                    ->description('العدد الكلي للكتب في النظام')
                    ->descriptionIcon('heroicon-m-book-open')
                    ->color('primary'),

                Stat::make('الكتب المنشورة', $stats->published_books)
                    ->description('الكتب المتاحة للقراء')
                    ->descriptionIcon('heroicon-m-check-circle')
                    ->color('success'),

                Stat::make('أقسام الكتب', $totalSections)
                    ->description('عدد الأقسام المتاحة')
                    ->descriptionIcon('heroicon-m-folder')
                    ->color('info'),

                Stat::make('كتب جديدة', $stats->recent_books)
                    ->description('خلال آخر 30 يوم')
                    ->descriptionIcon('heroicon-m-sparkles')
                    ->color('warning'),
            ];
        });
    }
}