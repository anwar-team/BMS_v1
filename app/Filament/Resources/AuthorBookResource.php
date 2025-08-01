<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuthorBookResource\Pages;
use App\Filament\Resources\AuthorBookResource\RelationManagers;
use App\Models\AuthorBook;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AuthorBookResource extends Resource
{
    protected static ?string $model = AuthorBook::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    protected static ?string $navigationGroup = 'إدارة المحتوى';
    
    protected static ?string $navigationLabel = 'مؤلفو الكتب';
    
    protected static ?string $modelLabel = 'مؤلف كتاب';
    
    protected static ?string $pluralModelLabel = 'مؤلفو الكتب';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('book_id')
                    ->label('الكتاب')
                    ->relationship('book', 'title')
                    ->required()
                    ->searchable()
                    ->preload(),
                    
                Forms\Components\Select::make('author_id')
                    ->label('المؤلف')
                    ->relationship('author', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                    
                Forms\Components\Select::make('role')
                    ->label('الدور')
                    ->options([
                        'author' => 'مؤلف',
                        'co_author' => 'مؤلف مشارك',
                        'editor' => 'محرر',
                        'translator' => 'مترجم',
                        'reviewer' => 'مراجع',
                        'commentator' => 'معلق',
                    ])
                    ->required()
                    ->default('author'),
                    
                Forms\Components\Toggle::make('is_main')
                    ->label('مؤلف رئيسي')
                    ->default(false),
                    
                Forms\Components\TextInput::make('display_order')
                    ->label('ترتيب العرض')
                    ->numeric()
                    ->default(1)
                    ->minValue(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('book.title')
                    ->label('الكتاب')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('author.name')
                    ->label('المؤلف')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('role_arabic')
                    ->label('الدور')
                    ->sortable(),
                    
                Tables\Columns\IconColumn::make('is_main')
                    ->label('مؤلف رئيسي')
                    ->boolean()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('display_order')
                    ->label('ترتيب العرض')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('الدور')
                    ->options([
                        'author' => 'مؤلف',
                        'co_author' => 'مؤلف مشارك',
                        'editor' => 'محرر',
                        'translator' => 'مترجم',
                        'reviewer' => 'مراجع',
                        'commentator' => 'معلق',
                    ]),
                    
                Tables\Filters\TernaryFilter::make('is_main')
                    ->label('مؤلف رئيسي'),
                    
                Tables\Filters\SelectFilter::make('book_id')
                    ->label('الكتاب')
                    ->relationship('book', 'title')
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\SelectFilter::make('author_id')
                    ->label('المؤلف')
                    ->relationship('author', 'name')
                    ->searchable()
                    ->preload(),
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
            'index' => Pages\ListAuthorBooks::route('/'),
            'create' => Pages\CreateAuthorBook::route('/create'),
            'edit' => Pages\EditAuthorBook::route('/{record}/edit'),
        ];
    }
}
