<?php

namespace App\Filament\Resources;

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

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';
    
    protected static ?string $navigationGroup = 'Books';
    
    protected static ?int $navigationSort = 6;

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
            'index' => Pages\ListBookIndices::route('/'),
            'create' => Pages\CreateBookIndex::route('/create'),
            'view' => Pages\ViewBookIndex::route('/{record}'),
            'edit' => Pages\EditBookIndex::route('/{record}/edit'),
        ];
    }
}
