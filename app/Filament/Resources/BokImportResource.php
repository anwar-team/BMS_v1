<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\BookImport;

use App\Filament\Resources\BokImportResource\Pages;
use App\Filament\Resources\BokImportResource\RelationManagers;
use App\Models\BokImport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BokImportResource extends Resource
{
    protected static ?string $model = BokImport::class;
    protected static ?string $cluster = BookImport::class;
    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';
    
    protected static ?string $navigationGroup = 'إدارة المحتوى';
    
    protected static ?string $navigationLabel = 'استيراد الكتب';
    
    protected static ?string $modelLabel = 'استيراد كتاب';
    
    protected static ?string $pluralModelLabel = 'استيراد الكتب';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('original_filename')
                    ->label('اسم الملف الأصلي')
                    ->required()
                    ->maxLength(255),
                    
                Forms\Components\TextInput::make('title')
                    ->label('العنوان')
                    ->required()
                    ->maxLength(255),
                    
                Forms\Components\TextInput::make('author')
                    ->label('المؤلف')
                    ->maxLength(255),
                    
                Forms\Components\Textarea::make('description')
                    ->label('الوصف')
                    ->rows(3),
                    
                Forms\Components\Select::make('language')
                    ->label('اللغة')
                    ->options([
                        'ar' => 'العربية',
                        'en' => 'الإنجليزية',
                        'fr' => 'الفرنسية',
                    ])
                    ->default('ar'),
                    
                Forms\Components\Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'في الانتظار',
                        'processing' => 'قيد المعالجة',
                        'completed' => 'مكتمل',
                        'failed' => 'فشل',
                        'cancelled' => 'ملغي',
                    ])
                    ->required()
                    ->default('pending'),
                    
                Forms\Components\Select::make('import_source')
                    ->label('مصدر الاستيراد')
                    ->options([
                        'web' => 'الويب',
                        'cli' => 'سطر الأوامر',
                        'api' => 'واجهة برمجة التطبيقات',
                    ])
                    ->default('web'),
                    
                Forms\Components\Select::make('book_id')
                    ->label('الكتاب المرتبط')
                    ->relationship('book', 'title')
                    ->searchable()
                    ->preload(),
                    
                Forms\Components\Select::make('user_id')
                    ->label('المستخدم')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                    
                Forms\Components\Toggle::make('is_featured')
                    ->label('مميز')
                    ->default(false),
                    
                Forms\Components\Toggle::make('allow_download')
                    ->label('السماح بالتحميل')
                    ->default(true),
                    
                Forms\Components\Toggle::make('allow_search')
                    ->label('السماح بالبحث')
                    ->default(true),
                    
                Forms\Components\Toggle::make('is_public')
                    ->label('عام')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('original_filename')
                    ->label('اسم الملف')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                    
                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                    
                Tables\Columns\TextColumn::make('author')
                    ->label('المؤلف')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('status_label')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'في الانتظار' => 'warning',
                        'قيد المعالجة' => 'info',
                        'مكتمل' => 'success',
                        'فشل' => 'danger',
                        'ملغي' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('language')
                    ->label('اللغة')
                    ->sortable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('file_size_formatted')
                    ->label('حجم الملف')
                    ->sortable('file_size')
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('pages_count')
                    ->label('عدد الصفحات')
                    ->sortable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('user.name')
                    ->label('المستخدم')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                    
                Tables\Columns\IconColumn::make('is_public')
                    ->label('عام')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'في الانتظار',
                        'processing' => 'قيد المعالجة',
                        'completed' => 'مكتمل',
                        'failed' => 'فشل',
                        'cancelled' => 'ملغي',
                    ]),
                    
                Tables\Filters\SelectFilter::make('language')
                    ->label('اللغة')
                    ->options([
                        'ar' => 'العربية',
                        'en' => 'الإنجليزية',
                        'fr' => 'الفرنسية',
                    ]),
                    
                Tables\Filters\SelectFilter::make('import_source')
                    ->label('مصدر الاستيراد')
                    ->options([
                        'web' => 'الويب',
                        'cli' => 'سطر الأوامر',
                        'api' => 'واجهة برمجة التطبيقات',
                    ]),
                    
                Tables\Filters\TernaryFilter::make('is_public')
                    ->label('عام'),
                    
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('مميز'),
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
            'index' => Pages\ListBokImports::route('/'),
            'create' => Pages\CreateBokImport::route('/create'),
            'edit' => Pages\EditBokImport::route('/{record}/edit'),
            'import-shamela' => Pages\ImportShamela::route('/import-shamela'),
        ];
    }
}
