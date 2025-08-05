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
        $chaptersWithFootnotes = Chapter::has('footnotes')->count();
        $newChaptersThisMonth = Chapter::where('created_at', '>=', Carbon::now()->startOfMonth())->count();

        // Calculate percentage of chapters with pages
        $chaptersWithPagesPercentage = $totalChapters > 0 ? round(($chaptersWithPages / $totalChapters) * 100) : 0;

        return [
            Stat::make('إجمالي الفصول', $totalChapters)
                ->description('جميع الفصول في النظام')
                ->descriptionIcon('heroicon-m-list-bullet')
                ->color('primary'),

            Stat::make('الفصول مع صفحات', $chaptersWithPages)
                ->description($chaptersWithPagesPercentage . '% من إجمالي الفصول')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success'),

            Stat::make('الفصول مع حواشي', $chaptersWithFootnotes)
                ->description('الفصول التي تحتوي على حواشي')
                ->descriptionIcon('heroicon-m-chat-bubble-bottom-center-text')
                ->color('info'),

            Stat::make('فصول جديدة هذا الشهر', $newChaptersThisMonth)
                ->description('تمت إضافتها في ' . Carbon::now()->format('F Y'))
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('warning'),
        ];
    }
}