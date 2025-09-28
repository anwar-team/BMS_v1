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
            Stat::make('Total Books', $totalBooks)
                ->description('All books in the system')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('primary'),

            Stat::make('Published Books', $publishedBooks)
                ->description('Books available for reading')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Books with Authors', $booksWithAuthors)
                ->description($booksWithAuthorsPercentage . '% of total books')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('New Books This Month', $newBooksThisMonth)
                ->description('Added in ' . Carbon::now()->format('F Y'))
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('warning'),
        ];
    }
}