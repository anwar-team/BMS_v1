<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VolumeResource\Pages;
use App\Filament\Resources\VolumeResource\RelationManagers;
use App\Models\Volume;
use App\Models\Book;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VolumeResource extends Resource
{
    protected static ?string $model = Volume::class;
    protected static ?string $navigationGroup = 'Book Content Management';
    protected static ?int $navigationSort = -9;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    
    protected static ?string $navigationLabel = null;
    protected static ?string $modelLabel = null;
    protected static ?string $pluralModelLabel = null;

    public static function getNavigationLabel(): string
    {
        return __('resource.volume.volumes');
    }

    public static function getModelLabel(): string
    {
        return __('resource.volume.volume');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resource.volume.volumes');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('book_id')
                    ->label('Book')
                    ->relationship('book', 'title')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('volume_number')
                    ->label('Volume Number')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                Forms\Components\TextInput::make('title')
                    ->label('Volume Title')
                    ->maxLength(255),
                Forms\Components\TextInput::make('pages')
                    ->label('Number of Pages')
                    ->numeric()
                    ->minValue(1),
                Forms\Components\Textarea::make('description')
                    ->label('Volume Description')
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
                Tables\Columns\TextColumn::make('volume_number')
                    ->label('Volume Number')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Volume Title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pages')
                    ->label('Number of Pages')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
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
            VolumeResource\RelationManagers\ChaptersRelationManager::class,
            VolumeResource\RelationManagers\PagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVolumes::route('/'),
            'create' => Pages\CreateVolume::route('/create'),
            'edit' => Pages\EditVolume::route('/{record}/edit'),
        ];
    }
}
