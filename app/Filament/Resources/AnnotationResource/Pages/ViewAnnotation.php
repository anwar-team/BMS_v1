<?php

namespace App\Filament\Resources\AnnotationResource\Pages;

use App\Filament\Resources\AnnotationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAnnotation extends ViewRecord
{
    protected static string $resource = AnnotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
