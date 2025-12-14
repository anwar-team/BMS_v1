<?php

namespace App\Filament\Resources\PublisherResource\Widgets;

use App\Models\Publisher;
use App\Models\Book;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class PublisherStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $totalPublishers = Publisher::count();
        $activePublishers = Publisher::where('is_active', true)->count();
        $publishersWithBooks = Publisher::has('books')->count();
        $newPublishersThisMonth = Publisher::where('created_at', '>=', Carbon::now()->startOfMonth())->count();
        
        // Calculate percentage of publishers with books
        $publishersWithBooksPercentage = $totalPublishers > 0 ? round(($publishersWithBooks / $totalPublishers) * 100) : 0;
        
        return [
            Stat::make('إجمالي الناشرين', $totalPublishers)
                ->description('جميع الناشرين في النظام')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('primary'),
                
            Stat::make('الناشرون النشطون', $activePublishers)
                ->description('الناشرون النشطون حالياً')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
                
            Stat::make('الناشرون الذين لديهم كتب', $publishersWithBooks)
                ->description($publishersWithBooksPercentage . '% من إجمالي الناشرين')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('info'),
                
            Stat::make('الناشرون الجدد هذا الشهر', $newPublishersThisMonth)
                ->description('أُضيفوا هذا الشهر')
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('warning'),
        ];
    }
}