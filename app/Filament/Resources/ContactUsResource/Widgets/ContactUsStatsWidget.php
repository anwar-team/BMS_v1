<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContactUsResource\Widgets;

use App\Models\ContactUs;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class ContactUsStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $query = ContactUs::query();

        $total = (clone $query)->count();
        $new = (clone $query)->where('status', 'new')->count();
        $responded = (clone $query)->where('status', 'responded')->count();

        $thisMonth = (clone $query)
            ->where('created_at', '>=', Carbon::now()->startOfMonth())
            ->where('created_at', '<=', Carbon::now()->endOfMonth())
            ->count();
        $lastMonth = (clone $query)
            ->where('created_at', '>=', Carbon::now()->subMonth()->startOfMonth())
            ->where('created_at', '<=', Carbon::now()->subMonth()->endOfMonth())
            ->count();
        $percentageChange = $lastMonth > 0
            ? round((($thisMonth - $lastMonth) / $lastMonth) * 100)
            : 0;
        $trend = $percentageChange >= 0 ? 'up' : 'down';

        $trendLabel = $trend === 'up' 
            ? "{$percentageChange}% زيادة مقارنة بالشهر الماضي" 
            : "{$percentageChange}% نقصان مقارنة بالشهر الماضي";

        return [
            Stat::make('إجمالي الرسائل', $total)
                ->description('جميع رسائل التواصل')
                ->descriptionIcon('heroicon-m-envelope')
                ->color('gray'),
            Stat::make('جديدة', $new)
                ->description('الرسائل الجديدة')
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('danger'),
            Stat::make('مستجابة', $responded)
                ->description('الرسائل التي تم الرد عليها')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('هذا الشهر', $thisMonth)
                ->description($trendLabel)
                ->descriptionIcon($trend === 'up' ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($trend === 'up' ? 'success' : 'danger'),
        ];
    }
}
