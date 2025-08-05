<?php

namespace App\Filament\Resources\AuthorResource\Widgets;

use App\Models\Author;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentAuthorsWidget extends BaseWidget
{
    protected static ?string $heading = 'المؤلفين المضافين مؤخراً';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 4;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Author::query()
                    ->latest()
                    ->limit(8)
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
                    ->weight('bold'),
                    
                Tables\Columns\TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->icon('heroicon-o-envelope')
                    ->copyable(),
                    
                Tables\Columns\TextColumn::make('specialization')
                    ->label('التخصص')
                    ->badge()
                    ->color('info')
                    ->limit(25),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->since(),
                    
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