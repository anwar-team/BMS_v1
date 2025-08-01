<?php

namespace App\Filament\Resources\BookMetadataResource\Pages;

use App\Filament\Resources\BookMetadataResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBookMetadata extends CreateRecord
{
    protected static string $resource = BookMetadataResource::class;
}
