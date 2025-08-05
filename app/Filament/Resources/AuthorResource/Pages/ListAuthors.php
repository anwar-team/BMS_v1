<?php

namespace App\Filament\Resources\AuthorResource\Pages;

use App\Filament\Resources\AuthorResource;
use App\Filament\Resources\AuthorResource\Widgets\AuthorStatsWidget;
use App\Filament\Resources\AuthorResource\Widgets\TopAuthorsWidget;
use App\Filament\Resources\AuthorResource\Widgets\AuthorSpecializationChart;
use App\Filament\Resources\AuthorResource\Widgets\RecentAuthorsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAuthors extends ListRecords
{
    protected static string $resource = AuthorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            AuthorStatsWidget::class,
            TopAuthorsWidget::class,
            AuthorSpecializationChart::class,
            RecentAuthorsWidget::class,
        ];
    }
}
