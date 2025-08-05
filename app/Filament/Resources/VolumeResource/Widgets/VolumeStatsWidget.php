<?php

namespace App\Filament\Resources\VolumeResource\Widgets;

use App\Models\Volume;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class VolumeStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $totalVolumes = Volume::count();
        $volumesWithPages = Volume::has('pages')->count();
        $volumesWithChapters = Volume::has('chapters')->count();
        $newVolumesThisMonth = Volume::where('created_at', '>=', Carbon::now()->startOfMonth())->count();

        // Calculate percentage of volumes with pages
        $volumesWithPagesPercentage = $totalVolumes > 0 ? round(($volumesWithPages / $totalVolumes) * 100) : 0;

        return [
            Stat::make('إجمالي المجلدات', $totalVolumes)
                ->description('جميع المجلدات في النظام')
                ->descriptionIcon('heroicon-m-folder')
                ->color('primary'),

            Stat::make('المجلدات مع صفحات', $volumesWithPages)
                ->description($volumesWithPagesPercentage . '% من إجمالي المجلدات')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success'),

            Stat::make('المجلدات مع فصول', $volumesWithChapters)
                ->description('المجلدات التي تحتوي على فصول')
                ->descriptionIcon('heroicon-m-list-bullet')
                ->color('info'),

            Stat::make('مجلدات جديدة هذا الشهر', $newVolumesThisMonth)
                ->description('تمت إضافتها في ' . Carbon::now()->format('F Y'))
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('warning'),
        ];
    }
}