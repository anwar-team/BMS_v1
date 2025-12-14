<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactUsResource\Actions\ReplyAction;
use App\Filament\Resources\ContactUsResource\Pages;
use App\Models\ContactUs;
use Filament\Tables\Actions as TablesActions;
use Filament\Infolists\Infolist;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker as FilamentDatePicker;

class ContactUsResource extends Resource
{
    protected static ?string $model = ContactUs::class;

    protected static ?string $slug = 'contact-us/inbox';

    // protected static ?string $recordTitleAttribute = 'firstname . " " . lastname';

    protected static ?string $navigationIcon = 'fluentui-mail-inbox-28';

    protected static ?int $navigationSort = 0;

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('معلومات المرسل')
                    ->schema([
                        Infolists\Components\TextEntry::make('firstname'),
                        Infolists\Components\TextEntry::make('lastname'),
                        Infolists\Components\TextEntry::make('email')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('phone')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('company')
                            ->label('الشركة'),
                        Infolists\Components\TextEntry::make('employees')
                            ->formatStateUsing(fn(?string $state): ?string => $state ? $state . ' موظف' : null),
                        Infolists\Components\TextEntry::make('title')
                            ->label('المسمى الوظيفي'),
                        Infolists\Components\TextEntry::make('ip_address')
                            ->visible(fn () => auth()->user()?->can('viewConfidential', ContactUs::class) ?? false),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('الرسالة')
                    ->schema([
                        Infolists\Components\TextEntry::make('subject')
                            ->label('الموضوع'),
                        Infolists\Components\TextEntry::make('message')
                            ->prose()
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('البيانات الوصفية')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->dateTime()
                            ->label('تاريخ الاستلام'),
                        Infolists\Components\TextEntry::make('metadata.source')
                            ->label('المصدر'),
                        Infolists\Components\TextEntry::make('metadata.utm_source')
                            ->label('مصدر الحملة'),
                        Infolists\Components\TextEntry::make('metadata.utm_medium')
                            ->label('الوسيط'),
                        Infolists\Components\TextEntry::make('metadata.utm_campaign')
                            ->label('اسم الحملة'),
                        Infolists\Components\TextEntry::make('metadata.referrer')
                            ->label('مرجع الإحالة'),
                        Infolists\Components\TextEntry::make('user_agent')
                            ->visible(fn () => auth()->user()?->can('viewConfidential', ContactUs::class) ?? false)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),

                // Show reply section if a reply has been sent
                Infolists\Components\Section::make('ردك')
                    ->schema([
                        Infolists\Components\TextEntry::make('reply_subject')
                            ->label('موضوع الرد'),
                        Infolists\Components\TextEntry::make('replied_at')
                            ->dateTime()
                            ->label('تاريخ الرد'),
                        Infolists\Components\TextEntry::make('repliedBy.firstname')
                            ->label('رد بواسطة')
                            ->formatStateUsing(function ($state, ContactUs $record) {
                                return $record->repliedBy
                                    ? "{$record->repliedBy->firstname} {$record->repliedBy->lastname}"
                                    : 'النظام';
                            }),
                        Infolists\Components\TextEntry::make('reply_message')
                            ->html()
                            ->columnSpanFull(),
                    ])
                    ->visible(
                        fn(ContactUs $record): bool =>
                        !empty($record->reply_message) && !empty($record->reply_subject)
                    )
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('name')
                            ->searchable()
                            ->weight('bold')
                            ->description(fn($record) => $record->email)
                            ->icon('heroicon-o-user')
                            ->limit(20)
                            ->alignLeft(),
                        Tables\Columns\TextColumn::make('phone')
                            ->searchable()
                            ->copyable()
                            ->toggleable()
                            ->toggledHiddenByDefault()
                            ->alignLeft(),
                    ])->space(1),
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('subject')
                            ->searchable()
                            ->limit(30)
                            ->alignLeft(),
                        Tables\Columns\TextColumn::make('company')
                            ->searchable()
                            ->toggleable()
                            ->alignLeft(),
                        Tables\Columns\TextColumn::make('employees')
                            ->formatStateUsing(fn(?string $state): ?string => $state ?? '')
                            ->toggleable()
                            ->toggledHiddenByDefault()
                            ->alignLeft(),
                    ])->space(1),
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('status')
                            ->badge()
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'new' => 'جديدة',
                                'read' => 'مقروءة',
                                'pending' => 'قيد الانتظار',
                                'responded' => 'تم الرد',
                                'closed' => 'مغلقة',
                                default => ucfirst($state),
                            })
                            ->color(fn(string $state): string => match ($state) {
                                'new' => 'danger',
                                'read' => 'warning',
                                'responded' => 'success',
                                'pending' => 'info',
                                'closed' => 'gray',
                                default => '',
                            })
                            ->alignLeft(),
                        Tables\Columns\TextColumn::make('created_at')
                            ->dateTime('M j, Y H:i')
                            ->label('تم الإرسال')
                            ->alignLeft(),
                    ])->alignment('center')->space(1),
                ])
            ])
            ->recordClasses(fn(ContactUs $record) => match ($record->status) {
                'new' => 'border-s-2 border-danger-600 dark:border-danger-300',
                'read' => 'border-s-2 border-warning-600 dark:border-warning-300',
                'responded' => 'border-s-2 border-success-600 dark:border-success-300',
                'pending' => 'border-s-2 border-info-600 dark:border-info-300',
                default => '',
            })
            ->filters([
                TrashedFilter::make(),

                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'new' => 'جديدة',
                        'read' => 'مقروءة',
                        'pending' => 'قيد الانتظار',
                        'responded' => 'تم الرد',
                        'closed' => 'مغلقة',
                    ]),

                Filter::make('created_at')
                    ->form([
                        FilamentDatePicker::make('from')->label('من'),
                        FilamentDatePicker::make('until')->label('إلى'),
                    ])
                    ->query(function ($query, $data) {
                        return $query
                            ->when($data['from'], fn($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'], fn($q, $date) => $q->whereDate('created_at', '<=', $date));
                    })
                    ->label('تاريخ الاستلام'),

                SelectFilter::make('company')
                    ->label('الشركة')
                    ->searchable()
                    ->options(
                        fn () => ContactUs::query()
                            ->distinct()
                            ->pluck('company', 'company')
                            ->filter()
                            ->toArray()
                    ),

                SelectFilter::make('employees')
                    ->label('عدد الموظفين')
                    ->options([
                        '1-10' => '1-10',
                        '11-50' => '11-50',
                        '51-200' => '51-200',
                        '201-500' => '201-500',
                        '501-1000' => '501-1000',
                    ]),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(2)
            ->filtersFormWidth(MaxWidth::ThreeExtraLarge)
                    ->bulkActions([
                TablesActions\BulkActionGroup::make([
                    TablesActions\DeleteBulkAction::make(),
                    TablesActions\ForceDeleteBulkAction::make(),
                    TablesActions\RestoreBulkAction::make(),
                    Tables\Actions\BulkAction::make('markAsRead')
                        ->label('وضع كمقروء')
                        ->icon('heroicon-o-check')
                        ->action(function (Collection $records): void {
                            $count = 0;
                            foreach ($records as $record) {
                                if ($record->status === 'new') {
                                    $record->markAsRead();
                                    $count++;
                                }
                            }

                            if ($count > 0) {
                                Notification::make()
                                    ->title("تم وضع {$count} رسالة كمقروءة")
                                    ->success()
                                    ->send();
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->actions([
                ReplyAction::make()
                    ->hiddenLabel()
                    ->button()
                    ->size('xs')
                    ->tooltip('الرد على هذه الرسالة')
                    ->color(fn(ContactUs $record): string => (empty($record->reply_message) || empty($record->reply_subject)) ? 'success' : 'gray')
                    ->disabled(fn(ContactUs $record): bool => !empty($record->reply_message) || !empty($record->reply_subject)),
                TablesActions\ActionGroup::make([
                    TablesActions\ViewAction::make('view')
                        ->mutateRecordDataUsing(function (array $data, ContactUs $record): array {
                            if ($record->status === 'new') {
                                $data['status'] = 'read';
                                $record->markAsRead();
                            }
                            return $data;
                        }),
                    TablesActions\Action::make('markAsRead')
                        ->label('وضع كمقروء')
                        ->icon('heroicon-o-check')
                        ->action(function (ContactUs $record): void {
                            if ($record->status === 'new') {
                                $record->markAsRead();
                                $record->refresh();

                                Notification::make()
                                    ->title('تم وضع الرسالة كمقروءة')
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('الرسالة مقروءة بالفعل')
                                    ->info()
                                    ->send();
                            }
                        })
                        ->visible(fn (ContactUs $record): bool => $record->status === 'new'),
                    TablesActions\DeleteAction::make('delete'),
                    TablesActions\ForceDeleteAction::make(),
                    TablesActions\RestoreAction::make(),
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
            'index' => Pages\ListContactUs::route('/'),
        ];
    }

    public static function getLabel(): string
    {
        return 'صندوق الوارد';
    }

    public static function getPluralLabel(): string
    {
        return 'تواصل معنا';
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'new')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'gray';
    }

    public static function getNavigationLabel(): string
    {
        return 'صندوق الوارد';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return $record->firstname . ' ' . $record->lastname;
    }
}
