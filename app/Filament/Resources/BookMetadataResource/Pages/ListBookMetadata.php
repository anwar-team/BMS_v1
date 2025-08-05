<?php

namespace App\Filament\Resources\BookMetadataResource\Pages;

use App\Filament\Resources\BookMetadataResource;
use App\Filament\Resources\BookMetadataResource\Widgets\BookMetadataStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBookMetadata extends ListRecords
{
    protected static string $resource = BookMetadataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            BookMetadataStatsWidget::class,
        ];
    }
}
