<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeedbackComplaintResource\Pages;
use App\Filament\Resources\FeedbackComplaintResource\RelationManagers;
use App\Models\FeedbackComplaint;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\SelectFilter;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;

class FeedbackComplaintResource extends Resource
{
    protected static ?string $model = FeedbackComplaint::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    
    protected static ?string $navigationLabel = 'الملاحظات والشكاوى';
    
    protected static ?string $modelLabel = 'ملاحظة/شكوى';
    
    protected static ?string $pluralModelLabel = 'الملاحظات والشكاوى';
    
    protected static ?string $navigationGroup = 'إدارة الموقع';
    
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('type')
                    ->required(),
                Forms\Components\TextInput::make('subject')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('message')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('status')
                    ->required(),
                Forms\Components\TextInput::make('priority')
                    ->required(),
                Forms\Components\Textarea::make('admin_notes')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('ip_address')
                    ->maxLength(45)
                    ->default(null),
                Forms\Components\Textarea::make('user_agent')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('subject')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('priority'),
                Tables\Columns\TextColumn::make('ip_address')
                    ->searchable(),
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
            'index' => Pages\ListFeedbackComplaints::route('/'),
            'create' => Pages\CreateFeedbackComplaint::route('/create'),
            'edit' => Pages\EditFeedbackComplaint::route('/{record}/edit'),
        ];
    }
}
