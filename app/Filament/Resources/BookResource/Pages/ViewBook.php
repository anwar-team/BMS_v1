<?php

namespace App\Filament\Resources\BookResource\Pages;

use App\Filament\Resources\BookResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;

class ViewBook extends ViewRecord
{
    protected static string $resource = BookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_on_frontend')
                ->label('عرض في الواجهة الأمامية')
                ->icon('heroicon-o-eye')
                ->url(fn () => route('book.show', $this->record->slug))
                ->openUrlInNewTab(),
            Actions\EditAction::make(),
        ];
    }
}
