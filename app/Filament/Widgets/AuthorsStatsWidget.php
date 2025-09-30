<?php

namespace App\Filament\Widgets;

use App\Models\Author;
use App\Models\Publisher;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class AuthorsStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    
    // Disable automatic refresh
    protected static ?string $pollingInterval = null;
    
    // Cache data for 15 minutes
    protected static string $cacheKey = 'authors_stats_widget_data';
    protected static int $cacheDuration = 900; // 15 minutes

    protected function getStats(): array
    {
        // Use Cache to avoid recalculating data every time
        return Cache::remember(static::$cacheKey, static::$cacheDuration, function () {
            // Use one optimized query instead of 4 separate queries
            $authorStats = Author::selectRaw('
                COUNT(*) as total_authors,
                COUNT(CASE WHEN created_at >= ? THEN 1 END) as recent_authors
            ', [now()->subDays(30)])->first();
            
            $activeAuthors = Author::whereHas('books')->count();
            $totalPublishers = Publisher::count();

            return [
                Stat::make('إجمالي المؤلفين', $authorStats->total_authors)
                    ->description('جميع المؤلفين في النظام')
                    ->descriptionIcon('heroicon-m-user-group')
                    ->color('primary'),

                Stat::make('المؤلفون النشطون', $activeAuthors)
                    ->description('المؤلفون الذين نشروا كتباً')
                    ->descriptionIcon('heroicon-m-pencil-square')
                    ->color('success'),

                Stat::make('الناشرون', $totalPublishers)
                    ->description('الناشرون المسجلون')
                    ->descriptionIcon('heroicon-m-building-office')
                    ->color('info'),

                Stat::make('المؤلفون الجدد', $authorStats->recent_authors)
                    ->description('المؤلفون الجدد خلال 30 يوماً')
                    ->descriptionIcon('heroicon-m-user-plus')
                    ->color('warning'),
            ];
        });
    }
}