<?php

namespace App\Filament\Resources\PublisherResource\Widgets;

use App\Models\Publisher;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PublisherCountryChart extends ChartWidget
{
    protected static ?string $heading = 'توزيع دور النشر حسب البلد';
    
    protected static ?int $sort = 3;
    
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $countries = Publisher::select('country', DB::raw('count(*) as count'))
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->groupBy('country')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'عدد دور النشر',
                    'data' => $countries->pluck('count')->toArray(),
                    'backgroundColor' => [
                        '#FF6B35',
                        '#F7931E',
                        '#FFD23F',
                        '#06FFA5',
                        '#118AB2',
                        '#073B4C',
                        '#EF476F',
                        '#FFD166',
                        '#06D6A0',
                        '#8338EC',
                    ],
                ],
            ],
            'labels' => $countries->pluck('country')->toArray(),
        ];
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