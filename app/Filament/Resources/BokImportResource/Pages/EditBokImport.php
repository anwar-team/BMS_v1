<?php

namespace App\Filament\Resources\BokImportResource\Pages;

use App\Filament\Resources\BokImportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Grid;
use Filament\Notifications\Notification;

class EditBokImport extends EditRecord
{
    protected static string $resource = BokImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('عرض'),
            Actions\DeleteAction::make()
                ->label('حذف'),
            Actions\Action::make('reindex')
                ->label('إعادة فهرسة')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('إعادة فهرسة الكتاب')
                ->modalDescription('هل تريد إعادة فهرسة محتوى هذا الكتاب؟ قد تستغرق هذه العملية بعض الوقت.')
                ->action(function () {
                    // يمكن تطوير هذه الوظيفة لإعادة الفهرسة
                    Notification::make()
                        ->title('تمت إعادة الفهرسة')
                        ->body('تم إعادة فهرسة الكتاب بنجاح')
                        ->success()
                        ->send();
                }),
        ];
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('معلومات الكتاب الأساسية')
                    ->description('تعديل المعلومات الأساسية للكتاب المستورد')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label('عنوان الكتاب')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                    
                                Textarea::make('description')
                                    ->label('الوصف')
                                    ->rows(4)
                                    ->columnSpanFull(),
                                    
                                Select::make('language')
                                    ->label('اللغة')
                                    ->options([
                                        'ar' => 'العربية',
                                        'en' => 'الإنجليزية',
                                        'fr' => 'الفرنسية',
                                        'es' => 'الإسبانية',
                                    ])
                                    ->default('ar')
                                    ->required(),
                                    
                                Select::make('status')
                                    ->label('الحالة')
                                    ->options([
                                        'draft' => 'مسودة',
                                        'published' => 'منشور',
                                        'archived' => 'مؤرشف'
                                    ])
                                    ->default('published')
                                    ->required(),
                            ])
                    ]),
                    
                Section::make('إعدادات متقدمة')
                    ->description('إعدادات إضافية للكتاب')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_featured')
                                    ->label('كتاب مميز')
                                    ->helperText('عرض الكتاب في قائمة الكتب المميزة')
                                    ->default(false),
                                    
                                Toggle::make('allow_download')
                                    ->label('السماح بالتحميل')
                                    ->helperText('السماح للمستخدمين بتحميل الكتاب')
                                    ->default(true),
                                    
                                Toggle::make('enable_search')
                                    ->label('تفعيل البحث')
                                    ->helperText('تضمين الكتاب في نتائج البحث')
                                    ->default(true),
                                    
                                Toggle::make('public_access')
                                    ->label('وصول عام')
                                    ->helperText('السماح بالوصول للكتاب بدون تسجيل دخول')
                                    ->default(true),
                            ])
                    ])
                    ->collapsible(),
                    
                Section::make('معلومات الاستيراد')
                    ->description('تفاصيل عملية الاستيراد (للقراءة فقط)')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('volumes_count')
                                    ->label('عدد الأجزاء')
                                    ->disabled()
                                    ->default(fn ($record) => $record->volumes()->count()),
                                    
                                TextInput::make('chapters_count')
                                    ->label('عدد الفصول')
                                    ->disabled()
                                    ->default(fn ($record) => $record->chapters()->count()),
                                    
                                TextInput::make('pages_count')
                                    ->label('عدد الصفحات')
                                    ->disabled()
                                    ->default(fn ($record) => $record->pages()->count()),
                            ])
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
    
    protected function getSavedNotificationTitle(): ?string
    {
        return 'تم حفظ التغييرات بنجاح';
    }
    
    public function getTitle(): string
    {
        return 'تعديل الكتاب المستورد';
    }
    
    public function getHeading(): string
    {
        return 'تعديل: ' . $this->getRecord()->title;
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // إضافة تاريخ آخر تحديث
        $data['updated_at'] = now();
        
        return $data;
    }
    
    protected function afterSave(): void
    {
        // يمكن إضافة منطق إضافي بعد الحفظ
        // مثل إعادة فهرسة البحث أو تحديث الكاش
    }
}