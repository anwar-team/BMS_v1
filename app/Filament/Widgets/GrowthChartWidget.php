<?php

namespace App\Filament\Widgets;

use App\Models\Book;
use App\Models\Author;
use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class GrowthChartWidget extends ChartWidget
{
    protected static ?string $heading = 'نمو النظام عبر الوقت';
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $months = collect(range(0, 11))->map(function ($month) {
            return Carbon::now()->subMonths($month)->format('Y-m');
        })->reverse();

        $booksData = $months->map(function ($month) {
            return Book::whereYear('created_at', Carbon::parse($month)->year)
                ->whereMonth('created_at', Carbon::parse($month)->month)
                ->count();
        });

        $authorsData = $months->map(function ($month) {
            return Author::whereYear('created_at', Carbon::parse($month)->year)
                ->whereMonth('created_at', Carbon::parse($month)->month)
                ->count();
        });

        $usersData = $months->map(function ($month) {
            return User::whereYear('created_at', Carbon::parse($month)->year)
                ->whereMonth('created_at', Carbon::parse($month)->month)
                ->count();
        });

        return [
            'datasets' => [
                [
                    'label' => 'الكتب الجديدة',
                    'data' => $booksData->toArray(),
                    'borderColor' => '#36A2EB',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'المؤلفون الجدد',
                    'data' => $authorsData->toArray(),
                    'borderColor' => '#FF6384',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'المستخدمون الجدد',
                    'data' => $usersData->toArray(),
                    'borderColor' => '#4BC0C0',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $months->map(function ($month) {
                return Carbon::parse($month)->locale('ar')->format('M Y');
            })->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }
}