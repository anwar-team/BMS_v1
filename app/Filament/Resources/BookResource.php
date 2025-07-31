<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookResource\Pages;
use App\Filament\Resources\BookResource\RelationManagers;
use App\Models\Book;
use App\Models\Author;
use App\Models\Publisher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Book Management';
    
    protected static ?string $navigationLabel = 'الكتب';
    
    protected static ?string $modelLabel = 'كتاب';
    
    protected static ?string $pluralModelLabel = 'الكتب';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('عنوان الكتاب')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('author_id')
                    ->label('المؤلف')
                    ->relationship('author', 'full_name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('publisher_id')
                    ->label('الناشر')
                    ->relationship('publisher', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('isbn')
                    ->label('رقم ISBN')
                    ->maxLength(20),
                Forms\Components\DatePicker::make('publication_date')
                    ->label('تاريخ النشر'),
                Forms\Components\TextInput::make('pages')
                    ->label('عدد الصفحات')
                    ->numeric()
                    ->minValue(1),
                Forms\Components\Select::make('language')
                    ->label('اللغة')
                    ->options([
                        'العربية' => 'العربية',
                        'الإنجليزية' => 'الإنجليزية',
                        'الفرنسية' => 'الفرنسية',
                        'الألمانية' => 'الألمانية',
                        'أخرى' => 'أخرى',
                    ])
                    ->default('العربية'),
                Forms\Components\Textarea::make('description')
                    ->label('وصف الكتاب')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('price')
                    ->label('السعر')
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\TextInput::make('stock_quantity')
                    ->label('الكمية المتوفرة')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان الكتاب')
                    ->searchable(),
                Tables\Columns\TextColumn::make('author.full_name')
                    ->label('المؤلف')
                    ->searchable(),
                Tables\Columns\TextColumn::make('publisher.name')
                    ->label('الناشر')
                    ->searchable(),
                Tables\Columns\TextColumn::make('isbn')
                    ->label('رقم ISBN')
                    ->searchable(),
                Tables\Columns\TextColumn::make('publication_date')
                    ->label('تاريخ النشر')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pages')
                    ->label('عدد الصفحات')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('language')
                    ->label('اللغة'),
                Tables\Columns\TextColumn::make('price')
                    ->label('السعر')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('الكمية المتوفرة')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListBooks::route('/'),
            'create' => Pages\CreateBook::route('/create'),
            'edit' => Pages\EditBook::route('/{record}/edit'),
        ];
    }
}
