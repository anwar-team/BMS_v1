<?php

namespace App\Filament\Resources\BokImportResource\Pages;

use App\Filament\Resources\BokImportResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Split;
use Filament\Support\Enums\FontWeight;

class ViewBokImport extends ViewRecord
{
    protected static string $resource = BokImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('تعديل الكتاب'),
            Actions\DeleteAction::make()
                ->label('حذف الكتاب'),
            Actions\Action::make('view_content')
                ->label('عرض المحتوى')
                ->icon('heroicon-o-book-open')
                ->color('info')
                ->url(fn ($record) => route('filament.admin.resources.books.view', $record->id))
                ->openUrlInNewTab(),
            Actions\Action::make('export')
                ->label('تصدير')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function () {
                    \Filament\Notifications\Notification::make()
                        ->title('قريباً')
                        ->body('ستتوفر ميزة التصدير قريباً')
                        ->info()
                        ->send();
                })
        ];
    }
    
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Split::make([
                    Grid::make(2)
                        ->schema([
                            Section::make('معلومات الكتاب')
                                ->schema([
                                    TextEntry::make('title')
                                        ->label('العنوان')
                                        ->weight(FontWeight::Bold)
                                        ->size('lg'),
                                    TextEntry::make('authors.name')
                                        ->label('المؤلف')
                                        ->listWithLineBreaks()
                                        ->bulleted(),
                                    TextEntry::make('description')
                                        ->label('الوصف')
                                        ->markdown()
                                        ->columnSpanFull(),
                                    TextEntry::make('language')
                                        ->label('اللغة')
                                        ->badge()
                                        ->color('info'),
                                    TextEntry::make('status')
                                        ->label('الحالة')
                                        ->badge()
                                        ->color(fn (string $state): string => match ($state) {
                                            'draft' => 'gray',
                                            'published' => 'success',
                                            'archived' => 'warning',
                                        }),
                                ])
                                ->columns(2),
                                
                            Section::make('إحصائيات')
                                ->schema([
                                    TextEntry::make('volumes_count')
                                        ->label('عدد الأجزاء')
                                        ->getStateUsing(fn ($record) => $record->volumes()->count())
                                        ->badge()
                                        ->color('primary'),
                                    TextEntry::make('chapters_count')
                                        ->label('عدد الفصول')
                                        ->getStateUsing(fn ($record) => $record->chapters()->count())
                                        ->badge()
                                        ->color('success'),
                                    TextEntry::make('pages_count')
                                        ->label('عدد الصفحات')
                                        ->getStateUsing(fn ($record) => $record->pages()->count())
                                        ->badge()
                                        ->color('warning'),
                                    TextEntry::make('total_words')
                                        ->label('عدد الكلمات التقريبي')
                                        ->getStateUsing(function ($record) {
                                            $totalContent = $record->pages()->sum('content');
                                            return number_format(str_word_count(strip_tags($totalContent)));
                                        })
                                        ->badge()
                                        ->color('info'),
                                ])
                                ->columns(2),
                        ]),
                        
                    Section::make('تفاصيل الاستيراد')
                        ->schema([
                            TextEntry::make('created_at')
                                ->label('تاريخ الاستيراد')
                                ->dateTime('d/m/Y H:i')
                                ->icon('heroicon-o-calendar'),
                            TextEntry::make('updated_at')
                                ->label('آخر تحديث')
                                ->dateTime('d/m/Y H:i')
                                ->icon('heroicon-o-clock'),
                            TextEntry::make('import_source')
                                ->label('مصدر الاستيراد')
                                ->default('ملف BOK - المكتبة الشاملة')
                                ->icon('heroicon-o-document-arrow-down'),
                        ])
                        ->grow(false),
                ])
                ->from('lg'),
                
                Section::make('هيكل الكتاب')
                    ->schema([
                        TextEntry::make('structure_preview')
                            ->label('')
                            ->getStateUsing(function ($record) {
                                $structure = [];
                                
                                foreach ($record->volumes as $volume) {
                                    $volumeInfo = "📚 {$volume->title} (الصفحات {$volume->page_start}-{$volume->page_end})";
                                    $structure[] = $volumeInfo;
                                    
                                    $chapters = $volume->chapters()->orderBy('chapter_number')->get();
                                    foreach ($chapters->take(5) as $chapter) {
                                        $structure[] = "  📖 {$chapter->title}";
                                    }
                                    
                                    if ($chapters->count() > 5) {
                                        $structure[] = "  ... و " . ($chapters->count() - 5) . " فصول أخرى";
                                    }
                                }
                                
                                return implode("\n", $structure);
                            })
                            ->markdown()
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
                    
                Section::make('معاينة المحتوى')
                    ->schema([
                        TextEntry::make('content_preview')
                            ->label('')
                            ->getStateUsing(function ($record) {
                                $firstPage = $record->pages()->orderBy('page_number')->first();
                                if ($firstPage) {
                                    $content = strip_tags($firstPage->content);
                                    return Str::limit($content, 500) . "\n\n[الصفحة {$firstPage->page_number}]";
                                }
                                return 'لا يوجد محتوى متاح';
                            })
                            ->markdown()
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
    
    public function getTitle(): string
    {
        return 'عرض الكتاب المستورد';
    }
}