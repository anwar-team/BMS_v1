<?php

namespace App\Filament\Resources\BookIndexResource\Pages;

use App\Filament\Resources\BookIndexResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBookIndex extends EditRecord
{
    protected static string $resource = BookIndexResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
