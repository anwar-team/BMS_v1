<?php

namespace App\Filament\Resources\AuthorBookResource\Pages;

use App\Filament\Resources\AuthorBookResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAuthorBook extends EditRecord
{
    protected static string $resource = AuthorBookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
