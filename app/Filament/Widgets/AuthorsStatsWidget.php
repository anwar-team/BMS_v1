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
    
    // تعطيل التحديث التلقائي
    protected static ?string $pollingInterval = null;
    
    // Cache للبيانات لمدة 15 دقيقة
    protected static string $cacheKey = 'authors_stats_widget_data';
    protected static int $cacheDuration = 900; // 15 دقيقة

    protected function getStats(): array
    {
        // استخدام Cache لتجنب إعادة حساب البيانات في كل مرة
        return Cache::remember(static::$cacheKey, static::$cacheDuration, function () {
            // استخدام query واحد محسن بدلاً من 4 queries منفصلة
            $authorStats = Author::selectRaw('
                COUNT(*) as total_authors,
                COUNT(CASE WHEN created_at >= ? THEN 1 END) as recent_authors
            ', [now()->subDays(30)])->first();
            
            $activeAuthors = Author::whereHas('books')->count();
            $totalPublishers = Publisher::count();

            return [
                Stat::make(__('resource.author.total_authors'), $authorStats->total_authors)
                    ->description(__('resource.author.total_system_authors'))
                    ->descriptionIcon('heroicon-m-user-group')
                    ->color('primary'),

                Stat::make(__('resource.author.active_authors'), $activeAuthors)
                    ->description(__('resource.author.authors_with_published_books'))
                    ->descriptionIcon('heroicon-m-pencil-square')
                    ->color('success'),

                Stat::make(__('resource.publisher.publishers'), $totalPublishers)
                    ->description(__('resource.publisher.registered_publishers'))
                    ->descriptionIcon('heroicon-m-building-office')
                    ->color('info'),

                Stat::make(__('resource.author.new_authors'), $authorStats->recent_authors)
                    ->description(__('resource.author.new_authors_last_30_days'))
                    ->descriptionIcon('heroicon-m-user-plus')
                    ->color('warning'),
            ];
        });
    }
}