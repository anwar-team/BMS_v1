<?php

namespace App\Filament\Resources\BookMetadataResource\Pages;

use App\Filament\Resources\BookMetadataResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBookMetadata extends EditRecord
{
    protected static string $resource = BookMetadataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
