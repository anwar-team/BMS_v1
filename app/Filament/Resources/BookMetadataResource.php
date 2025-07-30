<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookMetadataResource\Pages;
use App\Filament\Resources\BookMetadataResource\RelationManagers;
use App\Models\BookMetadata;
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

    protected static ?string $navigationIcon = 'heroicon-o-information-circle';
    
    protected static ?string $navigationGroup = 'Books';
    
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'view' => Pages\ViewBookMetadata::route('/{record}'),
            'edit' => Pages\EditBookMetadata::route('/{record}/edit'),
        ];
    }
}
