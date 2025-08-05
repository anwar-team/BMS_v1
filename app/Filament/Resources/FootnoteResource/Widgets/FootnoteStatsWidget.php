<?php

namespace App\Filament\Resources\FootnoteResource\Widgets;

use App\Models\Footnote;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class FootnoteStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $totalFootnotes = Footnote::count();
        $footnotesWithContent = Footnote::whereNotNull('content')->where('content', '!=', '')->count();
        $footnotesThisYear = Footnote::where('created_at', '>=', Carbon::now()->startOfYear())->count();
        $newFootnotesThisMonth = Footnote::where('created_at', '>=', Carbon::now()->startOfMonth())->count();

        // Calculate percentage of footnotes with content
        $footnotesWithContentPercentage = $totalFootnotes > 0 ? round(($footnotesWithContent / $totalFootnotes) * 100) : 0;

        return [
            Stat::make('إجمالي الحواشي', $totalFootnotes)
                ->description('جميع الحواشي في النظام')
                ->descriptionIcon('heroicon-m-chat-bubble-bottom-center-text')
                ->color('primary'),

            Stat::make('الحواشي مع محتوى', $footnotesWithContent)
                ->description($footnotesWithContentPercentage . '% من إجمالي الحواشي')
                ->descriptionIcon('heroicon-m-document-check')
                ->color('success'),

            Stat::make('حواشي هذا العام', $footnotesThisYear)
                ->description('تمت إضافتها في عام ' . Carbon::now()->year)
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make('حواشي جديدة هذا الشهر', $newFootnotesThisMonth)
                ->description('تمت إضافتها في ' . Carbon::now()->format('F Y'))
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('warning'),
        ];
    }
}