<?php

namespace App\Filament\Resources\Banner\ContentResource\Pages;

use App\Filament\Resources\Banner\ContentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewContent extends ViewRecord
{
    protected static string $resource = ContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return 'Úׁײ ַבדֽÊזל';
    }
}
