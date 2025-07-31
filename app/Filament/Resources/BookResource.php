<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookResource\Pages;
use App\Filament\Resources\BookResource\RelationManagers;
use App\Models\Book;
use App\Models\Author;
use App\Models\Publisher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Book Management';
    
    protected static ?string $navigationLabel = 'الكتب';
    
    protected static ?string $modelLabel = 'كتاب';
    
    protected static ?string $pluralModelLabel = 'الكتب';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('عنوان الكتاب')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('book_section_id')
                    ->label('قسم الكتاب')
                    ->relationship('bookSection', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('publisher_id')
                    ->label('الناشر')
                    ->relationship('publisher', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('slug')
                    ->label('الرابط المختصر')
                    ->maxLength(255),
                Forms\Components\FileUpload::make('cover_image')
                    ->label('صورة الغلاف')
                    ->image()
                    ->directory('book-covers'),
                Forms\Components\TextInput::make('published_year')
                    ->label('سنة النشر')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(date('Y')),
                Forms\Components\TextInput::make('publisher')
                    ->label('الناشر')
                    ->maxLength(255),
                Forms\Components\TextInput::make('pages_count')
                    ->label('عدد الصفحات')
                    ->numeric()
                    ->minValue(1),
                Forms\Components\TextInput::make('volumes_count')
                    ->label('عدد المجلدات')
                    ->numeric()
                    ->minValue(1)
                    ->default(1),
                Forms\Components\Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'draft' => 'مسودة',
                        'published' => 'منشور',
                        'archived' => 'مؤرشف',
                    ])
                    ->default('draft'),
                Forms\Components\Select::make('visibility')
                    ->label('الرؤية')
                    ->options([
                        'public' => 'عام',
                        'private' => 'خاص',
                        'restricted' => 'مقيد',
                    ])
                    ->default('public'),
                Forms\Components\TextInput::make('cover_image_url')
                    ->label('رابط صورة الغلاف')
                    ->url()
                    ->maxLength(255),
                Forms\Components\TextInput::make('source_url')
                    ->label('رابط المصدر')
                    ->url()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('وصف الكتاب')
                    ->columnSpanFull(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان الكتاب')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bookSection.name')
                    ->label('قسم الكتاب')
                    ->searchable(),
                Tables\Columns\TextColumn::make('publisher')
                    ->label('الناشر')
                    ->searchable(),
                Tables\Columns\TextColumn::make('published_year')
                    ->label('سنة النشر')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pages_count')
                    ->label('عدد الصفحات')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('volumes_count')
                    ->label('عدد المجلدات')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'published' => 'success',
                        'archived' => 'warning',
                    }),
                Tables\Columns\TextColumn::make('visibility')
                    ->label('الرؤية')
                    ->badge(),
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
            'index' => Pages\ListBooks::route('/'),
            'create' => Pages\CreateBook::route('/create'),
            'edit' => Pages\EditBook::route('/{record}/edit'),
        ];
    }
}
