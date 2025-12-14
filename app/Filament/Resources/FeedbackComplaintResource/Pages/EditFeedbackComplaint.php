<?php

namespace App\Filament\Resources\FeedbackComplaintResource\Pages;

use App\Filament\Resources\FeedbackComplaintResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditFeedbackComplaint extends EditRecord
{
    protected static string $resource = FeedbackComplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('حذف')
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('تم الحذف')
                        ->body('تم حذف الرسالة بنجاح'),
                ),
        ];
    }
    
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('تم الحفظ')
            ->body('تم تحديث الرسالة بنجاح');
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
