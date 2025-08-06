<?php

namespace App\Filament\Widgets;

use App\Models\Author;
use App\Models\Publisher;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AuthorsStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $totalAuthors = Author::count();
        $totalPublishers = Publisher::count();
        $activeAuthors = Author::whereHas('books')->count();
        $recentAuthors = Author::where('created_at', '>=', now()->subDays(30))->count();

        return [
            Stat::make('إجمالي المؤلفين', $totalAuthors)
                ->description('العدد الكلي للمؤلفين')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('المؤلفون النشطون', $activeAuthors)
                ->description('المؤلفون الذين لديهم كتب')
                ->descriptionIcon('heroicon-m-pencil-square')
                ->color('success'),

            Stat::make('دور النشر', $totalPublishers)
                ->description('عدد دور النشر المسجلة')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('info'),

            Stat::make('مؤلفون جدد', $recentAuthors)
                ->description('خلال آخر 30 يوم')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('warning'),
        ];
    }
}