<?php

namespace App\Filament\Pages\Auth;

use Filament\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\PasswordReset\ResetPassword;
use Illuminate\Validation\Rules\Password as PasswordRule;

class CustomResetPassword extends ResetPassword
{
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Email Admin')
            ->disabled()
            ->autofocus();
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Password Baru')
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->rule(PasswordRule::default())
            ->same('passwordConfirmation');
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('passwordConfirmation')
            ->label('Konfirmasi Password Baru')
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->dehydrated(false);
    }

    public function getTitle(): string
    {
        return 'Buat Password Baru';
    }

    public function getHeading(): string
    {
        return 'Atur Password Admin Baru';
    }

    public function getResetPasswordFormAction(): Action
    {
        return Action::make('resetPassword')
            ->label('Simpan Password Baru')
            ->submit('resetPassword');
    }
}
