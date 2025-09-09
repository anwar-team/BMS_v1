<?php

namespace App\Filament\Widgets;

use App\Models\BookSection;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;

class BooksSectionChartWidget extends ChartWidget
{
    protected static ?string $heading = 'توزيع الكتب حسب الأقسام';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';
    
    // تعطيل التحديث التلقائي
    protected static ?string $pollingInterval = null;
    
    // Cache للبيانات لمدة 25 دقيقة
    protected static string $cacheKey = 'books_section_chart_widget_data';
    protected static int $cacheDuration = 1500; // 25 دقيقة

    protected function getData(): array
    {
        // استخدام Cache لتجنب إعادة حساب البيانات في كل مرة
        return Cache::remember(static::$cacheKey, static::$cacheDuration, function () {
            $sections = BookSection::withCount('books')
                ->having('books_count', '>', 0)
                ->orderBy('books_count', 'desc')
                ->limit(10)
                ->get();

            return [
                'datasets' => [
                    [
                        'label' => 'عدد الكتب',
                        'data' => $sections->pluck('books_count')->toArray(),
                        'backgroundColor' => [
                            '#FF6384',
                            '#36A2EB',
                            '#FFCE56',
                            '#4BC0C0',
                            '#9966FF',
                            '#FF9F40',
                            '#FF6384',
                            '#C9CBCF',
                            '#4BC0C0',
                            '#FF6384',
                        ],
                    ],
                ],
                'labels' => $sections->pluck('name')->toArray(),
            ];
        });
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }
}