<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuthorResource\Pages;
use App\Filament\Resources\AuthorResource\RelationManagers;
use App\Models\Author;
use App\Support\DateHelper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Get;
use DefStudio\FilamentSearchableInput\Components\SearchableInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AuthorResource extends Resource
{
    protected static ?string $model = Author::class;
    protected static ?int $navigationSort = -6;

    protected static ?string $navigationGroup = null;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    protected static ?string $navigationLabel = null;
    protected static ?string $modelLabel = null;
    protected static ?string $pluralModelLabel = null;

    public static function getNavigationLabel(): string
    {
        return 'المؤلفون';
    }

    public static function getModelLabel(): string
    {
        return 'مؤلف';
    }

    public static function getPluralModelLabel(): string
    {
        return 'المؤلفون';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'المكتبة';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('full_name')
                    ->label('الاسم الكامل')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull()
                    ->placeholder('أدخل اسم المؤلف الكامل'),

                FileUpload::make('image')
                    ->label('صورة المؤلف')
                    ->image()
                    ->directory('authors')
                    ->visibility('public')
                    ->columnSpanFull(),

                Grid::make(2)->schema([
                    Select::make('madhhab')
                        ->label('Madhhab')
                        ->options([
                            'المذهب الحنفي' => 'Hanafi Madhhab',
                            'المذهب المالكي' => 'Maliki Madhhab',
                            'المذهب الشافعي' => 'Shafi\'i Madhhab',
                            'المذهب الحنبلي' => 'Hanbali Madhhab',
                            'آخرون' => 'Others',
                        ])
                        ->placeholder('Choose Madhhab'),
                    
                    Select::make('is_living')
                        ->label('Author Status')
                        ->options([
                            true => 'Living',
                            false => 'Deceased',
                        ])
                        ->default(true)
                        ->live(),
                ]),

                Textarea::make('biography')
                    ->label('Biography')
                    ->rows(4)
                    ->columnSpanFull(),
                
                Grid::make(2)->schema([
                    Select::make('birth_calendar_type')
                        ->label('Birth Calendar Type')
                        ->options([
                            'gregorian' => 'Gregorian',
                            'hijri' => 'Hijri',
                        ])
                        ->default('gregorian')
                        ->reactive(),
                    
                    TextInput::make('birth_year_hijri')
                        ->label('Birth Year (Hijri)')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(1500)
                        ->visible(fn (Get $get) => $get('birth_calendar_type') === 'hijri'),
                    
                    TextInput::make('birth_year_gregorian')
                        ->label('Birth Year (Gregorian)')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(2024)
                        ->visible(fn (Get $get) => $get('birth_calendar_type') === 'gregorian'),
                ]),
                
                Grid::make(2)->schema([
                    Select::make('death_calendar_type')
                        ->label('Death Calendar Type')
                        ->options([
                            'gregorian' => 'Gregorian',
                            'hijri' => 'Hijri',
                        ])
                        ->default('gregorian')
                        ->reactive()
                        ->visible(fn (Get $get) => !$get('is_living')),
                    
                    TextInput::make('death_year_hijri')
                        ->label('Death Year (Hijri)')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(1500)
                        ->visible(fn (Get $get) => $get('death_calendar_type') === 'hijri' && !$get('is_living')),
                    
                    TextInput::make('death_year_gregorian')
                        ->label('Death Year (Gregorian)')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(2024)
                        ->visible(fn (Get $get) => $get('death_calendar_type') === 'gregorian' && !$get('is_living')),
                ]),
                
                Grid::make(2)->schema([
                    DatePicker::make('birth_date')
                        ->label('Birth Date')
                        ->nullable(),
                    
                    DatePicker::make('death_date')
                        ->label('Death Date')
                        ->nullable()
                        ->visible(fn (Get $get) => !$get('is_living')),

                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('الصورة')
                    ->circular()
                    ->size(40),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('الاسم الكامل')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('madhhab')
                    ->label('المذهب')
                    ->badge()
                    ->formatStateUsing(function ($state) {
                        return match($state) {
                            'المذهب الحنفي' => 'المذهب الحنفي',
                            'المذهب المالكي' => 'المذهب المالكي',
                            'المذهب الشافعي' => 'المذهب الشافعي',
                            'المذهب الحنبلي' => 'المذهب الحنبلي',
                            'آخرون' => 'آخرون',
                            default => $state,
                        };
                    }),
                Tables\Columns\IconColumn::make('is_living')
                    ->label('على قيد الحياة')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('birth_year')
                    ->label('سنة الميلاد')
                    ->sortable()
                    ->placeholder('غير محدد'),
                Tables\Columns\TextColumn::make('death_year')
                    ->label('سنة الوفاة')
                    ->sortable()
                    ->placeholder('غير محدد')
                    ->visible(fn ($record) => $record && !$record->is_living),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('madhhab')
                    ->label('Madhhab')
                    ->options([
                        'المذهب الحنفي' => 'Hanafi Madhhab',
                        'المذهب المالكي' => 'Maliki Madhhab',
                        'المذهب الشافعي' => 'Shafi\'i Madhhab',
                        'المذهب الحنبلي' => 'Hanbali Madhhab',
                        'آخرون' => 'Others',
                    ])
                    ->placeholder('All Madhabs'),
                Tables\Filters\TernaryFilter::make('is_living')
                    ->label('Author Status')
                    ->placeholder('All')
                    ->trueLabel('Living')
                    ->falseLabel('Deceased'),
                Tables\Filters\Filter::make('birth_year')
                    ->form([
                        Forms\Components\TextInput::make('from')
                            ->label('From Year')
                            ->numeric(),
                        Forms\Components\TextInput::make('until')
                            ->label('To Year')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->where('birth_year', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->where('birth_year', '<=', $date),
                            );
                    })
                    ->label('Birth Year'),
                Tables\Filters\Filter::make('death_year')
                    ->form([
                        Forms\Components\TextInput::make('from')
                            ->label('From Year')
                            ->numeric(),
                        Forms\Components\TextInput::make('until')
                            ->label('To Year')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->where('death_year', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->where('death_year', '<=', $date),
                            );
                    })
                    ->label('Death Year'),
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
            AuthorResource\RelationManagers\BooksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuthors::route('/'),
            'create' => Pages\CreateAuthor::route('/create'),
            'edit' => Pages\EditAuthor::route('/{record}/edit'),
        ];
    }
}
