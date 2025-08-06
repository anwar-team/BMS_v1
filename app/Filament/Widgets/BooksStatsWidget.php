<?php

namespace App\Filament\Widgets;

use App\Models\Book;
use App\Models\BookSection;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BooksStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalBooks = Book::count();
        $publishedBooks = Book::where('status', 'published')->count();
        $totalSections = BookSection::count();
        $recentBooks = Book::where('created_at', '>=', now()->subDays(30))->count();

        return [
            Stat::make('إجمالي الكتب', $totalBooks)
                ->description('العدد الكلي للكتب في النظام')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('primary'),

            Stat::make('الكتب المنشورة', $publishedBooks)
                ->description('الكتب المتاحة للقراء')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('أقسام الكتب', $totalSections)
                ->description('عدد الأقسام المتاحة')
                ->descriptionIcon('heroicon-m-folder')
                ->color('info'),

            Stat::make('كتب جديدة', $recentBooks)
                ->description('خلال آخر 30 يوم')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('warning'),
        ];
    }
}