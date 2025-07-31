<?php

namespace App\Filament\Resources\FootnoteResource\Pages;

use App\Filament\Resources\FootnoteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFootnote extends EditRecord
{
    protected static string $resource = FootnoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
