<?php

namespace App\Filament\Resources\AuthorBookResource\Pages;

use App\Filament\Resources\AuthorBookResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAuthorBook extends CreateRecord
{
    protected static string $resource = AuthorBookResource::class;
}
