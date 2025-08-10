<?php

namespace App\Filament\Widgets;

use App\Models\Book;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestBooksTableWidget extends BaseWidget
{
    protected static ?string $heading = 'أحدث الكتب المضافة';
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Book::query()
                    ->with(['authors', 'bookSection'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان الكتاب')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('authors.name')
                    ->label('المؤلف')
                    ->badge()
                    ->separator(', ')
                    ->limit(20),

                Tables\Columns\TextColumn::make('bookSection.name')
                    ->label('القسم')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'draft' => 'warning',
                        'archived' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'published' => 'منشور',
                        'draft' => 'مسودة',
                        'archived' => 'مؤرشف',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->paginated(false);
    }
}