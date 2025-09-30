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
                Infolists\Components\Section::make(__('contact_us.sections.contact_information'))
                    ->schema([
                        Infolists\Components\TextEntry::make('firstname'),
                        Infolists\Components\TextEntry::make('lastname'),
                        Infolists\Components\TextEntry::make('email')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('phone')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('company')
                            ->label(__('contact_us.fields.company')),
                        Infolists\Components\TextEntry::make('employees')
                            ->formatStateUsing(fn(?string $state): ?string => $state ? $state . ' ' . __('contact_us.fields.employees') : null),
                        Infolists\Components\TextEntry::make('title')
                            ->label(__('contact_us.fields.job_title')),
                        Infolists\Components\TextEntry::make('ip_address')
                            ->visible(fn () => auth()->user()?->can('viewConfidential', ContactUs::class) ?? false),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make(__('contact_us.sections.message'))
                    ->schema([
                        Infolists\Components\TextEntry::make('subject')
                            ->label(__('contact_us.fields.subject')),
                        Infolists\Components\TextEntry::make('message')
                            ->prose()
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make(__('contact_us.sections.metadata'))
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->dateTime()
                            ->label(__('contact_us.fields.received')),
                        Infolists\Components\TextEntry::make('metadata.source')
                            ->label(__('contact_us.fields.source')),
                        Infolists\Components\TextEntry::make('metadata.utm_source')
                            ->label(__('contact_us.fields.campaign_source')),
                        Infolists\Components\TextEntry::make('metadata.utm_medium')
                            ->label(__('contact_us.fields.campaign_medium')),
                        Infolists\Components\TextEntry::make('metadata.utm_campaign')
                            ->label(__('contact_us.fields.campaign_name')),
                        Infolists\Components\TextEntry::make('metadata.referrer')
                            ->label(__('contact_us.fields.referrer')),
                        Infolists\Components\TextEntry::make('user_agent')
                            ->visible(fn () => auth()->user()?->can('viewConfidential', ContactUs::class) ?? false)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),

                // Show reply section if a reply has been sent
                Infolists\Components\Section::make(__('contact_us.sections.your_reply'))
                    ->schema([
                        Infolists\Components\TextEntry::make('reply_subject')
                            ->label(__('contact_us.fields.reply_subject')),
                        Infolists\Components\TextEntry::make('replied_at')
                            ->dateTime()
                            ->label(__('contact_us.fields.replied_at')),
                        Infolists\Components\TextEntry::make('repliedBy.firstname')
                            ->label(__('contact_us.fields.replied_by'))
                            ->formatStateUsing(function ($state, ContactUs $record) {
                                return $record->repliedBy
                                    ? "{$record->repliedBy->firstname} {$record->repliedBy->lastname}"
                                    : __('contact_us.fields.replied_by');
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
                            ->formatStateUsing(fn(string $state): string => __("contact_us.status.{" . $state . "}") ?? ucfirst($state))
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
                            ->label(__('contact_us.fields.sent_at'))
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
                    ->label(__('contact_us.filters.status'))
                    ->options([
                        'new' => __('contact_us.status.new'),
                        'read' => __('contact_us.status.read'),
                        'pending' => __('contact_us.status.pending'),
                        'responded' => __('contact_us.status.responded'),
                        'closed' => __('contact_us.status.closed'),
                    ]),

                Filter::make('created_at')
                    ->form([
                        FilamentDatePicker::make('from')->label(__('contact_us.filters.from')),
                        FilamentDatePicker::make('until')->label(__('contact_us.filters.until')),
                    ])
                    ->query(function ($query, $data) {
                        return $query
                            ->when($data['from'], fn($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'], fn($q, $date) => $q->whereDate('created_at', '<=', $date));
                    })
                    ->label('Received Date'),

                SelectFilter::make('company')
                    ->label(__('contact_us.fields.company'))
                    ->searchable()
                    ->options(
                        fn () => ContactUs::query()
                            ->distinct()
                            ->pluck('company', 'company')
                            ->filter()
                            ->toArray()
                    ),

                SelectFilter::make('employees')
                    ->label(__('contact_us.fields.employees'))
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
                        ->label(__('contact_us.actions.mark_as_read'))
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
                                    ->title(__('contact_us.notifications.marked_as_read', ['count' => $count]))
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
                        ->tooltip(__('contact_us.actions.reply_tooltip'))
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
                        ->label(__('contact_us.actions.mark_as_read'))
                        ->icon('heroicon-o-check')
                        ->action(function (ContactUs $record): void {
                            if ($record->status === 'new') {
                                $record->markAsRead();
                                $record->refresh();

                                Notification::make()
                                    ->title(__('contact_us.notifications.message_marked_read'))
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title(__('contact_us.notifications.message_already_read'))
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
        return 'Inbox';
    }

    public static function getPluralLabel(): string
    {
        return 'Contact Us';
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
        return 'Inbox';
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
