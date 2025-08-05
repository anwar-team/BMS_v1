<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\BookManagement;

use App\Filament\Resources\PageReferenceResource\Pages;
use App\Filament\Resources\PageReferenceResource\RelationManagers;
use App\Models\PageReference;
use App\Models\Page;
use App\Models\Reference;
use App\Models\Chapter;
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
    protected static ?string $cluster = BookManagement::class;
    protected static ?int $navigationSort = 7;

    protected static ?string $navigationIcon = 'heroicon-o-link';
    
    protected static ?string $navigationGroup = 'إدارة المحتوى';
    
    protected static ?string $navigationLabel = 'مراجع الصفحات';
    
    protected static ?string $modelLabel = 'مرجع صفحة';
    
    protected static ?string $pluralModelLabel = 'مراجع الصفحات';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('page_id')
                    ->label('الصفحة')
                    ->relationship('page', 'page_number')
                    ->searchable()
                    ->preload()
                    ->required(),
                    
                Forms\Components\Select::make('reference_id')
                    ->label('المرجع')
                    ->relationship('reference', 'title')
                    ->searchable()
                    ->preload()
                    ->required(),
                    
                Forms\Components\Select::make('chapter_id')
                    ->label('الفصل')
                    ->relationship('chapter', 'title')
                    ->searchable()
                    ->preload(),
                    
                Forms\Components\Textarea::make('citation_text')
                    ->label('نص الاستشهاد')
                    ->rows(4)
                    ->maxLength(1000),
                    
                Forms\Components\TextInput::make('position_in_page')
                    ->label('الموقع في الصفحة')
                    ->numeric()
                    ->minValue(1),
                    
                Forms\Components\Select::make('citation_type')
                    ->label('نوع الاستشهاد')
                    ->options([
                        'direct_quote' => 'اقتباس مباشر',
                        'paraphrase' => 'إعادة صياغة',
                        'reference' => 'مرجع',
                        'see_also' => 'انظر أيضاً'
                    ])
                    ->required(),
                    
                Forms\Components\Textarea::make('context')
                    ->label('السياق')
                    ->rows(3)
                    ->maxLength(500),
                    
                Forms\Components\Toggle::make('is_primary_source')
                    ->label('مصدر أولي')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('page.page_number')
                    ->label('رقم الصفحة')
                    ->sortable()
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('reference.title')
                    ->label('المرجع')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                    
                Tables\Columns\TextColumn::make('chapter.title')
                    ->label('الفصل')
                    ->searchable()
                    ->toggleable()
                    ->limit(30),
                    
                Tables\Columns\TextColumn::make('citation_text')
                    ->label('نص الاستشهاد')
                    ->limit(50)
                    ->searchable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('position_in_page')
                    ->label('الموقع في الصفحة')
                    ->sortable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('citation_type')
                    ->label('نوع الاستشهاد')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'direct_quote' => 'اقتباس مباشر',
                        'paraphrase' => 'إعادة صياغة',
                        'reference' => 'مرجع',
                        'see_also' => 'انظر أيضاً',
                        default => $state,
                    })
                    ->badge()
                    ->sortable(),
                    
                Tables\Columns\IconColumn::make('is_primary_source')
                    ->label('مصدر أولي')
                    ->boolean()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('citation_type')
                    ->label('نوع الاستشهاد')
                    ->options([
                        'direct_quote' => 'اقتباس مباشر',
                        'paraphrase' => 'إعادة صياغة',
                        'reference' => 'مرجع',
                        'see_also' => 'انظر أيضاً'
                    ]),
                    
                Tables\Filters\TernaryFilter::make('is_primary_source')
                    ->label('مصدر أولي'),
                    
                Tables\Filters\SelectFilter::make('reference_id')
                    ->label('المرجع')
                    ->relationship('reference', 'title')
                    ->searchable()
                    ->preload(),
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
            'index' => Pages\ListPageReferences::route('/'),
            'create' => Pages\CreatePageReference::route('/create'),
            'edit' => Pages\EditPageReference::route('/{record}/edit'),
        ];
    }
}
