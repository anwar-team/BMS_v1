<?php

namespace App\Filament\Resources\BookSectionResource\Widgets;

use App\Models\BookSection;
use App\Models\Book;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class BookSectionStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $totalSections = BookSection::count();
        $activeSections = BookSection::where('is_active', true)->count();
        $sectionsWithBooks = BookSection::has('books')->count();
        $newSectionsThisMonth = BookSection::where('created_at', '>=', Carbon::now()->startOfMonth())->count();
        
        // Calculate percentage of sections with books
        $sectionsWithBooksPercentage = $totalSections > 0 ? round(($sectionsWithBooks / $totalSections) * 100) : 0;
        
        return [
            Stat::make('Total Book Sections', $totalSections)
                ->description('All book sections in the system')
                ->descriptionIcon('heroicon-m-folder')
                ->color('primary'),
                
            Stat::make('Active Sections', $activeSections)
                ->description('Currently active sections')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
                
            Stat::make('Sections with Books', $sectionsWithBooks)
                ->description($sectionsWithBooksPercentage . '% of total sections')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('info'),
                
            Stat::make('New Sections This Month', $newSectionsThisMonth)
                ->description('Added this month')
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('warning'),
        ];
    }
}