<?php

namespace App\Filament\Resources\AuthorResource\Widgets;

use App\Models\Author;
use App\Models\Book;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class AuthorStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $totalAuthors = Author::count();
        $livingAuthors = Author::where('is_living', true)->count();
        $authorsWithBooks = Author::has('books')->count();
        $newAuthorsThisMonth = Author::where('created_at', '>=', Carbon::now()->startOfMonth())->count();

        // Calculate percentage of authors with books
        $authorsWithBooksPercentage = $totalAuthors > 0 ? round(($authorsWithBooks / $totalAuthors) * 100) : 0;

        return [
            Stat::make('إجمالي المؤلفين', $totalAuthors)
                ->description('جميع المؤلفين في النظام')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('المؤلفين الأحياء', $livingAuthors)
                ->description('المؤلفين الأحياء حالياً')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
                
            Stat::make('مؤلفين لديهم كتب', $authorsWithBooks)
                ->description($authorsWithBooksPercentage . '% من إجمالي المؤلفين')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('info'),
                
            Stat::make('مؤلفين جدد هذا الشهر', $newAuthorsThisMonth)
                ->description('المضافين في الشهر الحالي')
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('warning'),
        ];
    }
}