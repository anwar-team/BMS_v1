<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\BookManagement;

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
    protected static ?string $cluster = BookManagement::class;
    protected static ?int $navigationSort = 3;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Book Management';
    
    protected static ?string $navigationLabel = 'الفصول';
    
    protected static ?string $modelLabel = 'فصل';
    
    protected static ?string $pluralModelLabel = 'الفصول';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('book_id')
                    ->label('الكتاب')
                    ->relationship('book', 'title')
                    ->required(),
                Forms\Components\Select::make('volume_id')
                    ->label('المجلد')
                    ->relationship('volume', 'title')
                    ->nullable(),
                Forms\Components\TextInput::make('title')
                    ->label('عنوان الفصل')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('parent_id')
                    ->label('الفصل الأب')
                    ->relationship('parent', 'title')
                    ->nullable(),
                Forms\Components\TextInput::make('order')
                    ->label('الترتيب')
                    ->numeric()
                    ->nullable(),
                Forms\Components\TextInput::make('page_start')
                    ->label('الصفحة الأولى')
                    ->numeric()
                    ->nullable(),
                Forms\Components\TextInput::make('page_end')
                    ->label('الصفحة الأخيرة')
                    ->numeric()
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('book.title')
                    ->label('الكتاب')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان الفصل')
                    ->searchable(),
                Tables\Columns\TextColumn::make('parent.title')
                    ->label('الفصل الأب')
                    ->searchable(),
                Tables\Columns\TextColumn::make('order')
                    ->label('الترتيب')
                    ->sortable(),
                Tables\Columns\TextColumn::make('page_start')
                    ->label('الصفحة الأولى')
                    ->sortable(),
                Tables\Columns\TextColumn::make('page_end')
                    ->label('الصفحة الأخيرة')
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
