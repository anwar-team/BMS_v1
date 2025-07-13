<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FileEditorResource\Pages;
use App\Services\FileService;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FileEditorResource extends Resource
{
   // protected static ?string $model = null;

   // protected static ?string $navigationIcon = 'heroicon-o-document-text';

   // protected static ?string $navigationLabel = 'File Editor';

    //protected static ?string $slug = 'file-editor';

    //protected static ?string $navigationGroup = 'System';

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
                //
            ])
            ->bulkActions([
                //
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
            'index' => Pages\ManageFileEditor::route('/'),
        ];
    }
}
