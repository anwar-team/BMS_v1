<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookSectionResource\Pages;
use App\Filament\Resources\BookSectionResource\RelationManagers;
use App\Models\BookSection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookSectionResource extends Resource
{
    protected static ?string $model = BookSection::class;
    protected static ?int $navigationSort = -4;

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?string $navigationIcon = 'heroicon-o-folder';
    
    protected static ?string $navigationLabel = null;
    protected static ?string $modelLabel = null;
    protected static ?string $pluralModelLabel = null;

    public static function getNavigationLabel(): string
    {
        return __('resource.section.sections');
    }

    public static function getModelLabel(): string
    {
        return __('resource.section.section');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resource.section.sections');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Section Name')
                    ->required()
                    ->maxLength(255),
                    
                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->rows(3)
                    ->maxLength(500),
                    
                Forms\Components\Select::make('parent_id')
                    ->label('Parent Section')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload(),
                    
                Forms\Components\TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                    
                Forms\Components\TextInput::make('slug')
                    ->label('Slug')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                    
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Section Name')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Parent Section')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Sort Order')
                    ->sortable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('books_count')
                    ->label('Books Count')
                    ->counts('books')
                    ->sortable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
                    
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Parent Section')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\Filter::make('name')
                    ->form([
                        Forms\Components\TextInput::make('name')
                            ->label('Section Name')
                            ->placeholder('Search for section...'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['name'],
                                fn (Builder $query, $name): Builder => $query->where('name', 'like', "%{$name}%"),
                            );
                    })
                    ->label('Search by Name'),
                    
                Tables\Filters\Filter::make('has_books')
                    ->query(fn (Builder $query): Builder => $query->has('books'))
                    ->label('Has Books'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            BookSectionResource\RelationManagers\BooksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookSections::route('/'),
            'create' => Pages\CreateBookSection::route('/create'),
            'edit' => Pages\EditBookSection::route('/{record}/edit'),
        ];
    }
}
