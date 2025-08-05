<?php

namespace App\Filament\Resources\BookSectionResource\Pages;

use App\Filament\Resources\BookSectionResource;
use App\Filament\Resources\BookSectionResource\Widgets\BookSectionStatsWidget;
use App\Filament\Resources\BookSectionResource\Widgets\PopularSectionsWidget;
use App\Filament\Resources\BookSectionResource\Widgets\SectionDistributionChart;
use App\Filament\Resources\BookSectionResource\Widgets\RecentSectionsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBookSections extends ListRecords
{
    protected static string $resource = BookSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            BookSectionStatsWidget::class,
            PopularSectionsWidget::class,
            SectionDistributionChart::class,
            RecentSectionsWidget::class,
        ];
    }
}
