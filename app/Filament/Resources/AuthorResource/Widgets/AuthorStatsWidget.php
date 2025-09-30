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
        $authorsWithBooks = Author::has('books')->count();
        $newAuthorsThisMonth = Author::where('created_at', '>=', Carbon::now()->startOfMonth())->count();
        $authorsThisYear = Author::where('created_at', '>=', Carbon::now()->startOfYear())->count();

        // Calculate percentage of authors with books
        $authorsWithBooksPercentage = $totalAuthors > 0 ? round(($authorsWithBooks / $totalAuthors) * 100) : 0;

        return [
            Stat::make('إجمالي المؤلفين', $totalAuthors)
                ->description('جميع المؤلفين في النظام')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('المؤلفين مع كتب', $authorsWithBooks)
                ->description($authorsWithBooksPercentage . '% من إجمالي المؤلفين')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('info'),

            Stat::make('مؤلفين جدد هذا الشهر', $newAuthorsThisMonth)
                ->description('تمت إضافتهم في ' . Carbon::now()->format('F Y'))
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('warning'),

            Stat::make('مؤلفين هذا العام', $authorsThisYear)
                ->description('تمت إضافتهم في عام ' . Carbon::now()->year)
                ->descriptionIcon('heroicon-m-calendar')
                ->color('success'),
        ];
    }
}