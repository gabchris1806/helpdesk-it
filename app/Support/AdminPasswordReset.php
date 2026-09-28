<?php

namespace App\Support;

use App\Notifications\AdminResetPasswordNotification;
use Filament\Facades\Filament;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Support\Facades\Password;

class AdminPasswordReset
{
    public static function broker(): string
    {
        return Filament::getAuthPasswordBroker() ?? config('auth.defaults.passwords', 'users');
    }

    public static function expirationMinutes(): int
    {
        return (int) config('auth.passwords.' . static::broker() . '.expire', 60);
    }

    public static function createToken(CanResetPassword $user): string
    {
        return Password::broker(static::broker())->createToken($user);
    }

    public static function notify(CanResetPassword $user, string $token): void
    {
        $user->notify(new AdminResetPasswordNotification(
            Filament::getResetPasswordUrl($token, $user),
            static::expirationMinutes(),
        ));
    }

    public static function send(CanResetPassword $user): void
    {
        static::notify($user, static::createToken($user));
    }

    public static function deliveryHint(): string
    {
        if (app()->environment('local') && config('mail.default') === 'log') {
            return 'Untuk lokal, link reset disimpan di storage/logs/laravel.log.';
        }

        return 'Silakan cek inbox email Anda. Jika belum masuk, periksa folder spam.';
    }
}
