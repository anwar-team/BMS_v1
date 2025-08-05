<?php

namespace App\Filament\Resources\BookMetadataResource\Widgets;

use App\Models\BookMetadata;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class BookMetadataStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $totalBookMetadata = BookMetadata::count();
        $metadataWithDescription = BookMetadata::whereNotNull('description')->where('description', '!=', '')->count();
        $metadataThisYear = BookMetadata::where('created_at', '>=', Carbon::now()->startOfYear())->count();
        $newMetadataThisMonth = BookMetadata::where('created_at', '>=', Carbon::now()->startOfMonth())->count();

        // Calculate percentage of metadata with description
        $metadataWithDescriptionPercentage = $totalBookMetadata > 0 ? round(($metadataWithDescription / $totalBookMetadata) * 100) : 0;

        return [
            Stat::make('إجمالي البيانات الوصفية', $totalBookMetadata)
                ->description('جميع البيانات الوصفية في النظام')
                ->descriptionIcon('heroicon-m-information-circle')
                ->color('primary'),

            Stat::make('البيانات مع وصف', $metadataWithDescription)
                ->description($metadataWithDescriptionPercentage . '% من إجمالي البيانات')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success'),

            Stat::make('بيانات هذا العام', $metadataThisYear)
                ->description('تمت إضافتها في عام ' . Carbon::now()->year)
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make('بيانات جديدة هذا الشهر', $newMetadataThisMonth)
                ->description('تمت إضافتها في ' . Carbon::now()->format('F Y'))
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('warning'),
        ];
    }
}