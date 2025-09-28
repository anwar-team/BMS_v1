<?php

namespace App\Filament\Widgets;

use App\Models\Book;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class LatestBooksTableWidget extends BaseWidget
{
    protected static ?string $heading = 'Latest Added Books';
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 'full';
    
    // Disable automatic refresh
    protected static ?string $pollingInterval = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Book::query()
                    ->with(['authorBooks.author', 'bookSection'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Book Title')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('main_author')
                    ->label('Author')
                    ->getStateUsing(function ($record) {
                        $mainAuthor = $record->authorBooks
                            ->where('is_main', true)
                            ->first();
                        return $mainAuthor?->author?->full_name ?? 
                               $record->authorBooks->first()?->author?->full_name ?? 'Not specified';
                    })
                    ->badge()
                    ->limit(20),

                Tables\Columns\TextColumn::make('bookSection.name')
                    ->label('Section')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'draft' => 'warning',
                        'archived' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'published' => 'Published',
                        'draft' => 'Draft',
                        'archived' => 'Archived',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date Added')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->paginated(false);
    }
}