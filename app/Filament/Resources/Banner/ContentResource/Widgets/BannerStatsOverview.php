<?php

namespace App\Filament\Resources\Banner\ContentResource\Widgets;

use App\Models\Banner\Category;
use App\Models\Banner\Content;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BannerStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected static ?string $pollingInterval = '15s';

    public function getStats(): array
    {
        // Count active banners
        $activeBanners = Content::active()->count();
        $totalBanners = Content::count();
        $activePercentage = $totalBanners > 0 ? round(($activeBanners / $totalBanners) * 100) : 0;

        // Count active categories
        $activeCategories = Category::active()->count();
        $totalCategories = Category::count();

        // Get total impressions and clicks
        $totalImpressions = Content::sum('impression_count');
        $totalClicks = Content::sum('click_count');
        $ctr = $totalImpressions > 0 ? round(($totalClicks / $totalImpressions) * 100, 2) : 0;

        // Get scheduled banners
        $scheduledBanners = Content::whereNotNull('start_date')
            ->where('start_date', '>', now())
            ->count();

        // Get expiring banners (ending in next 7 days)
        $expiringBanners = Content::whereNotNull('end_date')
            ->where('end_date', '>', now())
            ->where('end_date', '<', now()->addDays(7))
            ->count();

        return [
            Stat::make('البانرات النشطة', $activeBanners)
                ->description($activePercentage . '% من إجمالي البانرات')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 4, 6, 8, 7, $activePercentage])
                ->color('success'),

            Stat::make('إجمالي الفئات', $totalCategories)
                ->description($activeCategories . ' فئة نشطة')
                ->descriptionIcon('heroicon-m-folder')
                ->color('primary'),

            Stat::make('إجمالي المشاهدات', number_format($totalImpressions))
                ->description('معدل النقر: ' . $ctr . '%')
                ->descriptionIcon('heroicon-m-cursor-arrow-rays')
                ->color($ctr > 2 ? 'success' : 'warning'),

            Stat::make('مجدولة', $scheduledBanners)
                ->description($expiringBanners . ' تنتهي قريباً')
                ->descriptionIcon('heroicon-m-calendar')
                ->color($expiringBanners > 0 ? 'warning' : 'success'),
        ];
    }
}
