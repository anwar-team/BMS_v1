<?php

namespace App\Filament\Resources\BookSectionResource\Widgets;

use App\Models\BookSection;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PopularSectionsWidget extends BaseWidget
{
    protected static ?string $heading = 'أقسام الكتب الأكثر شعبية';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                BookSection::query()
                    ->withCount('books')
                    ->having('books_count', '>', 0)
                    ->orderBy('books_count', 'desc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('الصورة')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&background=118AB2&color=fff')
                    ->size(40),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم القسم')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                    
                Tables\Columns\TextColumn::make('books_count')
                    ->label('عدد الكتب')
                    ->badge()
                    ->color('success')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(50)
                    ->tooltip(function (BookSection $record): ?string {
                        return $record->description;
                    }),
                    
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('ترتيب العرض')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('عرض')
                    ->icon('heroicon-o-eye')
                    ->url(fn (BookSection $record): string => route('filament.admin.resources.book-sections.view', $record))
                    ->openUrlInNewTab(),
            ])
            ->paginated(false);
    }
}