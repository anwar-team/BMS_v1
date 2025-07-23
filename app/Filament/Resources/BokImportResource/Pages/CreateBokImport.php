<?php

namespace App\Filament\Resources\BokImportResource\Pages;

use App\Filament\Resources\BokImportResource;
use App\Services\BokConverterService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;

class CreateBokImport extends CreateRecord
{
    protected static string $resource = BokImportResource::class;
    
    protected static bool $canCreateAnother = false;
    
    public function getTitle(): string
    {
        return 'استيراد ملف BOK جديد';
    }
    
    public function getHeading(): string
    {
        return 'تحويل ملف المكتبة الشاملة';
    }
    
    public function getSubheading(): ?string
    {
        return 'اتبع الخطوات لتحويل ملف .bok إلى قاعدة البيانات';
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // لا نحتاج لإنشاء سجل فعلي، فقط تحويل الملف
        return [];
    }
    
    protected function handleRecordCreation(array $data): Model
    {
        try {
            // التحقق من وجود ملف BOK
            if (!isset($data['bok_file']) || empty($data['bok_file'])) {
                throw new \Exception('لم يتم رفع ملف BOK');
            }
            
            $bokFile = $data['bok_file'];
            $filePath = Storage::path($bokFile);
            
            // التحقق من وجود الملف
            if (!file_exists($filePath)) {
                throw new \Exception('ملف BOK غير موجود');
            }
            
            // تحويل الملف
            $converter = new BokConverterService();
            $result = $converter->convertBokFile($filePath);
            
            if (!$result['success']) {
                throw new \Exception($result['error']);
            }
            
            // الحصول على الكتاب المحول
            $book = \App\Models\Book::find($result['book_id']);
            
            if (!$book) {
                throw new \Exception('فشل في العثور على الكتاب المحول');
            }
            
            // تحديث حالة الكتاب بناءً على إعدادات الاستيراد
            if (isset($data['import_status'])) {
                $book->update(['status' => $data['import_status']]);
            }
            
            // إرسال إشعار نجاح
            Notification::make()
                ->title('تم التحويل بنجاح')
                ->body("تم تحويل الكتاب '{$book->title}' بنجاح")
                ->success()
                ->persistent()
                ->actions([
                    \Filament\Notifications\Actions\Action::make('view')
                        ->label('عرض الكتاب')
                        ->url(route('filament.admin.resources.books.view', $book->id))
                        ->button()
                ])
                ->send();
            
            // حذف الملف المؤقت
            Storage::delete($bokFile);
            
            return $book;
            
        } catch (\Exception $e) {
            // إرسال إشعار خطأ
            Notification::make()
                ->title('فشل في التحويل')
                ->body($e->getMessage())
                ->danger()
                ->persistent()
                ->send();
            
            // حذف الملف المؤقت في حالة الخطأ
            if (isset($bokFile)) {
                Storage::delete($bokFile);
            }
            
            throw $e;
        }
    }
    
    protected function getCreatedNotificationTitle(): ?string
    {
        return null; // نستخدم الإشعار المخصص
    }
    
    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('بدء التحويل')
                ->icon('heroicon-o-arrow-path'),
            $this->getCancelFormAction()
                ->label('إلغاء'),
        ];
    }
}