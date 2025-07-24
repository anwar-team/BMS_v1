<?php

namespace App\Filament\Resources\PageReferenceResource\Pages;

use App\Filament\Resources\PageReferenceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPageReference extends ViewRecord
{
    protected static string $resource = PageReferenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
