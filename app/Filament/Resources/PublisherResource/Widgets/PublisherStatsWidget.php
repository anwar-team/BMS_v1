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
            Stat::make('Total Publishers', $totalPublishers)
                ->description('All publishers in the system')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('primary'),
                
            Stat::make('Active Publishers', $activePublishers)
                ->description('Currently active publishers')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
                
            Stat::make('Publishers with Books', $publishersWithBooks)
                ->description($publishersWithBooksPercentage . '% of total publishers')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('info'),
                
            Stat::make('New Publishers This Month', $newPublishersThisMonth)
                ->description('Added this month')
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('warning'),
        ];
    }
}