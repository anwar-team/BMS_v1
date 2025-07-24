<?php

namespace App\Filament\Resources\BookMetadataResource\Pages;

use App\Filament\Resources\BookMetadataResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBookMetadata extends ViewRecord
{
    protected static string $resource = BookMetadataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
