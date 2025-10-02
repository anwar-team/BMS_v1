<?php

namespace App\Filament\Resources\Blog;

use App\Enums\Blog\PostStatus;
use App\Filament\Resources\Blog\PostResource\Pages;
use App\Models\Blog\Post;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
// use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Facades\Auth;

class PostResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Post::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationIcon = 'fluentui-news-20';

    protected static ?int $navigationSort = -2;

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any',
            'restore',
            'restore_any',
            'replicate',
            'reorder',
            'publish',
            'archive',
            'feature',
            'change_author',
            'approve',
            'schedule',
            'manage_seo',
            'bulk_publish',
            'view_analytics'
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('محتوى المقال')
                            ->description('المحتوى الرئيسي لمقال المدونة')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('العنوان')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->maxLength(255)
                                    ->placeholder('أدخل عنوان المقال')
                                    ->afterStateUpdated(fn(string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),

                                Forms\Components\TextInput::make('slug')
                                    ->label('الرابط المختصر')
                                    ->disabled()
                                    ->dehydrated()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(Post::class, 'slug', ignoreRecord: true)
                                    ->helperText('نسخة مناسبة للرابط من العنوان - يتم إنشاؤها تلقائياً')
                                    ->suffixAction(function (string $operation) {
                                        if ($operation === 'edit') {
                                            return Forms\Components\Actions\Action::make('editSlug')
                                                ->icon('heroicon-o-pencil-square')
                                                ->modalHeading('تحرير الرابط المختصر')
                                                ->modalDescription('تخصيص الرابط المختصر لهذا المقال. استخدم الأحرف الصغيرة والأرقام والشرطات فقط.')
                                                ->modalIcon('heroicon-o-link')
                                                ->modalSubmitActionLabel('تحديث الرابط المختصر')
                                                ->form([
                                                    Forms\Components\TextInput::make('new_slug')
                                                        ->hiddenLabel()
                                                        ->required()
                                                        ->maxLength(255)
                                                        ->live(debounce: 500)
                                                        ->afterStateUpdated(function (string $state, Forms\Set $set) {
                                                            $set('new_slug', Str::slug($state));
                                                        })
                                                        ->unique(Post::class, 'slug', ignoreRecord: true)
                                                        ->helperText('سيتم تنسيق الرابط المختصر تلقائياً أثناء الكتابة.')
                                                ])
                                                ->action(function (array $data, Forms\Set $set) {
                                                    $set('slug', $data['new_slug']);

                                                    Notification::make()
                                                        ->title('تم تحديث الرابط المختصر')
                                                        ->success()
                                                        ->send();
                                                });
                                        }
                                        return null;
                                    }),

                                Forms\Components\Textarea::make('content_overview')
                                    ->label('نظرة عامة على المحتوى')
                                    ->required()
                                    ->placeholder('قدم ملخصاً مختصراً أو مقتطفاً من هذا المقال')
                                    ->helperText('سيظهر هذا في صفحة قائمة المدونة')
                                    ->rows(5),

                                Forms\Components\RichEditor::make('content_raw')
                                    ->label('محتوى المقال')
                                    ->toolbarButtons([
                                        'attachFiles',
                                        'blockquote',
                                        'bold',
                                        'bulletList',
                                        'codeBlock',
                                        'h1',
                                        'h2',
                                        'h3',
                                        'italic',
                                        'link',
                                        'orderedList',
                                        'redo',
                                        'strike',
                                        'underline',
                                        'undo',
                                    ])
                                    ->required()
                                    ->placeholder('اكتب محتوى مقالك هنا...')
                                    ->fileAttachmentsDisk('public')
                                    ->fileAttachmentsDirectory('blog/posts/content-uploads')
                                    ->columnSpanFull()
                                    ->maxLength(65535)
                                    ->helperText('قم بتنسيق المحتوى باستخدام شريط الأدوات أعلاه')
                                    ->hint(function (Get $get): string {
                                        $wordCount = str_word_count(strip_tags($get('content_raw')));
                                        $readingTime = ceil($wordCount / 200); // افتراض 200 كلمة في الدقيقة
                                        return "{$wordCount} كلمة | ~{$readingTime} دقيقة قراءة";
                                    })
                                    ->extraInputAttributes(['style' => 'min-height: 500px;']),
                            ]),

                        Forms\Components\Section::make('الوسائط')
                            ->description('العناصر المرئية للمقال')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('featured')
                                    ->label('الصورة البارزة')
                                    ->collection('featured')
                                    ->image()
                                    ->imageResizeMode('contain')
                                    ->imageCropAspectRatio('16:9')
                                    ->imageResizeTargetWidth('1200')
                                    ->imageResizeTargetHeight('675')
                                    ->helperText('ستظهر هذه الصورة بشكل بارز في قوائم المقالات والمشاركات الاجتماعية (نسبة 16:9 موصى بها)')
                                    ->downloadable()
                                    ->responsiveImages(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('الحالة والرؤية')
                            ->description('التحكم في كيفية ظهور هذا المقال')
                            ->icon('heroicon-o-eye')
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('الحالة')
                                    ->options(function (?Post $record) {
                                        $user = Auth::user();
                                        $currentStatus = $record?->status;

                                        $allowedStatuses = [];

                                        if ($user && $user->isSuperAdmin()) {
                                            $allowedStatuses = [
                                                PostStatus::DRAFT->value => PostStatus::DRAFT->getLabel(),
                                                PostStatus::PENDING->value => PostStatus::PENDING->getLabel(),
                                                PostStatus::PUBLISHED->value => PostStatus::PUBLISHED->getLabel(),
                                            ];
                                        } elseif ($user && $user->hasAnyRole(['admin', 'editor'])) {
                                            $allowedStatuses = [
                                                PostStatus::DRAFT->value => PostStatus::DRAFT->getLabel(),
                                                PostStatus::PENDING->value => PostStatus::PENDING->getLabel(),
                                                PostStatus::PUBLISHED->value => PostStatus::PUBLISHED->getLabel(),
                                            ];
                                        } elseif ($user && $user->hasRole('author')) {
                                            $allowedStatuses = [
                                                PostStatus::DRAFT->value => PostStatus::DRAFT->getLabel(),
                                                PostStatus::PENDING->value => PostStatus::PENDING->getLabel(),
                                            ];

                                            if ($currentStatus === PostStatus::PUBLISHED) {
                                                $allowedStatuses[PostStatus::PUBLISHED->value] = PostStatus::PUBLISHED->getLabel();
                                            }
                                        }

                                        return $allowedStatuses;
                                    })
                                    ->default(PostStatus::DRAFT->value)
                                    ->live()
                                    ->required()
                                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                        if ($state === PostStatus::PUBLISHED->value && !$get('published_at')) {
                                            $set('published_at', now());
                                        } elseif ($state === PostStatus::DRAFT->value) {
                                            $set('published_at', null);
                                            $set('scheduled_at', null);
                                        }
                                    })
                                    ->helperText(function () {
                                        $user = Auth::user();
                                        if ($user && $user->hasRole('author')) {
                                            return 'يمكن للكتاب إنشاء مسودات أو تقديمها للمراجعة. المحررون فقط يمكنهم النشر.';
                                        }
                                        return 'التحكم في حالة نشر هذا المقال.';
                                    }),

                                Forms\Components\DatePicker::make('published_at')
                                    ->label('تاريخ النشر')
                                    ->required(fn(Get $get): bool => $get('status') === PostStatus::PUBLISHED->value)
                                    ->placeholder('اختر تاريخ النشر')
                                    ->helperText('التاريخ الذي سيتم نشر المقال فيه')
                                    ->default(now())
                                    ->disabled(function () {
                                        $user = Auth::user();
                                        return $user && $user->hasRole('author');
                                    }),

                                Forms\Components\DateTimePicker::make('scheduled_at')
                                    ->label('جدولة النشر')
                                    ->required(fn(Get $get): bool => $get('status') === PostStatus::PENDING->value)
                                    ->visible(fn(Get $get): bool => $get('status') === PostStatus::PENDING->value)
                                    ->placeholder('اختر موعد الجدولة')
                                    ->seconds(false)
                                    ->timezone('UTC')
                                    ->hint('سيتم نشر المقال تلقائياً في هذا الوقت')
                                    ->hintIcon('heroicon-m-clock')
                                    ->disabled(function (?Post $record) {
                                        $user = Auth::user();
                                        return $user && $user->hasRole('author') && !$user->can('schedule', $record ?? new Post());
                                    }),

                                Forms\Components\Toggle::make('is_featured')
                                    ->label('مقال مميز')
                                    ->helperText('المقالات المميزة تظهر بشكل بارز في الموقع')
                                    ->default(false)
                                    ->visible(function (?Post $record) {
                                        $user = Auth::user();
                                        return $user && $user->can('feature', $record ?? new Post());
                                    })
                                    ->disabled(function (?Post $record) {
                                        $user = Auth::user();
                                        return !$user || !$user->can('feature', $record ?? new Post());
                                    }),

                                Forms\Components\Placeholder::make('analytics')
                                    ->label('إحصائيات المقال')
                                    ->content(function (?Post $record): HtmlString {
                                        if (!$record) {
                                            return new HtmlString('<span class="text-sm text-gray-500">ستكون الإحصائيات متاحة بعد الحفظ</span>');
                                        }

                                        return new HtmlString("
                                            <div class='space-y-2'>
                                                <div class='flex justify-between'>
                                                    <span class='text-sm text-gray-600'>المشاهدات:</span>
                                                    <span class='text-sm font-semibold'>{$record->view_count}</span>
                                                </div>
                                                <div class='flex justify-between'>
                                                    <span class='text-sm text-gray-600'>وقت القراءة:</span>
                                                    <span class='text-sm font-semibold'>{$record->reading_time} دقيقة</span>
                                                </div>
                                                <div class='flex justify-between'>
                                                    <span class='text-sm text-gray-600'>التعليقات:</span>
                                                    <span class='text-sm font-semibold'>{$record->comments_count}</span>
                                                </div>
                                            </div>
                                        ");
                                    })
                                    ->visible(function (?Post $record) {
                                        $user = Auth::user();
                                        return $record && $user && $user->can('viewAnalytics', $record);
                                    }),
                            ]),

                        Forms\Components\Section::make('التصنيف')
                            ->description('تنظيم وتصنيف هذا المقال')
                            ->icon('heroicon-o-tag')
                            ->schema([
                                Forms\Components\Select::make('blog_category_id')
                                    ->label('الفئة')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->label('الاسم')
                                            ->required(),
                                    ])
                                    ->required(),

                                SpatieTagsInput::make('tags')
                                    ->label('العلامات')
                                    ->placeholder('أضف علامات')
                                    ->helperText('علامات مفصولة بفواصل للمساعدة في البحث والتصفية'),
                            ]),

                        Forms\Components\Section::make('الإسناد')
                            ->description('من أنشأ هذا المقال')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Forms\Components\Select::make('blog_author_id')
                                    ->label('المؤلف')
                                    ->relationship(
                                        name: 'author',
                                        modifyQueryUsing: fn(Builder $query) => $query->with('roles')->whereRelation('roles', 'name', '=', 'author'),
                                    )
                                    ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->firstname} {$record->lastname}")
                                    ->searchable(['firstname', 'lastname'])
                                    ->preload()
                                    ->required()
                                    ->disabled(function (?Post $record) {
                                        $user = Auth::user();
                                        if (!$user) return true;

                                        if ($user->isSuperAdmin()) {
                                            return false;
                                        }

                                        if (!$record) {
                                            return !$user->can('change_author_blog::post');
                                        }

                                        return !$user->can('changeAuthor', $record);
                                    })
                                    ->helperText(function (?Post $record) {
                                        $user = Auth::user();
                                        if (!$user) return '';

                                        if ($user->isSuperAdmin()) {
                                            return 'المدير العام يمكنه تغيير مؤلف أي مقال.';
                                        }

                                        if (!$user->can('change_author_blog::post')) {
                                            return 'المديرون فقط يمكنهم تغيير مؤلف المقال.';
                                        }
                                        return 'اختر المؤلف لهذا المقال.';
                                    }),

                                Forms\Components\Placeholder::make('audit_trail')
                                    ->label('')
                                    ->content(function (Post $record): HtmlString {
                                        if ($record->exists) {
                                            $creatorName = $record->creator ? "{$record->creator->firstname} {$record->creator->lastname}" : 'غير معروف';
                                            $updaterName = $record->updater ? "{$record->updater->firstname} {$record->updater->lastname}" : 'غير معروف';
                                            $createdAt = $record->created_at?->format('M d, Y \a\t h:ia');
                                            $updatedAt = $record->updated_at?->diffForHumans();

                                            return new HtmlString("
                                                <div class='space-y-4'>
                                                    <div>
                                                        <div class='text-sm font-medium text-gray-400 dark:text-gray-400'>أنشئ بواسطة</div>
                                                        <div class='flex items-center space-x-2'>
                                                            <span class='text-sm font-bold text-primary-600 dark:text-primary-400'>{$creatorName}</span>
                                                            <span class='text-xs text-gray-500 dark:text-gray-400'>في {$createdAt}</span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class='text-sm font-medium text-gray-400 dark:text-gray-400'>آخر تحديث بواسطة</div>
                                                        <div class='flex items-center space-x-2'>
                                                            <span class='text-sm font-bold text-primary-600 dark:text-primary-400'>{$updaterName}</span>
                                                            <span class='text-xs text-gray-500 dark:text-gray-400'>{$updatedAt}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            ");
                                        }

                                        return new HtmlString("<span class='text-sm text-gray-500 dark:text-gray-400'>ستكون معلومات التدقيق متاحة بعد الحفظ</span>");
                                    })
                                    ->visible(fn(string $operation): bool => $operation === 'edit'),
                            ])
                            ->visible(function (?Post $record) {
                                return Auth::user()->can('changeAuthor', $record);
                            }),

                        Forms\Components\Section::make('تحسين محركات البحث')
                            ->description('تحسين محركات البحث (SEO)')
                            ->icon('heroicon-o-magnifying-glass')
                            ->collapsed()
                            ->visible(function (?Post $record) {
                                $user = Auth::user();
                                return $user && $user->can('manageSeo', $record ?? new Post());
                            })
                            ->schema([
                                Forms\Components\Textarea::make('meta_title')
                                    ->label('عنوان الميتا')
                                    ->placeholder('اتركه فارغاً لاستخدام عنوان المقال')
                                    ->maxLength(70)
                                    ->helperText('موصى به: 50-60 حرف')
                                    ->rows(2),

                                Forms\Components\Textarea::make('meta_description')
                                    ->label('وصف الميتا')
                                    ->placeholder('اتركه فارغاً لاستخدام نظرة عامة على المقال')
                                    ->maxLength(160)
                                    ->helperText('موصى به: 150-160 حرف')
                                    ->rows(5),

                                Forms\Components\Section::make()
                                    ->schema([
                                        Forms\Components\Placeholder::make('seo_preview')
                                            ->label('معاينة جوجل')
                                            ->content(function (Get $get): HtmlString {
                                                $title = $get('meta_title') ?: $get('title');
                                                $description = $get('meta_description') ?: $get('content_overview');
                                                $url = config('app.url') . '/blog/' . ($get('slug') ?: Str::slug($get('title')));

                                                return new HtmlString("
                                                    <div class='text-base font-medium text-primary-600'>{$title}</div>
                                                    <div class='text-xs text-emerald-600'>{$url}</div>
                                                    <div class='mt-1 text-sm text-gray-600'>{$description}</div>
                                                ");
                                            }),
                                    ])
                                    ->compact(),

                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('generateSeoMetadata')
                                        ->label('إنشاء بيانات تحسين محركات البحث')
                                        ->icon('heroicon-m-sparkles')
                                        ->action(function (Get $get, Set $set) {
                                            $title = $get('title');
                                            $overview = $get('content_overview');

                                            // Generate meta title (up to 60 chars)
                                            $set('meta_title', Str::limit($title, 60));

                                            // Generate meta description (up to 155 chars)
                                            if ($overview) {
                                                $set('meta_description', Str::limit($overview, 155));
                                            }

                                            Notification::make()
                                                ->title('تم إنشاء بيانات تحسين محركات البحث')
                                                ->success()
                                                ->send();
                                        }),
                                ])->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $user = Auth::user();

                // Authors can only see their own posts
                if ($user && $user->hasRole('author')) {
                    $query->where(function ($q) use ($user) {
                        $q->where('blog_author_id', $user->id)
                          ->orWhere('created_by', $user->id);
                    });
                }

                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('مميز')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && $user->hasAnyRole(['super_admin', 'admin', 'editor']);
                    }),

                Tables\Columns\TextColumn::make('author.firstname')
                    ->label('المؤلف')
                    ->formatStateUsing(fn(Model $record) => "{$record->author->firstname} {$record->author->lastname}")
                    ->searchable(['firstname', 'lastname'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('الفئة')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),

                Tables\Columns\TextColumn::make('reading_time')
                    ->label('القراءة')
                    ->suffix(' دقيقة')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('view_count')
                    ->label('المشاهدات')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->visible(function () {
                        $user = auth()->user();
                        return $user->can('view_analytics_blog::post');
                    }),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('تاريخ النشر')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordClasses(fn(Post $record) => match ($record->is_featured) {
                true => '!border-x-2 !border-x-success-600 dark:!border-x-success-300',
                default => '',
            })
            ->defaultSort('updated_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(PostStatus::options()),

                Tables\Filters\SelectFilter::make('blog_category_id')
                    ->label('الفئة')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('blog_author_id')
                    ->label('المؤلف')
                    ->relationship('author', 'firstname')
                    ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->firstname} {$record->lastname}")
                    ->searchable()
                    ->preload()
                    ->visible(fn() => auth()->user()->hasAnyRole(['super_admin', 'admin', 'editor'])),

                Tables\Filters\Filter::make('is_featured')
                    ->label('المقالات المميزة')
                    ->query(fn(Builder $query): Builder => $query->where('is_featured', true))
                    ->visible(fn() => auth()->user()->hasAnyRole(['super_admin', 'admin', 'editor'])),

                Tables\Filters\Filter::make('published')
                    ->label('المقالات المنشورة')
                    ->query(fn(Builder $query): Builder => $query->published()),

                Tables\Filters\Filter::make('published_at')
                    ->label('منشور هذا الشهر')
                    ->query(fn(Builder $query): Builder => $query->whereMonth('published_at', now()->month)),

                Tables\Filters\Filter::make('pending_approval')
                    ->label('في انتظار الموافقة')
                    ->query(fn(Builder $query): Builder => $query->where('status', PostStatus::PENDING))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('عرض'),

                    Tables\Actions\EditAction::make()
                        ->label('تحرير')
                        ->visible(fn(Post $record) => auth()->user()->can('update', $record)),

                    Tables\Actions\Action::make('publish')
                        ->label('نشر')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Post $record) {
                            $record->update([
                                'status' => PostStatus::PUBLISHED,
                                'published_at' => now(),
                                'last_published_at' => now(),
                            ]);

                            Notification::make()
                                ->title('تم نشر المقال بنجاح')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->visible(fn(Post $record) =>
                            auth()->user()->can('publish', $record) &&
                            $record->status !== PostStatus::PUBLISHED
                        ),

                    Tables\Actions\Action::make('feature')
                        ->label($fn = fn(Post $record) => $record->is_featured ? 'إلغاء التمييز' : 'تمييز')
                        ->icon($fn = fn(Post $record) => $record->is_featured ? 'heroicon-o-star' : 'heroicon-o-star')
                        ->color($fn = fn(Post $record) => $record->is_featured ? 'warning' : 'success')
                        ->action(function (Post $record) {
                            $record->update(['is_featured' => !$record->is_featured]);

                            Notification::make()
                                ->title($record->is_featured ? 'تم تمييز المقال' : 'تم إلغاء تمييز المقال')
                                ->success()
                                ->send();
                        })
                        ->visible(fn(Post $record) => auth()->user()->can('feature', $record)),

                    Tables\Actions\Action::make('duplicate')
                        ->label('نسخ')
                        ->icon('heroicon-o-document-duplicate')
                        ->action(function (Post $record) {
                            $user = auth()->user();

                            $duplicate = $record->replicate();
                            $duplicate->title = "نسخة من " . $record->title;
                            $duplicate->slug = Str::slug($duplicate->title);
                            $duplicate->status = PostStatus::DRAFT;
                            $duplicate->published_at = null;
                            $duplicate->view_count = 0;
                            $duplicate->comments_count = 0;
                            $duplicate->is_featured = false;

                            if ($user->hasRole('author')) {
                                $duplicate->blog_author_id = $user->id;
                            }

                            $duplicate->save();

                            // Copy tags
                            $duplicate->syncTags($record->tags);

                            // Copy media
                            foreach ($record->getMedia('featured') as $media) {
                                $media->copy($duplicate, 'featured');
                            }

                            return redirect()->route('filament.admin.resources.blog.posts.edit', $duplicate->id);
                        }),

                    Tables\Actions\Action::make('approve')
                        ->label('موافقة')
                        ->icon('heroicon-o-check')
                        ->color('primary')
                        ->action(function (Post $record) {
                            $record->update([
                                'status' => PostStatus::PUBLISHED,
                                'published_at' => now(),
                                'last_published_at' => now(),
                            ]);

                            // Notify the author if available
                            if ($record->author) {
                                Notification::make()
                                    ->title('تمت الموافقة على مقالك ونشره!')
                                    ->body('المقال "' . $record->title . '" أصبح متاحاً الآن.')
                                    ->success()
                                    ->sendToDatabase($record->author);
                            }

                            Notification::make()
                                ->title('تمت الموافقة على المقال ونشره بنجاح')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->visible(fn(Post $record) =>
                            auth()->user()->can('approve', $record) &&
                            $record->status === PostStatus::PENDING
                        ),

                    Tables\Actions\DeleteAction::make()
                        ->label('حذف')
                        ->visible(fn(Post $record) => auth()->user()->can('delete', $record)),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد')
                        ->visible(fn() => auth()->user()->can('deleteAny', Post::class)),

                    Tables\Actions\BulkAction::make('publishSelected')
                        ->label('نشر المحدد')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records): void {
                            foreach ($records as $record) {
                                if (auth()->user()->can('publish', $record)) {
                                    $record->update([
                                        'status' => PostStatus::PUBLISHED,
                                        'published_at' => now(),
                                        'last_published_at' => now(),
                                    ]);
                                }
                            }

                            Notification::make()
                                ->title('تم نشر المقالات المحددة بنجاح')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->visible(fn() => auth()->user()->can('publish', Post::class)),

                    Tables\Actions\BulkAction::make('featureSelected')
                        ->label('تمييز المحدد')
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->action(function ($records): void {
                            foreach ($records as $record) {
                                if (auth()->user()->can('feature', $record)) {
                                    $record->update(['is_featured' => true]);
                                }
                            }

                            Notification::make()
                                ->title('تم تمييز المقالات المحددة بنجاح')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->visible(fn() => auth()->user()->can('feature', Post::class)),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        // Authors can only see their own posts
        if ($user && $user->hasRole('author')) {
            $query->where(function ($q) use ($user) {
                $q->where('blog_author_id', $user->id)
                  ->orWhere('created_by', $user->id);
            });
        }

        return $query;
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
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return __("menu.nav_group.blog");
    }

    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();

        if ($user && $user->hasRole('author')) {
            // Authors see count of their own posts
            return (string) static::getModel()::where('blog_author_id', $user->id)
                ->orWhere('created_by', $user->id)
                ->count();
        }

        return (string) static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'gray';
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->title;
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'الفئة' => $record->category->name,
            'المؤلف' => "{$record->author->firstname} {$record->author->lastname}",
            'الحالة' => $record->status->getLabel(),
        ];
    }
}
