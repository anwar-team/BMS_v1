<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReferenceResource\Pages;
use App\Filament\Resources\ReferenceResource\RelationManagers;
use App\Models\Reference;
use App\Models\Book;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReferenceResource extends Resource
{
    protected static ?string $model = Reference::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    
    protected static ?string $navigationGroup = 'إدارة المحتوى';
    
    protected static ?string $navigationLabel = 'المراجع';
    
    protected static ?string $modelLabel = 'مرجع';
    
    protected static ?string $pluralModelLabel = 'المراجع';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('book_id')
                    ->label('الكتاب')
                    ->relationship('book', 'title')
                    ->searchable()
                    ->preload()
                    ->required(),
                    
                Forms\Components\TextInput::make('title')
                    ->label('عنوان المرجع')
                    ->required()
                    ->maxLength(255),
                    
                Forms\Components\TextInput::make('author')
                    ->label('المؤلف')
                    ->maxLength(255),
                    
                Forms\Components\TextInput::make('publisher')
                    ->label('الناشر')
                    ->maxLength(255),
                    
                Forms\Components\TextInput::make('publication_year')
                    ->label('سنة النشر')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(date('Y')),
                    
                Forms\Components\TextInput::make('page_reference')
                    ->label('مرجع الصفحة')
                    ->maxLength(255),
                    
                Forms\Components\Select::make('reference_type')
                    ->label('نوع المرجع')
                    ->options([
                        'book' => 'كتاب',
                        'article' => 'مقال',
                        'website' => 'موقع إلكتروني',
                        'manuscript' => 'مخطوط',
                        'hadith_collection' => 'مجموعة أحاديث',
                        'tafsir' => 'تفسير',
                        'fatwa' => 'فتوى'
                    ])
                    ->required(),
                    
                Forms\Components\TextInput::make('isbn')
                    ->label('رقم ISBN')
                    ->maxLength(20),
                    
                Forms\Components\TextInput::make('url')
                    ->label('الرابط')
                    ->url()
                    ->maxLength(500),
                    
                Forms\Components\Textarea::make('notes')
                    ->label('ملاحظات')
                    ->rows(3),
                    
                Forms\Components\TextInput::make('edition')
                    ->label('الطبعة')
                    ->maxLength(100),
                    
                Forms\Components\TextInput::make('volume_info')
                    ->label('معلومات المجلد')
                    ->maxLength(100),
                    
                Forms\Components\TextInput::make('citation_count')
                    ->label('عدد الاستشهادات')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                    
                Forms\Components\Toggle::make('is_verified')
                    ->label('محقق')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('book.title')
                    ->label('الكتاب')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان المرجع')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                    
                Tables\Columns\TextColumn::make('author')
                    ->label('المؤلف')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('publisher')
                    ->label('الناشر')
                    ->searchable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('publication_year')
                    ->label('سنة النشر')
                    ->sortable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('reference_type')
                    ->label('نوع المرجع')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'book' => 'كتاب',
                        'article' => 'مقال',
                        'website' => 'موقع إلكتروني',
                        'manuscript' => 'مخطوط',
                        'hadith_collection' => 'مجموعة أحاديث',
                        'tafsir' => 'تفسير',
                        'fatwa' => 'فتوى',
                        default => $state,
                    })
                    ->badge()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('citation_count')
                    ->label('عدد الاستشهادات')
                    ->sortable()
                    ->toggleable(),
                    
                Tables\Columns\IconColumn::make('is_verified')
                    ->label('محقق')
                    ->boolean()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('reference_type')
                    ->label('نوع المرجع')
                    ->options([
                        'book' => 'كتاب',
                        'article' => 'مقال',
                        'website' => 'موقع إلكتروني',
                        'manuscript' => 'مخطوط',
                        'hadith_collection' => 'مجموعة أحاديث',
                        'tafsir' => 'تفسير',
                        'fatwa' => 'فتوى'
                    ]),
                    
                Tables\Filters\TernaryFilter::make('is_verified')
                    ->label('محقق'),
                    
                Tables\Filters\Filter::make('publication_year')
                    ->form([
                        Forms\Components\TextInput::make('from')
                            ->label('من سنة')
                            ->numeric(),
                        Forms\Components\TextInput::make('until')
                            ->label('إلى سنة')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->where('publication_year', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->where('publication_year', '<=', $date),
                            );
                    })
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
            'index' => Pages\ListReferences::route('/'),
            'create' => Pages\CreateReference::route('/create'),
            'edit' => Pages\EditReference::route('/{record}/edit'),
        ];
    }
}
