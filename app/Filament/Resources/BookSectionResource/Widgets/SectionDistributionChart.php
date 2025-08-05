<?php

namespace App\Filament\Resources\BookSectionResource\Widgets;

use App\Models\BookSection;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SectionDistributionChart extends ChartWidget
{
    protected static ?string $heading = 'توزيع الكتب حسب الأقسام';
    
    protected static ?int $sort = 3;
    
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $sections = BookSection::select('name', DB::raw('count(books.id) as books_count'))
            ->leftJoin('books', 'book_sections.id', '=', 'books.book_section_id')
            ->groupBy('book_sections.id', 'book_sections.name')
            ->having('books_count', '>', 0)
            ->orderBy('books_count', 'desc')
            ->limit(8)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'عدد الكتب',
                    'data' => $sections->pluck('books_count')->toArray(),
                    'backgroundColor' => [
                        '#FF6B35',
                        '#F7931E', 
                        '#FFD23F',
                        '#06FFA5',
                        '#118AB2',
                        '#073B4C',
                        '#EF476F',
                        '#8338EC',
                    ],
                    'borderWidth' => 2,
                    'borderColor' => '#ffffff',
                ],
            ],
            'labels' => $sections->pluck('name')->toArray(),
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