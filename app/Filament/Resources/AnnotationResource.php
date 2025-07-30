<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnnotationResource\Pages;
use App\Filament\Resources\AnnotationResource\RelationManagers;
use App\Models\Annotation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AnnotationResource extends Resource
{
    protected static ?string $model = Annotation::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';
    
    protected static ?string $navigationGroup = 'Books';
    
    protected static ?int $navigationSort = 9;

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
            'index' => Pages\ListAnnotations::route('/'),
            'create' => Pages\CreateAnnotation::route('/create'),
            'view' => Pages\ViewAnnotation::route('/{record}'),
            'edit' => Pages\EditAnnotation::route('/{record}/edit'),
        ];
    }
}
