<?php

namespace App\Filament\Resources\FeedbackComplaintResource\Widgets;

use App\Models\FeedbackComplaint;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FeedbackStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('إجمالي الرسائل', FeedbackComplaint::count())
                ->description('جميع الملاحظات والشكاوى')
                ->descriptionIcon('heroicon-m-inbox-stack')
                ->color('primary')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),
            
            Stat::make('المعلقة', FeedbackComplaint::where('status', 'pending')->count())
                ->description('تحتاج للمراجعة')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->chart([2, 1, 3, 2, 1, 2, 1, 3]),
            
            Stat::make('قيد المعالجة', FeedbackComplaint::where('status', 'in_progress')->count())
                ->description('يتم العمل عليها')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('info')
                ->chart([1, 2, 1, 3, 2, 1, 2, 1]),
            
            Stat::make('المحلولة', FeedbackComplaint::where('status', 'resolved')->count())
                ->description('تم الانتهاء منها')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([3, 5, 4, 6, 7, 5, 6, 4]),
        ];
    }
}
