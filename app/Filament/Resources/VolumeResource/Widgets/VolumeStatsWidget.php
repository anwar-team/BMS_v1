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
            Stat::make('Total Volumes', $totalVolumes)
                ->description('All volumes in the system')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('primary'),
                
            Stat::make('Volumes with Pages', $volumesWithPages)
                ->description($volumesWithPagesPercentage . '% of total volumes')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success'),
                
            Stat::make('Volumes with Chapters', $volumesWithChapters)
                ->description('Volumes that contain chapters')
                ->descriptionIcon('heroicon-m-list-bullet')
                ->color('info'),
                
            Stat::make('New Volumes This Month', $newVolumesThisMonth)
                ->description('Added in ' . Carbon::now()->format('F Y'))
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('warning'),
        ];
    }
}