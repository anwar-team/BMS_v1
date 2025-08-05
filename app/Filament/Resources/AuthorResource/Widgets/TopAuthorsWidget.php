<?php

namespace App\Filament\Resources\AuthorResource\Widgets;

use App\Models\Author;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopAuthorsWidget extends BaseWidget
{
    protected static ?string $heading = 'المؤلفين الأكثر إنتاجاً';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Author::query()
                    ->withCount('books')
                    ->having('books_count', '>', 0)
                    ->orderBy('books_count', 'desc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->label('الصورة')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->full_name) . '&background=0D8ABC&color=fff')
                    ->size(40),
                    
                Tables\Columns\TextColumn::make('full_name')
                    ->label('اسم المؤلف')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                    
                Tables\Columns\TextColumn::make('books_count')
                    ->label('عدد الكتب')
                    ->badge()
                    ->color('success')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('specialization')
                    ->label('التخصص')
                    ->badge()
                    ->color('info')
                    ->limit(30),
                    
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
                    ->url(fn (Author $record): string => route('filament.admin.resources.authors.view', $record))
                    ->openUrlInNewTab(),
            ])
            ->paginated(false);
    }
}