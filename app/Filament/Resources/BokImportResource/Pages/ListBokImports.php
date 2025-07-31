<?php

namespace App\Filament\Resources\BokImportResource\Pages;

use App\Filament\Resources\BokImportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBokImports extends ListRecords
{
    protected static string $resource = BokImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
