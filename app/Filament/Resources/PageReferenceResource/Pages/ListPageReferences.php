<?php

namespace App\Filament\Resources\PageReferenceResource\Pages;

use App\Filament\Resources\PageReferenceResource;
use App\Filament\Resources\PageReferenceResource\Widgets\PageReferenceStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPageReferences extends ListRecords
{
    protected static string $resource = PageReferenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            PageReferenceStatsWidget::class,
        ];
    }
}
