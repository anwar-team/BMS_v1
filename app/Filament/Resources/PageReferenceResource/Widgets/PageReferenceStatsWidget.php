<?php

namespace App\Filament\Resources\PageReferenceResource\Widgets;

use App\Models\PageReference;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class PageReferenceStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $totalPageReferences = PageReference::count();
        $pageReferencesWithSource = PageReference::whereNotNull('source')->where('source', '!=', '')->count();
        $pageReferencesThisYear = PageReference::where('created_at', '>=', Carbon::now()->startOfYear())->count();
        $newPageReferencesThisMonth = PageReference::where('created_at', '>=', Carbon::now()->startOfMonth())->count();

        // Calculate percentage of page references with source
        $pageReferencesWithSourcePercentage = $totalPageReferences > 0 ? round(($pageReferencesWithSource / $totalPageReferences) * 100) : 0;

        return [
            Stat::make('إجمالي مراجع الصفحات', $totalPageReferences)
                ->description('جميع مراجع الصفحات في النظام')
                ->descriptionIcon('heroicon-m-link')
                ->color('primary'),

            Stat::make('المراجع مع مصدر', $pageReferencesWithSource)
                ->description($pageReferencesWithSourcePercentage . '% من إجمالي المراجع')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success'),

            Stat::make('مراجع هذا العام', $pageReferencesThisYear)
                ->description('تمت إضافتها في عام ' . Carbon::now()->year)
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make('مراجع جديدة هذا الشهر', $newPageReferencesThisMonth)
                ->description('تمت إضافتها في ' . Carbon::now()->format('F Y'))
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('warning'),
        ];
    }
}