<?php

namespace App\Filament\Resources\BookResource\Pages;

use App\Filament\Resources\BookResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;

class ViewBook extends ViewRecord
{
    protected static string $resource = BookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('تعديل الكتاب'),
        ];
    }
    
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('معلومات الكتاب الأساسية')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                ImageEntry::make('cover_image')
                                    ->label('صورة الغلاف')
                                    ->height(200)
                                    ->defaultImageUrl('/images/no-cover.png'),
                                    
                                Grid::make(1)
                                    ->schema([
                                        TextEntry::make('title')
                                            ->label('عنوان الكتاب')
                                            ->size('lg')
                                            ->weight('bold'),
                                            
                                        TextEntry::make('authors.full_name')
                                            ->label('المؤلفين')
                                            ->badge()
                                            ->separator(','),
                                            
                                        TextEntry::make('bookSection.name')
                                            ->label('القسم')
                                            ->badge()
                                            ->color('success'),
                                            
                                        TextEntry::make('published_year')
                                            ->label('سنة النشر')
                                            ->badge()
                                            ->color('info'),
                                    ])
                                    ->columnSpan(2),
                            ]),
                            
                        TextEntry::make('description')
                            ->label('وصف الكتاب')
                            ->prose()
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
                    
                Section::make('تفاصيل النشر')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('publisher')
                                    ->label('دار النشر'),
                                    
                                TextEntry::make('volumes_count')
                                    ->label('عدد المجلدات')
                                    ->badge()
                                    ->color('warning'),
                                    
                                TextEntry::make('pages_count')
                                    ->label('عدد الصفحات')
                                    ->formatStateUsing(fn ($state) => $state ? number_format($state) : '-'),
                            ]),
                            
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('status')
                                    ->label('الحالة')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'draft' => 'gray',
                                        'published' => 'success',
                                        'archived' => 'warning',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'draft' => 'مسودة',
                                        'published' => 'منشور',
                                        'archived' => 'مؤرشف',
                                        default => $state,
                                    }),
                                    
                                TextEntry::make('visibility')
                                    ->label('الرؤية')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'public' => 'success',
                                        'private' => 'danger',
                                        'restricted' => 'warning',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'public' => 'عام',
                                        'private' => 'خاص',
                                        'restricted' => 'محدود',
                                        default => $state,
                                    }),
                                    
                                TextEntry::make('source_url')
                                    ->label('رابط المصدر')
                                    ->url()
                                    ->openUrlInNewTab(),
                            ]),
                    ]),
                    
                Section::make('هيكل الكتاب')
                    ->schema([
                        RepeatableEntry::make('volumes')
                            ->label('المجلدات')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('number')
                                            ->label('رقم المجلد')
                                            ->badge(),
                                            
                                        TextEntry::make('title')
                                            ->label('عنوان المجلد'),
                                            
                                        TextEntry::make('page_start')
                                            ->label('من صفحة'),
                                            
                                        TextEntry::make('page_end')
                                            ->label('إلى صفحة'),
                                    ]),
                                    
                                RepeatableEntry::make('chapters')
                                    ->label('الفصول')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextEntry::make('chapter_number')
                                                    ->label('رقم الفصل')
                                                    ->badge()
                                                    ->color('info'),
                                                    
                                                TextEntry::make('title')
                                                    ->label('عنوان الفصل')
                                                    ->weight('medium'),
                                                    
                                                TextEntry::make('chapter_type')
                                                    ->label('نوع الفصل')
                                                    ->badge()
                                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                                        'chapter' => 'فصل',
                                                        'section' => 'قسم',
                                                        'part' => 'جزء',
                                                        default => $state,
                                                    }),
                                            ]),
                                    ])
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->collapsed()
                    ->visible(fn ($record) => $record->volumes()->exists()),
                    
                Section::make('معلومات إضافية')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('تاريخ الإنشاء')
                                    ->dateTime('d/m/Y H:i'),
                                    
                                TextEntry::make('updated_at')
                                    ->label('آخر تحديث')
                                    ->dateTime('d/m/Y H:i'),
                            ]),
                    ])
                    ->collapsed(),
            ]);
    }
}
