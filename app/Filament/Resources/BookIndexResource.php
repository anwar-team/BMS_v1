<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\BookManagement;

use App\Filament\Resources\BookIndexResource\Pages;
use App\Filament\Resources\BookIndexResource\RelationManagers;
use App\Models\BookIndex;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookIndexResource extends Resource
{
    protected static ?string $model = BookIndex::class;
    protected static ?string $cluster = BookManagement::class;
    protected static ?int $navigationSort = 6;

    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass';
    protected static ?string $navigationGroup = 'Book Management';
    
    protected static ?string $navigationLabel = 'فهرس الكتب';
    
    protected static ?string $modelLabel = 'فهرس';
    
    protected static ?string $pluralModelLabel = 'فهرس الكتب';

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
                Forms\Components\Select::make('page_id')
                    ->label('الصفحة')
                    ->relationship('page', 'page_number')
                    ->nullable(),
                Forms\Components\TextInput::make('keyword')
                    ->label('الكلمة المفتاحية')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('normalized_keyword')
                    ->label('الكلمة المفتاحية المعيارية')
                    ->maxLength(255),
                Forms\Components\TextInput::make('page_number')
                    ->label('رقم الصفحة')
                    ->numeric(),
                Forms\Components\Textarea::make('context')
                    ->label('السياق')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('position_in_page')
                    ->label('الموقع في الصفحة')
                    ->numeric(),
                Forms\Components\TextInput::make('frequency')
                    ->label('التكرار')
                    ->numeric(),
                Forms\Components\Select::make('index_type')
                    ->label('نوع الفهرس')
                    ->options([
                        'keyword' => 'كلمة مفتاحية',
                        'topic' => 'موضوع',
                        'person' => 'شخص',
                        'place' => 'مكان',
                    ])
                    ->nullable(),
                Forms\Components\TextInput::make('relevance_score')
                    ->label('درجة الصلة')
                    ->numeric()
                    ->step(0.01),
                Forms\Components\Toggle::make('is_auto_generated')
                    ->label('مُولد تلقائياً'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('book.title')
                    ->label('الكتاب')
                    ->searchable(),
                Tables\Columns\TextColumn::make('keyword')
                    ->label('الكلمة المفتاحية')
                    ->searchable(),
                Tables\Columns\TextColumn::make('page_number')
                    ->label('رقم الصفحة')
                    ->sortable(),
                Tables\Columns\TextColumn::make('index_type')
                    ->label('نوع الفهرس')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'keyword' => 'info',
                        'topic' => 'success',
                        'person' => 'warning',
                        'place' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('frequency')
                    ->label('التكرار')
                    ->sortable(),
                Tables\Columns\TextColumn::make('relevance_score')
                    ->label('درجة الصلة')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_auto_generated')
                    ->label('مُولد تلقائياً')
                    ->boolean(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookIndices::route('/'),
            'create' => Pages\CreateBookIndex::route('/create'),
            'edit' => Pages\EditBookIndex::route('/{record}/edit'),
        ];
    }
}
