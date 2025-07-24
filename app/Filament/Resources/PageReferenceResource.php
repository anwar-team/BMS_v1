<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageReferenceResource\Pages;
use App\Filament\Resources\PageReferenceResource\RelationManagers;
use App\Models\PageReference;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PageReferenceResource extends Resource
{
    protected static ?string $model = PageReference::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
            'index' => Pages\ListPageReferences::route('/'),
            'create' => Pages\CreatePageReference::route('/create'),
            'view' => Pages\ViewPageReference::route('/{record}'),
            'edit' => Pages\EditPageReference::route('/{record}/edit'),
        ];
    }
}
