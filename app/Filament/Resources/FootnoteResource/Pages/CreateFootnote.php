<?php

namespace App\Filament\Resources\FootnoteResource\Pages;

use App\Filament\Resources\FootnoteResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateFootnote extends CreateRecord
{
    protected static string $resource = FootnoteResource::class;
}
