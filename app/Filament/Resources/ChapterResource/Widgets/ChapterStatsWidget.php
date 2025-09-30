<?php

namespace App\Filament\Resources\ChapterResource\Widgets;

use App\Models\Chapter;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class ChapterStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $totalChapters = Chapter::count();
        $chaptersWithPages = Chapter::has('pages')->count();
        $newChaptersThisMonth = Chapter::where('created_at', '>=', Carbon::now()->startOfMonth())->count();

        // Calculate percentage of chapters with pages
        $chaptersWithPagesPercentage = $totalChapters > 0 ? round(($chaptersWithPages / $totalChapters) * 100) : 0;

        return [
            Stat::make('Total Chapters', $totalChapters)
                ->description('All chapters in the system')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('primary'),
                
            Stat::make('Chapters with Pages', $chaptersWithPages)
                ->description($chaptersWithPagesPercentage . '% of total chapters')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success'),
                
            Stat::make('New Chapters This Month', $newChaptersThisMonth)
                ->description('Added in ' . Carbon::now()->format('F Y'))
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('warning'),
        ];
    }
}