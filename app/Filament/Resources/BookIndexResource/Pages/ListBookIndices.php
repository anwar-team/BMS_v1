<?php

namespace App\Filament\Resources\BookIndexResource\Pages;

use App\Filament\Resources\BookIndexResource;
use App\Filament\Resources\BookIndexResource\Widgets\BookIndexStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBookIndices extends ListRecords
{
    protected static string $resource = BookIndexResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            BookIndexStatsWidget::class,
        ];
    }
}
