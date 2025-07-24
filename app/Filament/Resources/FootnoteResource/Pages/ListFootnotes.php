<?php

namespace App\Filament\Resources\FootnoteResource\Pages;

use App\Filament\Resources\FootnoteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFootnotes extends ListRecords
{
    protected static string $resource = FootnoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
