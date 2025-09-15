<?php

namespace App\Filament\Resources\ContactUsResource\Pages;

use App\Filament\Resources\ContactUsResource;
use App\Filament\Resources\ContactUsResource\Widgets\ContactUsStatsWidget;
use Filament\Resources\Pages\ListRecords;
use Asmit\ResizedColumn\HasResizableColumn;

class ListContactUs extends ListRecords
{
    use HasResizableColumn;
    protected static string $resource = ContactUsResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ContactUsStatsWidget::class,
        ];
    }
}
