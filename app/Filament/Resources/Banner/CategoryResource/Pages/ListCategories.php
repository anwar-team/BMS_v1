<?php

namespace App\Filament\Resources\Banner\CategoryResource\Pages;

use App\Filament\Resources\Banner\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ViewRecord;
use Asmit\ResizedColumn\HasResizableColumn;

class ListCategories extends ListRecords
{
    use HasResizableColumn;
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return __('Banner Categories Management');
    }
}
