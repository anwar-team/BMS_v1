<?php

namespace App\Filament\Resources\PublisherResource\Widgets;

use App\Models\Publisher;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopPublishersWidget extends BaseWidget
{
    protected static ?string $heading = 'دور النشر الأكثر إنتاجاً';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Publisher::query()
                    ->withCount('books')
                    ->having('books_count', '>', 0)
                    ->orderBy('books_count', 'desc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->label('الشعار')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&background=FF6B35&color=fff')
                    ->size(40),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم دار النشر')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                    
                Tables\Columns\TextColumn::make('books_count')
                    ->label('عدد الكتب')
                    ->badge()
                    ->color('success')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('country')
                    ->label('البلد')
                    ->badge()
                    ->color('info'),
                    
                Tables\Columns\TextColumn::make('established_year')
                    ->label('سنة التأسيس')
                    ->badge()
                    ->color('gray'),
                    
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
                    ->url(fn (Publisher $record): string => route('filament.admin.resources.publishers.view', $record))
                    ->openUrlInNewTab(),
            ])
            ->paginated(false);
    }
}