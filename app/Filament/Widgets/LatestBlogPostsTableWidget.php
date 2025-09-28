<?php

namespace App\Filament\Widgets;

use App\Models\Blog\Post;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestBlogPostsTableWidget extends BaseWidget
{
    protected static ?string $heading = 'Latest Blog Posts';
    protected static ?int $sort = 9;
    protected int | string | array $columnSpan = 'full';
    
    // Disable automatic refresh
    protected static ?string $pollingInterval = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Post::query()
                    ->with(['category', 'creator'])
                    ->latest()
                    ->limit(8)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Article Title')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('creator.first_name')
                    ->label('Author')
                    ->formatStateUsing(fn ($record) => $record->creator ? $record->creator->first_name . ' ' . $record->creator->last_name : 'Not specified')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state): string => match ($state?->value ?? $state) {
                        'published' => 'success',
                        'draft' => 'warning',
                        'archived' => 'danger',
                        'pending' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state): string => match ($state?->value ?? $state) {
                        'published' => 'Published',
                        'draft' => 'Draft',
                        'archived' => 'Archived',
                        'pending' => 'Under Review',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('views_count')
                    ->label('Views')
                    ->numeric()
                    ->sortable()
                    ->default(0),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Publication Date')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->paginated(false);
    }
}