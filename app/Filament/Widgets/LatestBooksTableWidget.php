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
    protected static ?string $heading = null;
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 'full';
    
    // Disable automatic refresh
    protected static ?string $pollingInterval = null;

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('dashboard.widgets.tables.latest_books'))
            ->query(
                Book::query()
                    ->with(['authorBooks.author', 'bookSection'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('library.fields.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('main_author')
                    ->label(__('library.fields.author'))
                    ->getStateUsing(function ($record) {
                        $mainAuthor = $record->authorBooks
                            ->where('is_main', true)
                            ->first();
                        return $mainAuthor?->author?->full_name ?? 
                               $record->authorBooks->first()?->author?->full_name ?? __('library.values.not_specified');
                    })
                    ->badge()
                    ->limit(20),

                Tables\Columns\TextColumn::make('bookSection.name')
                    ->label(__('library.fields.section'))
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('dashboard.fields.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'draft' => 'warning',
                        'archived' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'published' => __('dashboard.status.published'),
                        'draft' => __('dashboard.status.draft'),
                        'archived' => __('dashboard.status.archived'),
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('dashboard.fields.date_added'))
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->paginated(false);
    }
}