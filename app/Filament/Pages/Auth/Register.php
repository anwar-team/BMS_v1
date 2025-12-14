<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class Register extends BaseRegister
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_type')
                    ->label('نوع المستخدم')
                    ->options([
                        'reader' => 'قارئ/باحث',
                        'library_team' => 'فريق المكتبة',
                    ])
                    ->required()
                    ->native(false)
                    ->placeholder('اختر نوع المستخدم')
                    ->helperText('القراء والباحثون لا يمكنهم الوصول للوحة التحكم، بينما فريق المكتبة يمكنهم الوصول إليها'),
                    
                TextInput::make('firstname')
                    ->label('الاسم الأول')
                    ->required()
                    ->maxLength(255)
                    ->autofocus(),
                    
                TextInput::make('lastname')
                    ->label('اسم العائلة')
                    ->required()
                    ->maxLength(255),
                    
                TextInput::make('username')
                    ->label('اسم المستخدم')
                    ->required()
                    ->maxLength(255)
                    ->unique(User::class)
                    ->helperText('اسم المستخدم يجب أن يكون فريداً'),
                    
                $this->getEmailFormComponent()
                    ->label('البريد الإلكتروني')
                    ->unique(User::class),
                    
                $this->getPasswordFormComponent()
                    ->label('كلمة المرور')
                    ->rule(Password::default()),
                    
                $this->getPasswordConfirmationFormComponent()
                    ->label('تأكيد كلمة المرور'),
            ])
            ->statePath('data');
    }

    protected function handleRegistration(array $data): Model
    {
        $user = User::create([
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // تعيين الأدوار حسب نوع المستخدم
        if ($data['user_type'] === 'library_team') {
            // فريق المكتبة يحصل على دور editor أو author حسب الحاجة
            $user->assignRole('author');
        } else {
            // القراء والباحثون لا يحصلون على أي دور خاص
            // يمكن إنشاء دور 'reader' إذا لزم الأمر
            // $user->assignRole('reader');
        }

        return $user;
    }

    public function getHeading(): string | Htmlable
    {
        return 'إنشاء حساب جديد';
    }

    protected function getFormActions(): array
    {
        return [
            $this->getRegisterFormAction()
                ->label('إنشاء الحساب'),
        ];
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }
}