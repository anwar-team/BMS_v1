<?php

namespace App\Filament\Resources\FeedbackComplaintResource\Pages;

use App\Filament\Resources\FeedbackComplaintResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFeedbackComplaints extends ListRecords
{
    protected static string $resource = FeedbackComplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
