<?php

namespace App\Filament\Resources\PublisherResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BooksRelationManager extends RelationManager
{
    protected static string $relationship = 'books';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Book Title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('subtitle')
                    ->label('Subtitle')
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('Book Description')
                    ->rows(4)
                    ->columnSpanFull(),
                Forms\Components\Select::make('book_section_id')
                    ->label('Book Section')
                    ->relationship('bookSection', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->label('Section Name')
                            ->required(),
                        Forms\Components\Textarea::make('description')
                            ->label('Section Description'),
                    ]),
                Forms\Components\TextInput::make('isbn')
                    ->label('ISBN Number')
                    ->maxLength(20)
                    ->unique(ignoreRecord: true),
                Forms\Components\DatePicker::make('publication_date')
                    ->label('Publication Date'),
                Forms\Components\TextInput::make('publication_year')
                    ->label('Publication Year')
                    ->numeric()
                    ->minValue(1000)
                    ->maxValue(date('Y') + 10),
                Forms\Components\TextInput::make('edition')
                    ->label('Edition')
                    ->maxLength(50),
                Forms\Components\TextInput::make('pages')
                    ->label('Number of Pages')
                    ->numeric()
                    ->minValue(1),
                Forms\Components\Select::make('language')
                    ->label('Language')
                    ->options([
                        'ar' => 'Arabic',
                        'en' => 'English',
                        'fr' => 'French',
                        'es' => 'Spanish',
                        'de' => 'German',
                        'tr' => 'Turkish',
                        'fa' => 'Persian',
                        'ur' => 'Urdu',
                    ])
                    ->default('ar'),
                Forms\Components\TextInput::make('price')
                    ->label('Price')
                    ->numeric()
                    ->prefix('$')
                    ->minValue(0),
                Forms\Components\Select::make('currency')
                    ->label('Currency')
                    ->options([
                        'USD' => 'US Dollar',
                        'EUR' => 'Euro',
                        'SAR' => 'Saudi Riyal',
                        'AED' => 'UAE Dirham',
                        'EGP' => 'Egyptian Pound',
                        'JOD' => 'Jordanian Dinar',
                        'KWD' => 'Kuwaiti Dinar',
                        'QAR' => 'Qatari Riyal',
                        'BHD' => 'Bahraini Dinar',
                        'OMR' => 'Omani Riyal',
                    ])
                    ->default('USD'),
                Forms\Components\Toggle::make('is_published')
                    ->label('Published')
                    ->default(true),
                Forms\Components\Toggle::make('is_featured')
                    ->label('Featured')
                    ->default(false),
                Forms\Components\Toggle::make('is_bestseller')
                    ->label('Bestseller')
                    ->default(false),
                Forms\Components\FileUpload::make('cover_image')
                    ->label('Cover Image')
                    ->image()
                    ->directory('book-covers')
                    ->visibility('public')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('notes')
                    ->label('Notes')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->label('Cover')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-book-cover.png'))
                    ->size(50),
                Tables\Columns\TextColumn::make('title')
                    ->label('Book Title')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->tooltip(function ($record) {
                        return $record->subtitle ? $record->title . ' - ' . $record->subtitle : $record->title;
                    }),
                Tables\Columns\TextColumn::make('subtitle')
                    ->label('Subtitle')
                    ->searchable()
                    ->limit(30)
                    ->toggleable()
                    ->placeholder('No subtitle'),
                Tables\Columns\TextColumn::make('bookSection.name')
                    ->label('Section')
                    ->searchable()
                    ->sortable()
                    ->limit(25)
                    ->placeholder('Not specified')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('authors_count')
                    ->label('Authors')
                    ->counts('authors')
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state === 0 => 'gray',
                        $state === 1 => 'success',
                        $state <= 3 => 'warning',
                        default => 'danger',
                    }),
                Tables\Columns\TextColumn::make('volumes_count')
                    ->label('Volumes')
                    ->counts('volumes')
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('chapters_count')
                    ->label('Chapters')
                    ->counts('chapters')
                    ->sortable()
                    ->badge()
                    ->color('secondary'),
                Tables\Columns\TextColumn::make('pages_count')
                    ->label('Pages')
                    ->counts('pages')
                    ->sortable()
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('publication_year')
                    ->label('Publication Year')
                    ->sortable()
                    ->placeholder('Not specified'),
                Tables\Columns\TextColumn::make('isbn')
                    ->label('ISBN')
                    ->searchable()
                    ->copyable()
                    ->placeholder('Not specified')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->money(fn ($record) => $record->currency ?? 'USD')
                    ->sortable()
                    ->placeholder('Not specified')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('language')
                    ->label('Language')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'ar' => 'Arabic',
                        'en' => 'English',
                        'fr' => 'French',
                        'es' => 'Spanish',
                        'de' => 'German',
                        'tr' => 'Turkish',
                        'fa' => 'Persian',
                        'ur' => 'Urdu',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'ar' => 'success',
                        'en' => 'info',
                        'fr' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_bestseller')
                    ->label('Bestseller')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Update')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('book_section_id')
                    ->label('Book Section')
                    ->relationship('bookSection', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('language')
                    ->label('Language')
                    ->options([
                        'ar' => 'Arabic',
                        'en' => 'English',
                        'fr' => 'French',
                        'es' => 'Spanish',
                        'de' => 'German',
                        'tr' => 'Turkish',
                        'fa' => 'Persian',
                        'ur' => 'Urdu',
                    ])
                    ->multiple(),
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Published'),
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured'),
                Tables\Filters\TernaryFilter::make('is_bestseller')
                    ->label('Bestseller'),
                Tables\Filters\Filter::make('publication_year')
                    ->label('Publication Year')
                    ->form([
                        Forms\Components\TextInput::make('from')
                            ->label('From Year')
                            ->numeric(),
                        Forms\Components\TextInput::make('to')
                            ->label('To Year')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $year): Builder => $query->where('publication_year', '>=', $year),
                            )
                            ->when(
                                $data['to'],
                                fn (Builder $query, $year): Builder => $query->where('publication_year', '<=', $year),
                            );
                    }),
                Tables\Filters\Filter::make('price_range')
                    ->label('Price Range')
                    ->form([
                        Forms\Components\TextInput::make('min_price')
                            ->label('Minimum Price')
                            ->numeric(),
                        Forms\Components\TextInput::make('max_price')
                            ->label('Maximum Price')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_price'],
                                fn (Builder $query, $price): Builder => $query->where('price', '>=', $price),
                            )
                            ->when(
                                $data['max_price'],
                                fn (Builder $query, $price): Builder => $query->where('price', '<=', $price),
                            );
                    }),
                Tables\Filters\TernaryFilter::make('has_isbn')
                    ->label('Has ISBN')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('isbn'),
                        false: fn (Builder $query) => $query->whereNull('isbn'),
                    ),
                Tables\Filters\TernaryFilter::make('has_cover_image')
                    ->label('Has Cover Image')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('cover_image'),
                        false: fn (Builder $query) => $query->whereNull('cover_image'),
                    ),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Book')
                    ->icon('heroicon-o-plus')
                    ->mutateFormDataUsing(function (array $data, RelationManager $livewire): array {
                        // Set publisher_id from the owner record
                        $data['publisher_id'] = $livewire->getOwnerRecord()->id;
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('View'),
                Tables\Actions\EditAction::make()
                    ->label('Edit'),
                Tables\Actions\DeleteAction::make()
                    ->label('Delete')
                    ->requiresConfirmation()
                    ->modalHeading('Delete Book')
                    ->modalDescription('Are you sure you want to delete this book? This action cannot be undone.')
                    ->modalSubmitActionLabel('Delete'),
                Tables\Actions\ReplicateAction::make()
                    ->label('Duplicate')
                    ->modalHeading('Duplicate Book')
                    ->modalDescription('This will create a copy of the book with all its data.')
                    ->modalSubmitActionLabel('Duplicate'),
                Tables\Actions\Action::make('toggle_featured')
                    ->label(fn ($record) => $record->is_featured ? 'Unfeature' : 'Feature')
                    ->icon('heroicon-o-star')
                    ->color(fn ($record) => $record->is_featured ? 'warning' : 'success')
                    ->action(fn ($record) => $record->update(['is_featured' => !$record->is_featured]))
                    ->requiresConfirmation()
                    ->modalHeading(fn ($record) => $record->is_featured ? 'Unfeature Book' : 'Feature Book')
                    ->modalDescription(fn ($record) => $record->is_featured ? 'Are you sure you want to remove this book from featured books?' : 'Are you sure you want to feature this book?')
                    ->modalSubmitActionLabel(fn ($record) => $record->is_featured ? 'Unfeature' : 'Feature'),
                Tables\Actions\Action::make('toggle_bestseller')
                    ->label(fn ($record) => $record->is_bestseller ? 'Unmark Bestseller' : 'Mark Bestseller')
                    ->icon('heroicon-o-fire')
                    ->color(fn ($record) => $record->is_bestseller ? 'danger' : 'success')
                    ->action(fn ($record) => $record->update(['is_bestseller' => !$record->is_bestseller]))
                    ->requiresConfirmation()
                    ->modalHeading(fn ($record) => $record->is_bestseller ? 'Unmark as Bestseller' : 'Mark as Bestseller')
                    ->modalDescription(fn ($record) => $record->is_bestseller ? 'Are you sure you want to remove this book from bestsellers?' : 'Are you sure you want to mark this book as a bestseller?')
                    ->modalSubmitActionLabel(fn ($record) => $record->is_bestseller ? 'Unmark' : 'Mark'),
                Tables\Actions\Action::make('statistics')
                    ->label('Statistics')
                    ->icon('heroicon-o-chart-bar')
                    ->color('info')
                    ->modalContent(function ($record) {
                        return view('filament.resources.book-resource.pages.book-statistics', [
                            'record' => $record,
                        ]);
                    })
                    ->modalHeading('Book Statistics')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Delete Selected')
                        ->requiresConfirmation()
                        ->modalHeading('Delete Selected Books')
                        ->modalDescription('Are you sure you want to delete the selected books? This action cannot be undone.')
                        ->modalSubmitActionLabel('Delete'),
                    Tables\Actions\BulkAction::make('publish')
                        ->label('Publish Selected')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update(['is_published' => true]));
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Publish Selected Books')
                        ->modalDescription('Are you sure you want to publish the selected books?')
                        ->modalSubmitActionLabel('Publish'),
                    Tables\Actions\BulkAction::make('unpublish')
                        ->label('Unpublish Selected')
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update(['is_published' => false]));
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Unpublish Selected Books')
                        ->modalDescription('Are you sure you want to unpublish the selected books?')
                        ->modalSubmitActionLabel('Unpublish'),
                    Tables\Actions\BulkAction::make('feature')
                        ->label('Feature Selected')
                        ->icon('heroicon-o-star')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update(['is_featured' => true]));
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Feature Selected Books')
                        ->modalDescription('Are you sure you want to feature the selected books?')
                        ->modalSubmitActionLabel('Feature'),
                    Tables\Actions\BulkAction::make('unfeature')
                        ->label('Unfeature Selected')
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update(['is_featured' => false]));
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Unfeature Selected Books')
                        ->modalDescription('Are you sure you want to remove the selected books from featured?')
                        ->modalSubmitActionLabel('Unfeature'),
                    Tables\Actions\BulkAction::make('mark_bestseller')
                        ->label('Mark as Bestseller')
                        ->icon('heroicon-o-fire')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update(['is_bestseller' => true]));
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Mark as Bestseller')
                        ->modalDescription('Are you sure you want to mark the selected books as bestsellers?')
                        ->modalSubmitActionLabel('Mark'),
                    Tables\Actions\BulkAction::make('unmark_bestseller')
                        ->label('Unmark Bestseller')
                        ->icon('heroicon-o-fire')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update(['is_bestseller' => false]));
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Unmark Bestseller')
                        ->modalDescription('Are you sure you want to remove the selected books from bestsellers?')
                        ->modalSubmitActionLabel('Unmark'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}