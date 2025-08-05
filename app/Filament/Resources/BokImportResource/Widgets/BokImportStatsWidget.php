<?php

namespace App\Filament\Resources\BokImportResource\Widgets;

use App\Models\BokImport;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class BokImportStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $totalImports = BokImport::count();
        $successfulImports = BokImport::where('status', 'completed')->count();
        $importsThisYear = BokImport::where('created_at', '>=', Carbon::now()->startOfYear())->count();
        $newImportsThisMonth = BokImport::where('created_at', '>=', Carbon::now()->startOfMonth())->count();

        // Calculate percentage of successful imports
        $successfulImportsPercentage = $totalImports > 0 ? round(($successfulImports / $totalImports) * 100) : 0;

        return [
            Stat::make('إجمالي عمليات الاستيراد', $totalImports)
                ->description('جميع عمليات استيراد الكتب')
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color('primary'),

            Stat::make('الاستيرادات الناجحة', $successfulImports)
                ->description($successfulImportsPercentage . '% من إجمالي العمليات')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('استيرادات هذا العام', $importsThisYear)
                ->description('تمت في عام ' . Carbon::now()->year)
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make('استيرادات جديدة هذا الشهر', $newImportsThisMonth)
                ->description('تمت في ' . Carbon::now()->format('F Y'))
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('warning'),
        ];
    }
}