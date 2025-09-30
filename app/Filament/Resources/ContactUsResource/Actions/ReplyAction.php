<?php

namespace App\Filament\Resources\ContactUsResource\Actions;

use App\Models\ContactUs;
use App\Mail\ContactReply;
use App\Settings\MailSettings;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class ReplyAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->icon('heroicon-o-paper-airplane');

        $this->color('success');

    $this->modalHeading(fn (ContactUs $record): string => "الرد على رسالة من {$record->name}");

    $this->modalDescription(fn (ContactUs $record): string => "الرد على الموضوع: {$record->subject}");

        $this->modalIcon(FilamentIcon::resolve('heroicon-o-paper-airplane'));

        $this->form([
            Forms\Components\TextInput::make('reply_subject')
                ->label('الموضوع')
                ->required()
                ->maxLength(255)
                ->default(fn (ContactUs $record): string => "RE: {$record->subject}"),
            Forms\Components\RichEditor::make('reply_message')
                ->label('الرسالة')
                ->required()
                ->fileAttachmentsDisk('public')
                ->fileAttachmentsDirectory('contact-replies')
                ->fileAttachmentsVisibility('public')
                ->columnSpanFull(),
        ]);

        $this->action(function (array $data, ContactUs $record): void {
            try {
                // Use the addReply helper method to update the record
                $record->addReply(
                    $data['reply_subject'],
                    $data['reply_message'],
                    auth()->user()
                );

                // Get mail settings
                $mailSettings = app(MailSettings::class);

                // Check if mail settings are configured
                if (!$mailSettings->isMailSettingsConfigured()) {
                    Log::warning('Mail settings not configured. Reply email not sent for contact ID: ' . $record->id);

                    Notification::make()
                        ->title('تم حفظ الرد ولكن لم يتم إرسال البريد الإلكتروني')
                        ->body('إعدادات البريد غير مفعلة. لم يتم إرسال البريد الإلكتروني.')
                        ->warning()
                        ->send();

                    return;
                }

                // Load mail settings
                $mailSettings->loadMailSettingsToConfig();

                // Send the reply email
                try {
                    Mail::to($record->email)
                        ->queue(new ContactReply($record));

                    Log::info('Contact reply email queued successfully', [
                        'contact_id' => $record->id,
                        'recipient' => $record->email
                    ]);

                    Notification::make()
                        ->title('تم إرسال الرد بنجاح')
                        ->success()
                        ->send();

                } catch (\Exception $e) {
                    Log::error('Failed to send contact reply email', [
                        'contact_id' => $record->id,
                        'recipient' => $record->email,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);

                    Notification::make()
                        ->title('تم حفظ الرد ولكن فشل إرسال البريد الإلكتروني')
                        ->body("خطأ: {$e->getMessage()}")
                        ->warning()
                        ->send();
                }

            } catch (\Exception $e) {
                Log::error('Error processing contact reply', [
                    'contact_id' => $record->id ?? 'unknown',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                Notification::make()
                    ->title('حدث خطأ أثناء معالجة الرد')
                    ->body("خطأ: {$e->getMessage()}")
                    ->danger()
                    ->send();
            }
        });
    }

    public static function getDefaultName(): ?string
    {
        return 'reply';
    }
}
