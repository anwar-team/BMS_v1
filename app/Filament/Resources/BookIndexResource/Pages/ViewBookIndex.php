<?php

namespace App\Filament\Resources\BookIndexResource\Pages;

use App\Filament\Resources\BookIndexResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBookIndex extends ViewRecord
{
    protected static string $resource = BookIndexResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
