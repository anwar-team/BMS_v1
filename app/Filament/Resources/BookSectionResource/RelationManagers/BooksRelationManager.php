<?php

namespace App\Filament\Resources\BookSectionResource\RelationManagers;

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
                Forms\Components\Select::make('publisher_id')
                    ->label('Publisher')
                    ->relationship('publisher', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->label('Publisher Name')
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email(),
                        Forms\Components\TextInput::make('phone')
                            ->label('Phone Number'),
                        Forms\Components\Textarea::make('address')
                            ->label('Address'),
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
                Forms\Components\Toggle::make('is_published')
                    ->label('Published')
                    ->default(true),
                Forms\Components\Toggle::make('is_featured')
                    ->label('Featured')
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
                Tables\Columns\TextColumn::make('publisher.name')
                    ->label('Publisher')
                    ->searchable()
                    ->sortable()
                    ->limit(25)
                    ->placeholder('Not specified'),
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
                    ->color('info'),
                Tables\Columns\TextColumn::make('chapters_count')
                    ->label('Chapters')
                    ->counts('chapters')
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('pages_count')
                    ->label('Pages')
                    ->counts('pages')
                    ->sortable()
                    ->badge()
                    ->color('secondary'),
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
                Tables\Filters\SelectFilter::make('publisher_id')
                    ->label('Publisher')
                    ->relationship('publisher', 'name')
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
                Tables\Filters\Filter::make('has_isbn')
                    ->label('Has ISBN Number')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('isbn')->where('isbn', '!=', '')),
                Tables\Filters\Filter::make('has_cover')
                    ->label('Has Cover Image')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('cover_image')),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Book')
                    ->mutateFormDataUsing(function (array $data, RelationManager $livewire): array {
                        // Set book_section_id from the owner record
                        $data['book_section_id'] = $livewire->getOwnerRecord()->id;
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
                    ->modalDescription('Are you sure you want to delete this book? All associated volumes, chapters, and pages will be deleted.')
                    ->modalSubmitActionLabel('Delete'),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function ($record, RelationManager $livewire) {
                        $newBook = $record->replicate();
                        $newBook->title = $record->title . ' (Copy)';
                        $newBook->isbn = null; // Clear ISBN for duplicate
                        $newBook->is_published = false;
                        $newBook->is_featured = false;
                        $newBook->book_section_id = $livewire->getOwnerRecord()->id;
                        $newBook->save();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Duplicate Book')
                    ->modalDescription('Do you want to create a copy of this book?')
                    ->modalSubmitActionLabel('Duplicate'),
                Tables\Actions\Action::make('toggle_featured')
                    ->label(fn ($record) => $record->is_featured ? 'Unfeature' : 'Feature')
                    ->icon(fn ($record) => $record->is_featured ? 'heroicon-o-star' : 'heroicon-o-star')
                    ->color(fn ($record) => $record->is_featured ? 'warning' : 'gray')
                    ->action(fn ($record) => $record->update(['is_featured' => !$record->is_featured]))
                    ->requiresConfirmation()
                    ->modalHeading(fn ($record) => $record->is_featured ? 'Unfeature Book' : 'Feature Book')
                    ->modalDescription(fn ($record) => $record->is_featured ? 'Do you want to unfeature this book?' : 'Do you want to feature this book?')
                    ->modalSubmitActionLabel(fn ($record) => $record->is_featured ? 'Unfeature' : 'Feature'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Delete Selected')
                        ->requiresConfirmation()
                        ->modalHeading('Delete Selected Books')
                        ->modalDescription('Are you sure you want to delete the selected books? All associated content will be deleted.')
                        ->modalSubmitActionLabel('Delete'),
                    Tables\Actions\BulkAction::make('publish')
                        ->label('Publish Selected')
                        ->icon('heroicon-o-eye')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update(['is_published' => true]));
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Publish Books')
                        ->modalDescription('Do you want to publish the selected books?')
                        ->modalSubmitActionLabel('Publish'),
                    Tables\Actions\BulkAction::make('unpublish')
                        ->label('Unpublish Selected')
                        ->icon('heroicon-o-eye-slash')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update(['is_published' => false]));
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Unpublish Books')
                        ->modalDescription('Do you want to unpublish the selected books?')
                        ->modalSubmitActionLabel('Unpublish'),
                    Tables\Actions\BulkAction::make('feature')
                        ->label('Feature Selected')
                        ->icon('heroicon-o-star')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update(['is_featured' => true]));
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Feature Books')
                        ->modalDescription('Do you want to feature the selected books?')
                        ->modalSubmitActionLabel('Feature'),
                    Tables\Actions\BulkAction::make('unfeature')
                        ->label('Unfeature Selected')
                        ->icon('heroicon-o-star')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update(['is_featured' => false]));
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Unfeature Books')
                        ->modalDescription('Do you want to unfeature the selected books?')
                        ->modalSubmitActionLabel('Unfeature'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}