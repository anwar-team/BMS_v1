<?php

namespace App\Filament\Widgets;

use App\Models\Blog\Post;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestBlogPostsTableWidget extends BaseWidget
{
    protected static ?string $heading = 'أحدث مقالات المدونة';
    protected static ?int $sort = 9;
    protected int | string | array $columnSpan = 'full';

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
                    ->label('عنوان المقال')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('التصنيف')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('creator.first_name')
                    ->label('الكاتب')
                    ->formatStateUsing(fn ($record) => $record->creator ? $record->creator->first_name . ' ' . $record->creator->last_name : 'غير محدد')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn ($state): string => match ($state?->value ?? $state) {
                        'published' => 'success',
                        'draft' => 'warning',
                        'archived' => 'danger',
                        'pending' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state): string => match ($state?->value ?? $state) {
                        'published' => 'منشور',
                        'draft' => 'مسودة',
                        'archived' => 'مؤرشف',
                        'pending' => 'قيد المراجعة',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('views_count')
                    ->label('المشاهدات')
                    ->numeric()
                    ->sortable()
                    ->default(0),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ النشر')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->paginated(false);
    }
}