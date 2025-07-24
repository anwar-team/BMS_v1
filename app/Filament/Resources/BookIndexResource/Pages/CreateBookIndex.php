<?php

namespace App\Filament\Resources\BookIndexResource\Pages;

use App\Filament\Resources\BookIndexResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBookIndex extends CreateRecord
{
    protected static string $resource = BookIndexResource::class;
}
