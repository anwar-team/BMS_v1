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
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AuthorResource extends Resource
{
    protected static ?string $model = Author::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Book Management';
    
    protected static ?string $navigationLabel = 'المؤلفين';
    
    protected static ?string $modelLabel = 'مؤلف';
    
    protected static ?string $pluralModelLabel = 'المؤلفين';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('full_name')
                    ->label('الاسم الكامل')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Grid::make(2)->schema([
                    Select::make('madhhab')
                        ->label('المذهب')
                        ->options([
                            'المذهب الحنفي' => 'المذهب الحنفي',
                            'المذهب المالكي' => 'المذهب المالكي',
                            'المذهب الشافعي' => 'المذهب الشافعي',
                            'المذهب الحنبلي' => 'المذهب الحنبلي',
                            'آخرون' => 'آخرون',
                        ])
                        ->placeholder('اختر المذهب'),
                    
                    Toggle::make('is_living')
                        ->label('هل المؤلف على قيد الحياة؟')
                        ->default(true)
                        ->live(),
                ]),

                Textarea::make('biography')
                    ->label('السيرة الذاتية')
                    ->rows(4)
                    ->columnSpanFull(),
                
                // Birth year fields
                Grid::make(2)->schema([
                    Select::make('birth_year_type')
                        ->label('نوع تقويم الميلاد')
                        ->options([
                            'gregorian' => 'ميلادي',
                            'hijri' => 'هجري',
                        ])
                        ->default('gregorian')
                        ->live(),
                    
                    TextInput::make('birth_year')
                        ->label(fn ($get) => $get('birth_year_type') === 'hijri' ? 'سنة الميلاد (هجري)' : 'سنة الميلاد (ميلادي)')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(fn ($get) => $get('birth_year_type') === 'hijri' ? 1500 : date('Y')),
                ]),
                
                // Death year fields (conditional)
                Grid::make(2)->schema([
                    Select::make('death_year_type')
                        ->label('نوع تقويم الوفاة')
                        ->options([
                            'gregorian' => 'ميلادي',
                            'hijri' => 'هجري',
                        ])
                        ->default('gregorian')
                        ->live()
                        ->visible(fn ($get) => !$get('is_living')),
                    
                    TextInput::make('death_year')
                        ->label(fn ($get) => $get('death_year_type') === 'hijri' ? 'سنة الوفاة (هجري)' : 'سنة الوفاة (ميلادي)')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(fn ($get) => $get('death_year_type') === 'hijri' ? 1500 : date('Y'))
                        ->visible(fn ($get) => !$get('is_living'))
                        ->nullable(),
                ]),

                // Keep original date fields for backward compatibility
                Forms\Components\DatePicker::make('birth_date')
                    ->label('تاريخ الميلاد')
                    ->hidden(),
                Forms\Components\DatePicker::make('death_date')
                    ->label('تاريخ الوفاة')
                    ->hidden(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('الاسم الكامل')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('madhhab')
                    ->label('المذهب')
                    ->searchable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\IconColumn::make('is_living')
                    ->label('على قيد الحياة')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('birth_year')
                    ->label('سنة الميلاد')
                    ->formatStateUsing(fn ($record) => $record->birth_year ? $record->birth_year . ' (' . ($record->birth_year_type === 'hijri' ? 'هـ' : 'م') . ')' : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('death_year')
                    ->label('سنة الوفاة')
                    ->formatStateUsing(fn ($record) => !$record->is_living && $record->death_year ? $record->death_year . ' (' . ($record->death_year_type === 'hijri' ? 'هـ' : 'م') . ')' : ($record->is_living ? 'على قيد الحياة' : '-'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('تاريخ التحديث')
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
            'index' => Pages\ListAuthors::route('/'),
            'create' => Pages\CreateAuthor::route('/create'),
            'edit' => Pages\EditAuthor::route('/{record}/edit'),
        ];
    }
}
