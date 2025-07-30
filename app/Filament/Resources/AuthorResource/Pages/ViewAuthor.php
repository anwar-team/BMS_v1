<?php

namespace App\Filament\Resources\AuthorResource\Pages;

use App\Filament\Resources\AuthorResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;

class ViewAuthor extends ViewRecord
{
    protected static string $resource = AuthorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('تعديل المؤلف'),
        ];
    }
    
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('المعلومات الشخصية')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('full_name')
                                    ->label('الاسم الكامل')
                                    ->size('lg')
                                    ->weight('bold'),
                                    

                                TextEntry::make('madhhab')
                                    ->label('المذهب')
                                    ->badge()
                                    ->color('success'),
                                    
                                TextEntry::make('books_count')
                                    ->label('عدد الكتب')
                                    ->state(fn ($record) => $record->books()->count())
                                    ->badge()
                                    ->color('warning'),
                            ]),
                    ]),
                    
                Section::make('التواريخ المهمة')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('birth_date')
                                    ->label('تاريخ الولادة')
                                    ->date('d/m/Y')
                                    ->placeholder('غير محدد'),
                                    
                                TextEntry::make('death_date')
                                    ->label('تاريخ الوفاة')
                                    ->date('d/m/Y')
                                    ->placeholder('على قيد الحياة'),
                            ]),
                    ]),
                    
                Section::make('السيرة الذاتية')
                    ->schema([
                        TextEntry::make('biography')
                            ->label('السيرة الذاتية')
                            ->prose()
                            ->columnSpanFull()
                            ->placeholder('لا توجد سيرة ذاتية مُدخلة'),
                    ])
                    ->visible(fn ($record) => !empty($record->biography)),
                    
                Section::make('الكتب المؤلفة')
                    ->schema([
                        TextEntry::make('books.title')
                            ->label('قائمة الكتب')
                            ->listWithLineBreaks()
                            ->bulleted(),
                    ])
                    ->visible(fn ($record) => $record->books()->exists()),
                    
                Section::make('معلومات إضافية')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('تاريخ الإضافة')
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
