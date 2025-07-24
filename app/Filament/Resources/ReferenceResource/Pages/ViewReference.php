<?php

namespace App\Filament\Resources\ReferenceResource\Pages;

use App\Filament\Resources\ReferenceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewReference extends ViewRecord
{
    protected static string $resource = ReferenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
