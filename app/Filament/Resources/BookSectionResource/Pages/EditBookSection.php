<?php

namespace App\Filament\Resources\BookSectionResource\Pages;

use App\Filament\Resources\BookSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBookSection extends EditRecord
{
    protected static string $resource = BookSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
