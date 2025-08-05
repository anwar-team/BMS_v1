<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookSectionResource\Pages;
use App\Filament\Resources\BookSectionResource\RelationManagers;
use App\Models\BookSection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;

class BookSectionResource extends Resource
{
    protected static ?string $model = BookSection::class;
    protected static ?int $navigationSort = 12;

    protected static ?string $navigationGroup = 'إدارة المحتوى';

    protected static ?string $navigationIcon = 'heroicon-o-folder';
    
    //protected static ?string $navigationGroup = 'إدارة المحتوى';
    
    
    protected static ?string $navigationLabel = 'أقسام الكتب';
    
    protected static ?string $modelLabel = 'قسم كتب';
    
    protected static ?string $pluralModelLabel = 'أقسام الكتب';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('اسم القسم')
                    ->required()
                    ->maxLength(255),
                    
                Forms\Components\Textarea::make('description')
                    ->label('الوصف')
                    ->rows(3)
                    ->maxLength(500),
                    
                Forms\Components\Select::make('parent_id')
                    ->label('القسم الأب')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload(),
                    
                Forms\Components\TextInput::make('sort_order')
                    ->label('ترتيب الفرز')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                    
                Forms\Components\TextInput::make('slug')
                    ->label('الرابط المختصر')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                    
                Forms\Components\Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم القسم')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('القسم الأب')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(50)
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('ترتيب الفرز')
                    ->sortable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('slug')
                    ->label('الرابط المختصر')
                    ->searchable()
                    ->toggleable(),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('books_count')
                    ->label('عدد الكتب')
                    ->counts('books')
                    ->sortable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('نشط'),
                    
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('القسم الأب')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\Filter::make('name')
                    ->form([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم القسم')
                            ->placeholder('ابحث عن قسم...'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['name'],
                                fn (Builder $query, $name): Builder => $query->where('name', 'like', "%{$name}%"),
                            );
                    })
                    ->label('البحث بالاسم'),
                    
                Tables\Filters\Filter::make('has_books')
                    ->query(fn (Builder $query): Builder => $query->has('books'))
                    ->label('يحتوي على كتب'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    FilamentExportBulkAction::make('export')
                        ->label('تصدير'),
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
            'index' => Pages\ListBookSections::route('/'),
            'create' => Pages\CreateBookSection::route('/create'),
            'edit' => Pages\EditBookSection::route('/{record}/edit'),
        ];
    }
}
