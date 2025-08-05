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
        $pagesWithFootnotes = Page::has('footnotes')->count();
        $newPagesThisMonth = Page::where('created_at', '>=', Carbon::now()->startOfMonth())->count();

        // Calculate percentage of pages with content
        $pagesWithContentPercentage = $totalPages > 0 ? round(($pagesWithContent / $totalPages) * 100) : 0;

        return [
            Stat::make('إجمالي الصفحات', $totalPages)
                ->description('جميع الصفحات في النظام')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('الصفحات مع محتوى', $pagesWithContent)
                ->description($pagesWithContentPercentage . '% من إجمالي الصفحات')
                ->descriptionIcon('heroicon-m-document-check')
                ->color('success'),

            Stat::make('الصفحات مع حواشي', $pagesWithFootnotes)
                ->description('الصفحات التي تحتوي على حواشي')
                ->descriptionIcon('heroicon-m-chat-bubble-bottom-center-text')
                ->color('info'),

            Stat::make('صفحات جديدة هذا الشهر', $newPagesThisMonth)
                ->description('تمت إضافتها في ' . Carbon::now()->format('F Y'))
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('warning'),
        ];
    }
}