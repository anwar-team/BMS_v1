<?php

namespace App\Filament\Resources\BookResource\Widgets;

use App\Models\Book;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class BookStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $totalBooks = Book::count();
        $publishedBooks = Book::where('status', 'published')->count();
        $booksWithAuthors = Book::has('authors')->count();
        $newBooksThisMonth = Book::where('created_at', '>=', Carbon::now()->startOfMonth())->count();

        // Calculate percentage of books with authors
        $booksWithAuthorsPercentage = $totalBooks > 0 ? round(($booksWithAuthors / $totalBooks) * 100) : 0;

        return [
            Stat::make('إجمالي الكتب', $totalBooks)
                ->description('جميع الكتب في النظام')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('primary'),

            Stat::make('الكتب المنشورة', $publishedBooks)
                ->description('الكتب المتاحة للقراءة')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('الكتب مع مؤلفين', $booksWithAuthors)
                ->description($booksWithAuthorsPercentage . '% من إجمالي الكتب')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('كتب جديدة هذا الشهر', $newBooksThisMonth)
                ->description('تمت إضافتها في ' . Carbon::now()->format('F Y'))
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('warning'),
        ];
    }
}