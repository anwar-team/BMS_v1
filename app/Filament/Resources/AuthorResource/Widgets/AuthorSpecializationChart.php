<?php

namespace App\Filament\Resources\AuthorResource\Widgets;

use App\Models\Author;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class AuthorSpecializationChart extends ChartWidget
{
    protected static ?string $heading = 'توزيع المؤلفين حسب التخصص';
    
    protected static ?int $sort = 3;
    
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $specializations = Author::select('specialization', DB::raw('count(*) as count'))
            ->whereNotNull('specialization')
            ->where('specialization', '!=', '')
            ->groupBy('specialization')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'عدد المؤلفين',
                    'data' => $specializations->pluck('count')->toArray(),
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
            'labels' => $specializations->pluck('specialization')->toArray(),
        ];
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