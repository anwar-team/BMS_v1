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

class BookSectionResource extends Resource
{
    protected static ?string $model = BookSection::class;
    protected static ?int $navigationSort = -4;

    protected static ?string $navigationGroup = 'المكتبة';

    protected static ?string $navigationIcon = 'heroicon-o-folder';
    
    protected static ?string $navigationLabel = null;
    protected static ?string $modelLabel = null;
    protected static ?string $pluralModelLabel = null;

    public static function getNavigationLabel(): string
    {
        return 'أقسام الكتب';
    }

    public static function getModelLabel(): string
    {
        return 'قسم كتاب';
    }

    public static function getPluralModelLabel(): string
    {
        return 'أقسام الكتب';
    }

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
                    ->label('ترتيب العرض')
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

                // قسم الأيقونات
                Forms\Components\Section::make('إعدادات الأيقونة')
                    ->schema([
                        // معاينة الأيقونة
                        Forms\Components\ViewField::make('icon_preview')
                            ->label('معاينة الأيقونة')
                            ->view('filament.forms.components.icon-preview')
                            ->viewData(fn (callable $get) => [
                                'icon_type' => $get('icon_type'),
                                'icon_url' => $get('icon_url'),
                                'icon_library' => $get('icon_library'),
                                'icon_name' => $get('icon_name'),
                                'icon_color' => $get('icon_color') ?? '#3B82F6',
                                'icon_size' => $get('icon_size') ?? 'md',
                            ])
                            ->visible(fn (callable $get) => !empty($get('icon_type'))),

                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('reset_icon')
                                ->label('إعادة تعيين للافتراضي')
                                ->icon('heroicon-o-arrow-path')
                                ->color('gray')
                                ->action(function (callable $set) {
                                    $set('icon_type', null);
                                    $set('icon_url', null);
                                    $set('icon_library', null);
                                    $set('icon_name', null);
                                    $set('icon_color', null);
                                    $set('icon_size', null);
                                })
                                ->requiresConfirmation()
                                ->modalHeading('إعادة تعيين الأيقونة')
                                ->modalDescription('هل أنت متأكد من إعادة تعيين الأيقونة للحالة الافتراضية؟')
                                ->modalSubmitActionLabel('نعم، إعادة تعيين')
                                ->modalCancelActionLabel('إلغاء')
                        ])
                        ->visible(fn (callable $get) => !empty($get('icon_type'))),

                        Forms\Components\Select::make('icon_type')
                            ->label('نوع الأيقونة')
                            ->options([
                                'upload' => 'رفع ملف',
                                'url' => 'رابط URL',
                                'library' => 'من المكتبة',
                                'color' => 'لون فقط',
                            ])
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('icon_url', null)),

                        Forms\Components\FileUpload::make('icon_url')
                            ->label('رفع الأيقونة')
                            ->image()
                            ->disk('public')
                            ->directory('icons')
                            ->visibility('public')
                            ->imageResizeMode('contain')
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth('64')
                            ->imageResizeTargetHeight('64')
                            ->acceptedFileTypes(['image/png', 'image/jpg', 'image/jpeg', 'image/svg+xml', 'image/gif'])
                            ->maxSize(2048)
                            ->reactive()
                            ->visible(fn (callable $get) => $get('icon_type') === 'upload'),

                        Forms\Components\TextInput::make('icon_url')
                            ->label('رابط الأيقونة')
                            ->url()
                            ->placeholder('https://example.com/icon.svg')
                            ->reactive()
                            ->visible(fn (callable $get) => $get('icon_type') === 'url'),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('icon_library')
                                    ->label('مكتبة الأيقونات')
                                    ->options([
                                        'heroicons' => 'Heroicons',
                                        'fontawesome' => 'Font Awesome',
                                        'custom' => 'مخصص',
                                    ])
                                    ->reactive()
                                    ->visible(fn (callable $get) => $get('icon_type') === 'library'),

                                \App\Filament\Forms\Components\IconPicker::make('icon_name')
                                    ->label('اختر الأيقونة')
                                    ->placeholder('ابحث عن الأيقونة...')
                                    ->helperText('ابحث واختر الأيقونة من المكتبة المحددة')
                                    ->visible(fn (callable $get) => $get('icon_type') === 'library' && $get('icon_library'))
                                    ->reactive()
                                    ->required(fn (callable $get) => $get('icon_type') === 'library'),
                            ])
                            ->visible(fn (callable $get) => $get('icon_type') === 'library'),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\ColorPicker::make('icon_color')
                                    ->label('لون الأيقونة')
                                    ->default('#3B82F6')
                                    ->reactive(),

                                Forms\Components\Select::make('icon_size')
                                    ->label('حجم الأيقونة')
                                    ->options([
                                        'sm' => 'صغير (16px)',
                                        'md' => 'متوسط (24px)',
                                        'lg' => 'كبير (32px)',
                                        'xl' => 'كبير جداً (48px)',
                                        'custom' => 'حجم مخصص'
                                    ])
                                    ->default('md')
                                    ->reactive(),

                                Forms\Components\TextInput::make('icon_custom_size')
                                    ->label('الحجم المخصص (بكسل)')
                                    ->numeric()
                                    ->minValue(8)
                                    ->maxValue(200)
                                    ->default(24)
                                    ->suffix('px')
                                    ->visible(fn (callable $get) => $get('icon_type') === 'library' && $get('icon_size') === 'custom')
                                    ->reactive()
                                    ->helperText('أدخل حجم الأيقونة بالبكسل (من 8 إلى 200)'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ViewColumn::make('icon')
                    ->label('الأيقونة')
                    ->view('filament.tables.columns.icon-column')
                    ->toggleable(),

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
                    ->label('ترتيب العرض')
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
                            ->placeholder('البحث عن قسم...'),
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
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            BookSectionResource\RelationManagers\BooksRelationManager::class,
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
