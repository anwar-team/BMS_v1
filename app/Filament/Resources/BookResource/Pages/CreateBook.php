<?php

namespace App\Filament\Resources\BookResource\Pages;

use App\Filament\Resources\BookResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateBook extends CreateRecord
{
    protected static string $resource = BookResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
    
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('تم إنشاء الكتاب بنجاح')
            ->body('تم إضافة الكتاب إلى المكتبة الرقمية');
    }
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // تحديث slug تلقائياً إذا لم يكن موجوداً
        if (empty($data['slug']) && !empty($data['title'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['title']);
        }
        
        return $data;
    }
}
