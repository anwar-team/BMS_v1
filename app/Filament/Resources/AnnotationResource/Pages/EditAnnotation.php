<?php

namespace App\Filament\Resources\AnnotationResource\Pages;

use App\Filament\Resources\AnnotationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAnnotation extends EditRecord
{
    protected static string $resource = AnnotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
