<?php

namespace App\Filament\Resources\PageResource\Widgets;

use App\Models\Page;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class PageStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $totalPages = Page::count();
        $pagesWithContent = Page::whereNotNull('content')->where('content', '!=', '')->count();
        $newPagesThisMonth = Page::where('created_at', '>=', Carbon::now()->startOfMonth())->count();

        // Calculate percentage of pages with content
        $pagesWithContentPercentage = $totalPages > 0 ? round(($pagesWithContent / $totalPages) * 100) : 0;

        return [
            Stat::make('Total Pages', $totalPages)
                ->description('All pages in the system')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),
                
            Stat::make('Pages with Content', $pagesWithContent)
                ->description($pagesWithContentPercentage . '% of total pages')
                ->descriptionIcon('heroicon-m-document-check')
                ->color('success'),
                
            Stat::make('New Pages This Month', $newPagesThisMonth)
                ->description('Added in ' . Carbon::now()->format('F Y'))
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('warning'),
        ];
    }
}