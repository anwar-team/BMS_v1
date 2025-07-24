<?php

namespace App\Filament\Resources\PageReferenceResource\Pages;

use App\Filament\Resources\PageReferenceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPageReference extends EditRecord
{
    protected static string $resource = PageReferenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
