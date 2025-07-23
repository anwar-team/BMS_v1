<?php

namespace App\Filament\Resources\BokImportResource\Pages;

use App\Filament\Resources\BokImportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Filament\Support\Enums\MaxWidth;

class ListBokImports extends ListRecords
{
    protected static string $resource = BokImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('استيراد ملف BOK جديد')
                ->icon('heroicon-o-plus')
                ->modalWidth(MaxWidth::SevenExtraLarge),
                
            Action::make('bulk_import')
                ->label('استيراد متعدد')
                ->icon('heroicon-o-folder-plus')
                ->color('warning')
                ->action(function () {
                    // يمكن تطوير هذه الوظيفة لاحقاً للاستيراد المتعدد
                    \Filament\Notifications\Notification::make()
                        ->title('قريباً')
                        ->body('ستتوفر ميزة الاستيراد المتعدد قريباً')
                        ->info()
                        ->send();
                }),
                
            Action::make('import_guide')
                ->label('دليل الاستيراد')
                ->icon('heroicon-o-question-mark-circle')
                ->color('gray')
                ->url('#')
                ->openUrlInNewTab()
        ];
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            // يمكن إضافة ويدجت إحصائيات هنا
        ];
    }
    
    public function getTitle(): string
    {
        return 'استيراد ملفات BOK';
    }
    
    public function getHeading(): string
    {
        return 'إدارة استيراد ملفات المكتبة الشاملة';
    }
    
    public function getSubheading(): ?string
    {
        return 'تحويل ملفات .bok من المكتبة الشاملة إلى قاعدة البيانات';
    }
}