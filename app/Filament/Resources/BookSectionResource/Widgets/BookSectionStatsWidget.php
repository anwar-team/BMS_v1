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
            Stat::make('إجمالي أقسام الكتب', $totalSections)
                ->description('جميع أقسام الكتب في النظام')
                ->descriptionIcon('heroicon-m-folder')
                ->color('primary'),
                
            Stat::make('الأقسام النشطة', $activeSections)
                ->description('الأقسام النشطة حالياً')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
                
            Stat::make('الأقسام التي تحتوي على كتب', $sectionsWithBooks)
                ->description($sectionsWithBooksPercentage . '% من إجمالي الأقسام')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('info'),
                
            Stat::make('الأقسام الجديدة هذا الشهر', $newSectionsThisMonth)
                ->description('تمت إضافتها هذا الشهر')
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('warning'),
        ];
    }
}