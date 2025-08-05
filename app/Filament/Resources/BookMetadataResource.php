<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\BookManagement;

use App\Filament\Resources\BookMetadataResource\Pages;
use App\Filament\Resources\BookMetadataResource\RelationManagers;
use App\Models\BookMetadata;
use App\Models\Book;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookMetadataResource extends Resource
{
    protected static ?string $model = BookMetadata::class;
    protected static ?string $cluster = BookManagement::class;
    protected static ?int $navigationSort = 9;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    
    protected static ?string $navigationGroup = 'إدارة المحتوى';
    
    protected static ?string $navigationLabel = 'البيانات الوصفية';
    
    protected static ?string $modelLabel = 'بيانات وصفية';
    
    protected static ?string $pluralModelLabel = 'البيانات الوصفية';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('book_id')
                    ->label('الكتاب')
                    ->relationship('book', 'title')
                    ->searchable()
                    ->preload()
                    ->required(),
                    
                Forms\Components\TextInput::make('metadata_key')
                    ->label('مفتاح البيانات')
                    ->required()
                    ->maxLength(255),
                    
                Forms\Components\Textarea::make('metadata_value')
                    ->label('قيمة البيانات')
                    ->required()
                    ->rows(3),
                    
                Forms\Components\Select::make('metadata_type')
                    ->label('نوع البيانات الوصفية')
                    ->options([
                        'dublin_core' => 'دبلن كور',
                        'islamic_metadata' => 'بيانات إسلامية',
                        'shamela_specific' => 'خاص بالشاملة',
                        'custom' => 'مخصص'
                    ])
                    ->required(),
                    
                Forms\Components\Select::make('data_type')
                    ->label('نوع البيانات')
                    ->options([
                        'string' => 'نص',
                        'number' => 'رقم',
                        'boolean' => 'منطقي',
                        'date' => 'تاريخ',
                        'json' => 'JSON'
                    ])
                    ->default('string')
                    ->required(),
                    
                Forms\Components\Textarea::make('description')
                    ->label('الوصف')
                    ->rows(2)
                    ->maxLength(500),
                    
                Forms\Components\TextInput::make('display_order')
                    ->label('ترتيب العرض')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                    
                Forms\Components\Toggle::make('is_searchable')
                    ->label('قابل للبحث')
                    ->default(true),
                    
                Forms\Components\Toggle::make('is_public')
                    ->label('عام')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('book.title')
                    ->label('الكتاب')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                    
                Tables\Columns\TextColumn::make('metadata_key')
                    ->label('مفتاح البيانات')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('metadata_value')
                    ->label('قيمة البيانات')
                    ->searchable()
                    ->limit(50)
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('metadata_type')
                    ->label('نوع البيانات الوصفية')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'dublin_core' => 'دبلن كور',
                        'islamic_metadata' => 'بيانات إسلامية',
                        'shamela_specific' => 'خاص بالشاملة',
                        'custom' => 'مخصص',
                        default => $state,
                    })
                    ->badge()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('data_type')
                    ->label('نوع البيانات')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'string' => 'نص',
                        'number' => 'رقم',
                        'boolean' => 'منطقي',
                        'date' => 'تاريخ',
                        'json' => 'JSON',
                        default => $state,
                    })
                    ->sortable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('display_order')
                    ->label('ترتيب العرض')
                    ->sortable()
                    ->toggleable(),
                    
                Tables\Columns\IconColumn::make('is_searchable')
                    ->label('قابل للبحث')
                    ->boolean()
                    ->sortable(),
                    
                Tables\Columns\IconColumn::make('is_public')
                    ->label('عام')
                    ->boolean()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('metadata_type')
                    ->label('نوع البيانات الوصفية')
                    ->options([
                        'dublin_core' => 'دبلن كور',
                        'islamic_metadata' => 'بيانات إسلامية',
                        'shamela_specific' => 'خاص بالشاملة',
                        'custom' => 'مخصص'
                    ]),
                    
                Tables\Filters\SelectFilter::make('data_type')
                    ->label('نوع البيانات')
                    ->options([
                        'string' => 'نص',
                        'number' => 'رقم',
                        'boolean' => 'منطقي',
                        'date' => 'تاريخ',
                        'json' => 'JSON'
                    ]),
                    
                Tables\Filters\TernaryFilter::make('is_searchable')
                    ->label('قابل للبحث'),
                    
                Tables\Filters\TernaryFilter::make('is_public')
                    ->label('عام'),
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
            'index' => Pages\ListBookMetadata::route('/'),
            'create' => Pages\CreateBookMetadata::route('/create'),
            'edit' => Pages\EditBookMetadata::route('/{record}/edit'),
        ];
    }
}
