<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PublisherResource\Pages;
use App\Filament\Resources\PublisherResource\RelationManagers;
use App\Models\Publisher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PublisherResource extends Resource
{
    protected static ?string $model = Publisher::class;
    protected static ?int $navigationSort = -5;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = null;
    
    protected static ?string $navigationLabel = null;
    protected static ?string $modelLabel = null;
    protected static ?string $pluralModelLabel = null;

    public static function getNavigationLabel(): string
    {
        return __('resource.publisher.publishers');
    }

    public static function getModelLabel(): string
    {
        return __('resource.publisher.publisher');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resource.publisher.publishers');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('library.navigation_group');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Publisher Name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('address')
                    ->label('Address')
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->rows(3)
                    ->maxLength(1000),
                Forms\Components\FileUpload::make('image')
                    ->label('Publisher Image')
                    ->image()
                    ->imageEditor()
                    ->maxSize(2048)
                    ->directory('publishers')
                    ->visibility('public'),
                Forms\Components\TextInput::make('phone')
                    ->label('Phone Number')
                    ->tel()
                    ->maxLength(20),
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255),
                Forms\Components\TextInput::make('website_url')
                    ->label('Website URL')
                    ->url()
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->helperText('Determine whether the publisher is active or not'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Publisher Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->label('Address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone Number'),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email'),
                Tables\Columns\TextColumn::make('website_url')
                    ->label('Website URL')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->circular()
                    ->size(40)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
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
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ])
                    ->placeholder('All Statuses'),
                Tables\Filters\Filter::make('name')
                    ->form([
                        Forms\Components\TextInput::make('name')
                            ->label('Publisher Name')
                            ->placeholder('Search for publisher...'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['name'],
                                fn (Builder $query, $name): Builder => $query->where('name', 'like', "%{$name}%"),
                            );
                    })
                    ->label('Search by Name'),
                Tables\Filters\Filter::make('address')
                    ->form([
                        Forms\Components\TextInput::make('address')
                            ->label('Address')
                            ->placeholder('Search for address...'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['address'],
                                fn (Builder $query, $address): Builder => $query->where('address', 'like', "%{$address}%"),
                            );
                    })
                    ->label('Search by Address'),
                Tables\Filters\Filter::make('has_website')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('website_url'))
                    ->label('Has Website'),
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
            PublisherResource\RelationManagers\BooksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPublishers::route('/'),
            'create' => Pages\CreatePublisher::route('/create'),
            'edit' => Pages\EditPublisher::route('/{record}/edit'),
        ];
    }
}
