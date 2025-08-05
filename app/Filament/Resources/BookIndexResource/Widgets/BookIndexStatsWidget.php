<?php

namespace App\Filament\Resources\BookIndexResource\Widgets;

use App\Models\BookIndex;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class BookIndexStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $totalBookIndexes = BookIndex::count();
        $bookIndexesWithEntries = BookIndex::has('entries')->count();
        $bookIndexesThisYear = BookIndex::where('created_at', '>=', Carbon::now()->startOfYear())->count();
        $newBookIndexesThisMonth = BookIndex::where('created_at', '>=', Carbon::now()->startOfMonth())->count();

        // Calculate percentage of book indexes with entries
        $bookIndexesWithEntriesPercentage = $totalBookIndexes > 0 ? round(($bookIndexesWithEntries / $totalBookIndexes) * 100) : 0;

        return [
            Stat::make('إجمالي فهارس الكتب', $totalBookIndexes)
                ->description('جميع فهارس الكتب في النظام')
                ->descriptionIcon('heroicon-m-queue-list')
                ->color('primary'),

            Stat::make('الفهارس مع مدخلات', $bookIndexesWithEntries)
                ->description($bookIndexesWithEntriesPercentage . '% من إجمالي الفهارس')
                ->descriptionIcon('heroicon-m-list-bullet')
                ->color('success'),

            Stat::make('فهارس هذا العام', $bookIndexesThisYear)
                ->description('تمت إضافتها في عام ' . Carbon::now()->year)
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make('فهارس جديدة هذا الشهر', $newBookIndexesThisMonth)
                ->description('تمت إضافتها في ' . Carbon::now()->format('F Y'))
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('warning'),
        ];
    }
}