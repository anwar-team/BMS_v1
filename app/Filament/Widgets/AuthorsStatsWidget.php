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
                Stat::make(__('dashboard.widgets.stats.total_authors'), $authorStats->total_authors)
                    ->description(__('dashboard.widgets.stats.all_authors_system'))
                    ->descriptionIcon('heroicon-m-user-group')
                    ->color('primary'),

                Stat::make(__('dashboard.widgets.stats.active_authors'), $activeAuthors)
                    ->description(__('dashboard.widgets.stats.authors_published_books'))
                    ->descriptionIcon('heroicon-m-pencil-square')
                    ->color('success'),

                Stat::make(__('dashboard.widgets.stats.publishers'), $totalPublishers)
                    ->description(__('dashboard.widgets.stats.registered_publishers'))
                    ->descriptionIcon('heroicon-m-building-office')
                    ->color('info'),

                Stat::make(__('dashboard.widgets.stats.new_authors'), $authorStats->recent_authors)
                    ->description(__('dashboard.widgets.stats.new_authors_30_days'))
                    ->descriptionIcon('heroicon-m-user-plus')
                    ->color('warning'),
            ];
        });
    }
}