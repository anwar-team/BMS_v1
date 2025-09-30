<?php

namespace App\Filament\Widgets;

use App\Models\Author;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;

class AuthorsBooksChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Most Productive Authors';
    protected static ?int $sort = 8;
    protected int | string | array $columnSpan = 'full';
    
    // تعطيل التحديث التلقائي
    protected static ?string $pollingInterval = null;
    
    // Cache للبيانات لمدة 30 دقيقة (البيانات لا تتغير كثيراً)
    protected static string $cacheKey = 'authors_books_chart_widget_data';
    protected static int $cacheDuration = 1800; // 30 دقيقة

    protected function getData(): array
    {
        // استخدام Cache لتجنب إعادة حساب البيانات في كل مرة
        return Cache::remember(static::$cacheKey, static::$cacheDuration, function () {
            $authors = Author::withCount('books')
                ->having('books_count', '>', 0)
                ->orderBy('books_count', 'desc')
                ->limit(10)
                ->get();

            return [
                'datasets' => [
                    [
                        'label' => 'Number of Books',
                        'data' => $authors->pluck('books_count')->toArray(),
                        'backgroundColor' => [
                            'rgba(255, 99, 132, 0.8)',
                            'rgba(54, 162, 235, 0.8)',
                            'rgba(255, 205, 86, 0.8)',
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(153, 102, 255, 0.8)',
                            'rgba(255, 159, 64, 0.8)',
                            'rgba(199, 199, 199, 0.8)',
                            'rgba(83, 102, 255, 0.8)',
                            'rgba(255, 99, 255, 0.8)',
                            'rgba(99, 255, 132, 0.8)',
                        ],
                        'borderColor' => [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 205, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)',
                            'rgba(199, 199, 199, 1)',
                            'rgba(83, 102, 255, 1)',
                            'rgba(255, 99, 255, 1)',
                            'rgba(99, 255, 132, 1)',
                        ],
                        'borderWidth' => 1,
                    ],
                ],
                'labels' => $authors->pluck('full_name')->toArray(),
            ];
        });
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }
}