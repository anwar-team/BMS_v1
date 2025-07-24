<?php

namespace App\Filament\Resources\AnnotationResource\Pages;

use App\Filament\Resources\AnnotationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAnnotations extends ListRecords
{
    protected static string $resource = AnnotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
