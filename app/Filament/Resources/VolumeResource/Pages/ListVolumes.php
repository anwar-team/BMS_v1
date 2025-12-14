<?php

namespace App\Filament\Resources\VolumeResource\Pages;

use App\Filament\Resources\VolumeResource;
use App\Filament\Resources\VolumeResource\Widgets\VolumeStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVolumes extends ListRecords
{
    protected static string $resource = VolumeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            VolumeStatsWidget::class,
        ];
    }
}
