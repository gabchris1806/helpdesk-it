<?php

namespace App\Filament\Pages\Auth;

use App\Support\AdminPasswordReset;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\PasswordReset\RequestPasswordReset;
use Illuminate\Auth\Events\PasswordResetLinkSent;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
use Throwable;

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

        try {
            $status = Password::broker(AdminPasswordReset::broker())->sendResetLink(
                $this->getCredentialsFromFormData($data),
                function (CanResetPassword $user, string $token): void {
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
        } catch (Throwable $exception) {
            Log::warning('Password reset email could not be sent.', [
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            Notification::make()
                ->title('Email reset tidak dapat dikirim')
                ->body('Silakan hubungi administrator untuk memperbarui password akun Anda.')
                ->danger()
                ->send();

            return;
        }

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
            ->label('Email')
            ->email()
            ->required()
            ->autocomplete()
            ->autofocus()
            ->placeholder('nama@email.com');
    }

    protected function getSentNotification(string $status): ?Notification
    {
        return Notification::make()
            ->title('Link reset password berhasil dibuat')
            ->body(AdminPasswordReset::deliveryHint())
            ->success();
    }

    public function getSubheading(): ?string
    {
        if (config('mail.default') === 'log') {
            return 'Pengiriman email belum aktif di aplikasi ini. Hubungi administrator untuk memperbarui password akun Anda.';
        }

        return 'Masukkan email akun Anda untuk menerima tautan pengaturan ulang password.';
    }

    public function loginAction(): Action
    {
        return Action::make('login')
            ->link()
            ->label('Kembali ke login')
            ->url(route('login'));
    }

    public function getTitle(): string
    {
        return 'Lupa Password';
    }

    public function getHeading(): string
    {
        return 'Reset Password Akun';
    }

    protected function getRequestFormAction(): Action
    {
        return Action::make('request')
            ->label('Kirim Link Reset')
            ->submit('request');
    }
}
