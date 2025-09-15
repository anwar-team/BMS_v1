<?php

namespace App\Filament\Resources\BokImportResource\Pages;

use App\Filament\Resources\BokImportResource;
use App\Filament\Resources\BokImportResource\Widgets\BokImportStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBokImports extends ListRecords
{
    protected static string $resource = BokImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('import_shamela')
                ->label('استيراد من الشاملة')
                ->icon('heroicon-o-cloud-arrow-down')
                ->color('success')
                ->url(fn (): string => static::$resource::getUrl('import-shamela'))
        ];
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            BokImportStatsWidget::class,
        ];
    }
}
