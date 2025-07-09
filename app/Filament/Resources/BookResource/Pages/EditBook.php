<?php

namespace App\Filament\Resources\BookResource\Pages;

use App\Filament\Resources\BookResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditBook extends EditRecord
{
    protected static string $resource = BookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('عرض الكتاب'),
            Actions\DeleteAction::make()
                ->label('حذف الكتاب')
                ->requiresConfirmation()
                ->modalHeading('حذف الكتاب')
                ->modalDescription('هل أنت متأكد من حذف هذا الكتاب؟ لا يمكن التراجع عن هذا الإجراء.')
                ->modalSubmitActionLabel('نعم، احذف'),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
    
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('تم تحديث الكتاب بنجاح')
            ->body('تم حفظ التغييرات بنجاح');
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // تحديث slug تلقائياً إذا لم يكن موجوداً
        if (empty($data['slug']) && !empty($data['title'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['title']);
        }
        
        return $data;
    }
}
