<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Filament\Resources\PageResource\RelationManagers;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;
    protected static ?string $navigationGroup = 'Book Content Management';
    protected static ?int $navigationSort = -7;

    protected static ?string $navigationIcon = 'heroicon-o-document';
    
    protected static ?string $navigationLabel = null;
    protected static ?string $modelLabel = null;
    protected static ?string $pluralModelLabel = null;

    public static function getNavigationLabel(): string
    {
        return __('resource.page.pages');
    }

    public static function getModelLabel(): string
    {
        return __('resource.page.page');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resource.page.pages');
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
                Forms\Components\Select::make('chapter_id')
                    ->label('Chapter')
                    ->relationship('chapter', 'title')
                    ->nullable(),
                Forms\Components\TextInput::make('page_number')
                    ->label('Page Number')
                    ->numeric()
                    ->required(),
                Forms\Components\Textarea::make('content')
                    ->label('Content')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('book.title')
                    ->label('Book')
                    ->searchable(),
                Tables\Columns\TextColumn::make('volume.title')
                    ->label('Volume')
                    ->searchable(),
                Tables\Columns\TextColumn::make('chapter.title')
                    ->label('Chapter')
                    ->searchable(),
                Tables\Columns\TextColumn::make('page_number')
                    ->label('Page Number')
                    ->sortable(),
                Tables\Columns\TextColumn::make('content')
                    ->label('Content')
                    ->limit(50)
                    ->searchable(),
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
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
