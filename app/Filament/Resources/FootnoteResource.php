<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FootnoteResource\Pages;
use App\Filament\Resources\FootnoteResource\RelationManagers;
use App\Models\Footnote;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FootnoteResource extends Resource
{
    protected static ?string $model = Footnote::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationGroup = 'Books';
    
    protected static ?int $navigationSort = 5;

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
            'index' => Pages\ListFootnotes::route('/'),
            'create' => Pages\CreateFootnote::route('/create'),
            'view' => Pages\ViewFootnote::route('/{record}'),
            'edit' => Pages\EditFootnote::route('/{record}/edit'),
        ];
    }
}
