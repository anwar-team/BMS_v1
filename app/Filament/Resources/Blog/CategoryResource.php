<?php

namespace App\Filament\Resources\Blog;

use App\Filament\Resources\Blog\CategoryResource\Pages;
use App\Models\Blog\Category;
use Filament\Forms;
use Filament\Infolists;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Filament\Forms\Components\Tabs;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'blog/categories';

    protected static ?int $navigationSort = -1;
    protected static ?string $navigationIcon = 'fluentui-stack-20';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('تفاصيل الفئة')
                    ->tabs([
                        Tabs\Tab::make('المعلومات الأساسية')
                            ->schema([
                                Forms\Components\Select::make('parent_id')
                                    ->label('الفئة الأب')
                                    ->options(function () {
                                        // استبعاد الفئة الحالية إذا كان في وضع التحرير
                                        $query = Category::query();
                                        if (request()->route('record')) {
                                            $query->where('id', '!=', request()->route('record'));
                                        }
                                        return $query->pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->nullable()
                                    ->preload()
                                    ->columnSpan('full'),

                                Forms\Components\TextInput::make('name')
                                    ->label('الاسم')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn(string $operation, $state, Forms\Set $set) =>
                                        $operation === 'create' ? $set('slug', Str::slug($state)) : null),

                                Forms\Components\TextInput::make('slug')
                                    ->label('الرابط المختصر')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(Category::class, 'slug', ignoreRecord: true)
                                    ->helperText('اسم مناسب للرابط. سيتم إنشاؤه تلقائياً من الاسم إذا تُرك فارغاً.'),

                                Forms\Components\Select::make('locale')
                                    ->label('اللغة')
                                    ->options([
                                        'ar' => 'العربية',
                                        'en' => 'الإنجليزية',
                                        'id' => 'الإندونيسية',
                                        'zh' => 'الصينية',
                                        'ja' => 'اليابانية',
                                        // إضافة المزيد من اللغات حسب الحاجة
                                    ])
                                    ->default('ar')
                                    ->required(),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('نشط')
                                    ->helperText('فقط الفئات النشطة ستظهر في الواجهة الأمامية')
                                    ->default(true),
                            ]),

                        Tabs\Tab::make('المحتوى')
                            ->schema([
                                Forms\Components\MarkdownEditor::make('description')
                                    ->label('الوصف')
                                    ->columnSpan('full')
                                    ->fileAttachmentsDisk('public')
                                    ->fileAttachmentsDirectory('category-images')
                                    ->fileAttachmentsVisibility('public'),
                            ]),

                        Tabs\Tab::make('تحسين محركات البحث والبيانات الوصفية')
                            ->schema([
                                Forms\Components\TextInput::make('meta_title')
                                    ->label('عنوان البيانات الوصفية')
                                    ->maxLength(255)
                                    ->helperText('اتركه فارغاً لاستخدام اسم الفئة'),

                                Forms\Components\Textarea::make('meta_description')
                                    ->label('وصف البيانات الوصفية')
                                    ->maxLength(500)
                                    ->rows(3)
                                    ->helperText('وصف مختصر لمحركات البحث. الطول الموصى به: 150-160 حرف.'),
                            ]),

                        Tabs\Tab::make('الخيارات المتقدمة')
                            ->schema([
                                Forms\Components\KeyValue::make('options')
                                    ->label('الخيارات')
                                    ->keyLabel('اسم الخيار')
                                    ->valueLabel('قيمة الخيار')
                                    ->addable()
                                    ->reorderable()
                                    ->columnSpan('full')
                                    ->helperText('خيارات مخصصة لهذه الفئة (تنسيق JSON)')
                            ]),
                    ])
                    ->columnSpan('full'),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('المعلومات الأساسية')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('الاسم')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                        Infolists\Components\TextEntry::make('slug')
                            ->label('الرابط المختصر'),
                        Infolists\Components\TextEntry::make('parent.name')
                            ->label('الفئة الأب')
                            ->default('لا يوجد'),
                        Infolists\Components\IconEntry::make('is_active')
                            ->label('الحالة')
                            ->boolean(),
                        Infolists\Components\TextEntry::make('locale')
                            ->label('اللغة'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('الوصف')
                    ->schema([
                        Infolists\Components\TextEntry::make('description')
                            ->label('الوصف')
                            ->markdown(),
                    ]),

                Infolists\Components\Section::make('معلومات تحسين محركات البحث')
                    ->schema([
                        Infolists\Components\TextEntry::make('meta_title')
                            ->label('عنوان البيانات الوصفية'),
                        Infolists\Components\TextEntry::make('meta_description')
                            ->label('وصف البيانات الوصفية'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('الإحصائيات')
                    ->schema([
                        Infolists\Components\TextEntry::make('posts_count')
                            ->label('عدد المقالات')
                            ->state(fn(Category $record): int => $record->posts()->count()),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('تاريخ التحديث')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('creator.name')
                            ->label('أنشأ بواسطة')
                            ->default('النظام'),
                        Infolists\Components\TextEntry::make('updater.name')
                            ->label('آخر تحديث بواسطة')
                            ->default('النظام'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable()
                    ->description(fn(Category $record) => $record->parent ? "فرع من {$record->parent->name}" : '')
                    ->wrap(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('الرابط المختصر')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('locale')
                    ->label('اللغة')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('posts_count')
                    ->label('المقالات')
                    ->counts('posts')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('الفئة الأب')
                    ->options(fn() => Category::pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('locale')
                    ->label('اللغة')
                    ->options([
                        'ar' => 'العربية',
                        'en' => 'الإنجليزية',
                        'id' => 'الإندونيسية',
                        'zh' => 'الصينية',
                        'ja' => 'اليابانية',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('حالة النشاط'),
                Tables\Filters\TernaryFilter::make('root')
                    ->label('الفئات الجذرية فقط')
                    ->queries(
                        true: fn(Builder $query) => $query->whereNull('parent_id'),
                        false: fn(Builder $query) => $query->whereNotNull('parent_id'),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->hiddenLabel()->tooltip('عرض'),
                Tables\Actions\EditAction::make()->hiddenLabel()->tooltip('تحرير'),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('view_posts')
                        ->label('عرض المقالات')
                        ->icon('heroicon-m-photo')
                        ->url(fn(Category $record): string => PostResource::getUrl('index', [
                            'tableFilters[blog_category_id][value]' => $record->id,
                        ]))
                        ->openUrlInNewTab(),
                    Tables\Actions\Action::make('clone')
                        ->label('نسخ الفئة')
                        ->icon('heroicon-m-document-duplicate')
                        ->requiresConfirmation()
                        ->action(function (Category $record) {
                            // الحصول على الخصائص القابلة للملء فقط
                            $attributes = $record->only($record->getFillable());

                            // إنشاء مثيل جديد وملؤه بالخصائص
                            $clone = new Category($attributes);

                            // تعيين الاسم والرابط المختصر الجديد
                            $clone->name = "{$record->name} (نسخة)";
                            $clone->slug = Str::slug($clone->name);

                            // تعيين المنشئ/المحدث
                            $clone->created_by = auth()->id();
                            $clone->updated_by = auth()->id();

                            // حفظ النسخة
                            $clone->save();

                            // إعادة التوجيه إلى صفحة تحرير النسخة الجديدة
                            return redirect()->route('filament.admin.resources.blog.categories.edit', ['record' => $clone->id]);
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف'),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('تفعيل')
                        ->icon('heroicon-m-check-circle')
                        ->requiresConfirmation()
                        ->action(fn(Category $records) => $records->each->update(['is_active' => true])),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('إلغاء التفعيل')
                        ->icon('heroicon-m-x-circle')
                        ->requiresConfirmation()
                        ->action(fn(Category $records) => $records->each->update(['is_active' => false])),
                ]),
            ])
            ->defaultSort('updated_at', 'desc');
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return __("menu.nav_group.blog");
    }
}
