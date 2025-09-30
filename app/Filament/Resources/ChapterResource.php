<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChapterResource\Pages;
use App\Filament\Resources\ChapterResource\RelationManagers;
use App\Models\Chapter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ChapterResource extends Resource
{
    protected static ?string $model = Chapter::class;
    protected static ?string $navigationGroup = 'Book Content Management';
    protected static ?int $navigationSort = -8;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationLabel = null;
    protected static ?string $modelLabel = null;
    protected static ?string $pluralModelLabel = null;

    public static function getNavigationLabel(): string
    {
        return __('resource.chapter.chapters');
    }

    public static function getModelLabel(): string
    {
        return __('resource.chapter.chapter');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resource.chapter.chapters');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('book_id')
                    ->label('Book')
                    ->relationship('book', 'title')
                    ->required(),
                Forms\Components\Select::make('volume_id')
                    ->label('Volume')
                    ->relationship('volume', 'title')
                    ->nullable(),
                Forms\Components\TextInput::make('title')
                    ->label('Chapter Title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('parent_id')
                    ->label('Parent Chapter')
                    ->relationship('parent', 'title')
                    ->nullable(),
                Forms\Components\TextInput::make('order')
                    ->label('Order')
                    ->numeric()
                    ->nullable(),
                Forms\Components\TextInput::make('page_start')
                    ->label('Start Page')
                    ->numeric()
                    ->nullable(),
                Forms\Components\TextInput::make('page_end')
                    ->label('End Page')
                    ->numeric()
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('book.title')
                    ->label('Book')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Chapter Title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('parent.title')
                    ->label('Parent Chapter')
                    ->searchable(),
                Tables\Columns\TextColumn::make('order')
                    ->label('Order')
                    ->sortable(),
                Tables\Columns\TextColumn::make('page_start')
                    ->label('Start Page')
                    ->sortable(),
                Tables\Columns\TextColumn::make('page_end')
                    ->label('End Page')
                    ->sortable(),
            ])
            ->filters([
                //
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
            ChapterResource\RelationManagers\PagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChapters::route('/'),
            'create' => Pages\CreateChapter::route('/create'),
            'edit' => Pages\EditChapter::route('/{record}/edit'),
        ];
    }
}
