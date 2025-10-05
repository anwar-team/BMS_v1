<?php

namespace App\Filament\Resources\Banner;

use App\Filament\Resources\Banner\ContentResource\Pages;
use App\Models\Banner\Category;
use App\Models\Banner\Content;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;

class ContentResource extends Resource
{
    protected static ?string $model = Content::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $slug = 'banner/contents';

    protected static int $globalSearchResultsLimit = 10;

    protected static ?int $navigationSort = -2;
    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static function getLastSortValue(): int
    {
        return Content::max('sort') ?? 0;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('تفاصيل البانر')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('عام')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Forms\Components\Section::make('التفاصيل الرئيسية')
                                    ->description('املأ التفاصيل الرئيسية للبانر')
                                    ->icon('heroicon-o-clipboard')
                                    ->schema([
                                        Forms\Components\Select::make('banner_category_id')
                                            ->label('الفئة')
                                            ->relationship('category', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->createOptionForm([
                                                Forms\Components\TextInput::make('name')
                                                    ->label('الاسم')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(fn($state, Forms\Set $set) => $set('slug', Str::slug($state))),
                                                Forms\Components\TextInput::make('slug')
                                                    ->label('الرابط المختصر')
                                                    ->disabled()
                                                    ->dehydrated()
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->unique(Category::class, 'slug', ignoreRecord: true)
                                                    ->helperText('نسخة مناسبة للرابط من العنوان - يتم إنشاؤها تلقائياً')
                                                    ->suffixAction(
                                                        Forms\Components\Actions\Action::make('editSlug')
                                                            ->icon('heroicon-o-pencil-square')
                                                            ->modalHeading('تعديل الرابط المختصر')
                                                            ->modalDescription('تخصيص الرابط المختصر لهذه الفئة. استخدم الأحرف الصغيرة والأرقام والشرطات فقط.')
                                                            ->modalIcon('heroicon-o-link')
                                                            ->modalSubmitActionLabel('تحديث الرابط')
                                                            ->form([
                                                                Forms\Components\TextInput::make('new_slug')
                                                                    ->hiddenLabel()
                                                                    ->required()
                                                                    ->maxLength(255)
                                                                    ->live(debounce: 500)
                                                                    ->afterStateUpdated(function (string $state, Forms\Set $set) {
                                                                        $set('new_slug', Str::slug($state));
                                                                    })
                                                                    ->unique(Category::class, 'slug', ignoreRecord: true)
                                                                    ->helperText('سيتم تنسيق الرابط تلقائياً أثناء الكتابة.')
                                                            ])
                                                            ->action(function (array $data, Forms\Set $set) {
                                                                $set('slug', $data['new_slug']);

                                                                Notification::make()
                                                                    ->title('تم تحديث الرابط')
                                                                    ->success()
                                                                    ->send();
                                                            })
                                                    ),
                                                Forms\Components\Toggle::make('is_active')
                                                    ->label('نشط')
                                                    ->default(true),
                                            ])
                                            ->required(),
                                        Forms\Components\Toggle::make('is_active')
                                            ->label('نشط')
                                            ->helperText('التحكم في ظهور البانر')
                                            ->default(true),
                                        Forms\Components\TextInput::make('title')
                                            ->label('العنوان')
                                            ->maxLength(255)
                                            ->columnSpan(2),
                                        Forms\Components\MarkdownEditor::make('description')
                                            ->label('الوصف')
                                            ->helperText('قدم وصفاً للبانر')
                                            ->maxLength(500)
                                            ->columnSpanFull(),
                                        Forms\Components\Select::make('locale')
                                            ->label('اللغة')
                                            ->options([
                                                'ar' => 'العربية',
                                                'en' => 'الإنجليزية',
                                                'id' => 'الإندونيسية',
                                                'zh' => 'الصينية',
                                                'ja' => 'اليابانية',
                                                // Add more languages as needed
                                            ])
                                            ->default('ar')
                                            ->required(),
                                    ])
                                    ->compact()
                                    ->columns(2),
                            ]),
                        Forms\Components\Tabs\Tab::make('صورة البانر')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Forms\Components\Section::make('الصورة')
                                    ->description('ارفع صورة البانر هنا')
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('banners')
                                            ->label('صورة البانر')
                                            ->collection('banners')
                                            ->multiple(false)
                                            ->maxFiles(1)
                                            ->imagePreviewHeight('250')
                                            ->panelLayout('compact')
                                            ->imageResizeMode('cover')
                                            ->imageResizeTargetWidth('1200')
                                            ->imageResizeTargetHeight('800')
                                            ->acceptedFileTypes(['image/*'])
                                            ->helperText('ارفع صورة البانر. الحجم المُوصى به: 1200x800 بكسل')
                                            ->columnSpanFull(),
                                    ])
                                    ->compact(),
                            ]),
                        Forms\Components\Tabs\Tab::make('الجدولة')
                            ->icon('heroicon-o-calendar')
                            ->schema([
                                Forms\Components\Section::make('الجدولة')
                                    ->description('حدد تفاصيل جدولة البانر')
                                    ->schema([
                                        Forms\Components\DateTimePicker::make('start_date')
                                            ->label('تاريخ البداية')
                                            ->helperText('اختر تاريخ ووقت البداية')
                                            ->nullable(),
                                        Forms\Components\DateTimePicker::make('end_date')
                                            ->label('تاريخ النهاية')
                                            ->helperText('اختر تاريخ ووقت النهاية')
                                            ->nullable()
                                            ->after('start_date'),
                                        Forms\Components\DateTimePicker::make('published_at')
                                            ->label('تاريخ النشر')
                                            ->helperText('متى يجب نشر هذا البانر؟')
                                            ->nullable(),
                                    ])
                                    ->compact()
                                    ->columns(2),
                            ]),
                        Forms\Components\Tabs\Tab::make('الرابط والتتبع')
                            ->icon('heroicon-o-link')
                            ->schema([
                                Forms\Components\Section::make('إعدادات النقر')
                                    ->description('تكوين خيارات الرابط والتتبع')
                                    ->schema([
                                        Forms\Components\TextInput::make('click_url')
                                            ->label('رابط النقر')
                                            ->helperText('أدخل الرابط للانتقال إليه عند النقر على البانر')
                                            ->url()
                                            ->maxLength(255),
                                        Forms\Components\Select::make('click_url_target')
                                            ->label('هدف رابط النقر')
                                            ->helperText('اختر كيفية فتح الرابط')
                                            ->options([
                                                '_blank' => 'تبويب جديد',
                                                '_self' => 'التبويب الحالي',
                                            ])
                                            ->default('_self')
                                            ->native(false),
                                    ])
                                    ->compact()
                                    ->columns(2),
                                Forms\Components\Section::make('التتبع')
                                    ->description('إحصائيات تتبع البانر')
                                    ->schema([
                                        Forms\Components\Placeholder::make('impression_count')
                                            ->label('المشاهدات')
                                            ->content(fn(Content $record): string => number_format($record->impression_count ?? 0)),
                                        Forms\Components\Placeholder::make('click_count')
                                            ->label('النقرات')
                                            ->content(fn(Content $record): string => number_format($record->click_count ?? 0)),
                                        Forms\Components\Placeholder::make('ctr')
                                            ->label('معدل النقر (CTR)')
                                            ->content(function (Content $record): string {
                                                if (($record->impression_count ?? 0) > 0) {
                                                    $ctr = ($record->click_count / $record->impression_count) * 100;
                                                    return number_format($ctr, 2) . '%';
                                                }
                                                return '0.00%';
                                            }),
                                    ])
                                    ->compact()
                                    ->columns(3)
                                    ->visible(fn(?Content $record) => $record !== null),
                            ]),
                        Forms\Components\Tabs\Tab::make('الإعدادات المتقدمة')
                            ->icon('heroicon-o-cog')
                            ->schema([
                                Forms\Components\Section::make('الإعدادات')
                                    ->description('إعدادات إضافية للبانر')
                                    ->schema([
                                        Forms\Components\TextInput::make('sort')
                                            ->label('ترتيب الفرز')
                                            ->helperText('حدد ترتيب فرز البانر')
                                            ->required()
                                            ->numeric()
                                            ->default(static::getLastSortValue() + 1),
                                        Forms\Components\KeyValue::make('options')
                                            ->label('الخيارات المخصصة')
                                            ->keyLabel('اسم الخيار')
                                            ->valueLabel('قيمة الخيار')
                                            ->helperText('خيارات JSON مخصصة لهذا البانر')
                                            ->addable()
                                            ->reorderable()
                                            ->columnSpanFull(),
                                    ])
                                    ->compact(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('banners')
                    ->label('الصورة')
                    ->collection('banners')
                    ->conversion('thumbnail')
                    ->size(60)
                    ->circular(false)
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->description(fn(Model $record): string => Str::limit(strip_tags($record->description), 100))
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('الفئة')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean()
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('impression_count')
                    ->label('المشاهدات')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('click_count')
                    ->label('النقرات')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('locale')
                    ->label('اللغة')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('banner_category_id')
                    ->label('الفئة')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('حالة النشاط'),
                Tables\Filters\SelectFilter::make('locale')
                    ->label('اللغة')
                    ->options([
                        'ar' => 'العربية',
                        'en' => 'الإنجليزية',
                        'id' => 'الإندونيسية',
                        'zh' => 'الصينية',
                        'ja' => 'اليابانية',
                    ]),
                Tables\Filters\Filter::make('date_range')
                    ->label('نطاق التاريخ')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('من'),
                        Forms\Components\DatePicker::make('until')
                            ->label('إلى'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->where(function ($q) use ($date) {
                                    $q->whereNull('end_date')->orWhere('end_date', '>=', $date);
                                }),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->where(function ($q) use ($date) {
                                    $q->whereNull('start_date')->orWhere('start_date', '<=', $date);
                                }),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators['from'] = 'نشط من ' . $data['from']->format('M j, Y');
                        }

                        if ($data['until'] ?? null) {
                            $indicators['until'] = 'نشط حتى ' . $data['until']->format('M j, Y');
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->hiddenLabel()->tooltip('عرض'),
                Tables\Actions\EditAction::make()->hiddenLabel()->tooltip('تعديل'),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('preview')
                        ->label('معاينة البانر')
                        ->icon('heroicon-m-eye')
                        ->url(fn(Content $record) => $record->getImageUrl('large'))
                        ->openUrlInNewTab(),
                    Tables\Actions\Action::make('clone')
                        ->label('نسخ البانر')
                        ->icon('heroicon-m-document-duplicate')
                        ->requiresConfirmation()
                        ->action(function (Content $record) {
                            // Get only the fillable attributes
                            $attributes = $record->only($record->getFillable());

                            // Create a new instance and fill it with the attributes
                            $clone = new Content($attributes);

                            // Set the new title
                            $clone->title = "{$record->title} (نسخة)";

                            // Update the sort value
                            $clone->sort = static::getLastSortValue() + 1;

                            // Set the creator/updater
                            $clone->created_by = auth()->id();
                            $clone->updated_by = auth()->id();

                            // Reset counters
                            $clone->impression_count = 0;
                            $clone->click_count = 0;

                            // Save the clone
                            $clone->save();

                            // If the original has media, copy it to the clone
                            if ($record->hasMedia('banners')) {
                                $media = $record->getFirstMedia('banners');
                                $media->copy($clone, 'banners');
                            }

                            // Redirect to the edit page of the new clone
                            return redirect()->route('filament.admin.resources.banner.contents.edit', ['record' => $clone->id]);
                        }),
                    Tables\Actions\DeleteAction::make()->hiddenLabel()->tooltip('حذف'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('تفعيل')
                        ->icon('heroicon-m-check-circle')
                        ->requiresConfirmation()
                        ->action(fn(\Illuminate\Database\Eloquent\Collection $records) => $records->each->update(['is_active' => true])),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('إلغاء التفعيل')
                        ->icon('heroicon-m-x-circle')
                        ->requiresConfirmation()
                        ->action(fn(\Illuminate\Database\Eloquent\Collection $records) => $records->each->update(['is_active' => false])),
                ]),
            ])
            ->defaultSort('sort', 'asc')
            ->reorderable('sort');
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
            'index' => Pages\ListContents::route('/'),
            'create' => Pages\CreateContent::route('/create'),
            'edit' => Pages\EditContent::route('/{record}/edit'),
            'view' => Pages\ViewContent::route('/{record}'),
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['category']);
    }

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return $record->title;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'description', 'category.name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'الفئة' => $record->category->name,
            'الحالة' => $record->is_active ? 'نشط' : 'غير نشط',
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return __("menu.nav_group.banner");
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'gray';
    }
}
