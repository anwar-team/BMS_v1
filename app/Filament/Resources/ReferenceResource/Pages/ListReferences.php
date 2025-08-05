<?php

namespace App\Filament\Resources\ReferenceResource\Pages;

use App\Filament\Resources\ReferenceResource;
use App\Filament\Resources\ReferenceResource\Widgets\ReferenceStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReferences extends ListRecords
{
    protected static string $resource = ReferenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            ReferenceStatsWidget::class,
        ];
    }
}
