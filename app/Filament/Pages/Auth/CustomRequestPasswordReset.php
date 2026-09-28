<?php

namespace App\Filament\Pages\Auth;

use App\Support\AdminPasswordReset;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Exception;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\PasswordReset\RequestPasswordReset;
use Illuminate\Auth\Events\PasswordResetLinkSent;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Support\Facades\Password;

class CustomRequestPasswordReset extends RequestPasswordReset
{
    public function request(): void
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return;
        }

        $data = $this->form->getState();

        $status = Password::broker(AdminPasswordReset::broker())->sendResetLink(
            $this->getCredentialsFromFormData($data),
            function (CanResetPassword $user, string $token): void {
                if (($user instanceof FilamentUser) && (! $user->canAccessPanel(Filament::getCurrentPanel()))) {
                    return;
                }

                if (! method_exists($user, 'notify')) {
                    $userClass = $user::class;

                    throw new Exception("Model [{$userClass}] does not have a [notify()] method.");
                }

                AdminPasswordReset::notify($user, $token);

                if (class_exists(PasswordResetLinkSent::class)) {
                    event(new PasswordResetLinkSent($user));
                }
            },
        );

        if ($status !== Password::RESET_LINK_SENT) {
            $this->getFailureNotification($status)?->send();

            return;
        }

        $this->getSentNotification($status)?->send();
        $this->form->fill();
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Email Admin')
            ->email()
            ->required()
            ->autocomplete()
            ->autofocus()
            ->placeholder('admin@gmail.com');
    }

    protected function getSentNotification(string $status): ?Notification
    {
        return Notification::make()
            ->title('Link reset password berhasil dibuat')
            ->body(AdminPasswordReset::deliveryHint())
            ->success();
    }

    public function loginAction(): Action
    {
        return Action::make('login')
            ->link()
            ->label('Kembali ke login')
            ->url(filament()->getLoginUrl());
    }

    public function getTitle(): string
    {
        return 'Lupa Password';
    }

    public function getHeading(): string
    {
        return 'Reset Password Admin';
    }

    protected function getRequestFormAction(): Action
    {
        return Action::make('request')
            ->label('Kirim Link Reset')
            ->submit('request');
    }
}
