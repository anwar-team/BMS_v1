<?php

namespace App\Filament\Resources\ReferenceResource\Widgets;

use App\Models\Reference;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class ReferenceStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $totalReferences = Reference::count();
        $referencesWithTitle = Reference::whereNotNull('title')->where('title', '!=', '')->count();
        $referencesThisYear = Reference::where('created_at', '>=', Carbon::now()->startOfYear())->count();
        $newReferencesThisMonth = Reference::where('created_at', '>=', Carbon::now()->startOfMonth())->count();

        // Calculate percentage of references with title
        $referencesWithTitlePercentage = $totalReferences > 0 ? round(($referencesWithTitle / $totalReferences) * 100) : 0;

        return [
            Stat::make('إجمالي المراجع', $totalReferences)
                ->description('جميع المراجع في النظام')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('primary'),

            Stat::make('المراجع مع عنوان', $referencesWithTitle)
                ->description($referencesWithTitlePercentage . '% من إجمالي المراجع')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success'),

            Stat::make('مراجع هذا العام', $referencesThisYear)
                ->description('تمت إضافتها في عام ' . Carbon::now()->year)
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make('مراجع جديدة هذا الشهر', $newReferencesThisMonth)
                ->description('تمت إضافتها في ' . Carbon::now()->format('F Y'))
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('warning'),
        ];
    }
}