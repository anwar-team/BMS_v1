<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VolumeResource\Pages;
use App\Filament\Resources\VolumeResource\RelationManagers;
use App\Models\Volume;
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

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
        protected static ?string $navigationGroup = 'Books';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('book_id')
                    ->relationship('book', 'title')
                    ->required(),
                Forms\Components\TextInput::make('number')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('title')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('page_start')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('page_end')
                    ->numeric()
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('book.title')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('number')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('page_start')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('page_end')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
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
            'index' => Pages\ListVolumes::route('/'),
            'create' => Pages\CreateVolume::route('/create'),
            'edit' => Pages\EditVolume::route('/{record}/edit'),
        ];
    }
}
