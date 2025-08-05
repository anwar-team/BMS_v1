<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\BookManagement;

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
    protected static ?string $cluster = BookManagement::class;
    protected static ?int $navigationSort = 4;

    protected static ?string $navigationIcon = 'heroicon-o-document';
    protected static ?string $navigationGroup = 'Book Management';
    
    protected static ?string $navigationLabel = 'الصفحات';
    
    protected static ?string $modelLabel = 'صفحة';
    
    protected static ?string $pluralModelLabel = 'الصفحات';

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
                Forms\Components\Select::make('chapter_id')
                    ->label('الفصل')
                    ->relationship('chapter', 'title')
                    ->nullable(),
                Forms\Components\TextInput::make('page_number')
                    ->label('رقم الصفحة')
                    ->numeric()
                    ->required(),
                Forms\Components\Textarea::make('content')
                    ->label('المحتوى')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('book.title')
                    ->label('الكتاب')
                    ->searchable(),
                Tables\Columns\TextColumn::make('volume.title')
                    ->label('المجلد')
                    ->searchable(),
                Tables\Columns\TextColumn::make('chapter.title')
                    ->label('الفصل')
                    ->searchable(),
                Tables\Columns\TextColumn::make('page_number')
                    ->label('رقم الصفحة')
                    ->sortable(),
                Tables\Columns\TextColumn::make('content')
                    ->label('المحتوى')
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
            PageResource\RelationManagers\FootnotesRelationManager::class,
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
