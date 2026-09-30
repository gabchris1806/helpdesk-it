<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $resetUrl,
        protected int $expirationMinutes,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $recipientName = trim((string) ($notifiable->name ?? 'Pengguna')) ?: 'Pengguna';

        return (new MailMessage())
            ->subject('Reset Password Akun Helpdesk')
            ->greeting("Halo {$recipientName},")
            ->line('Kami menerima permintaan untuk mereset password akun Helpdesk Anda.')
            ->line('Klik tombol di bawah ini untuk membuat password baru.')
            ->action('Reset Password', $this->resetUrl)
            ->line("Link reset ini berlaku selama {$this->expirationMinutes} menit.")
            ->line('Jika Anda tidak merasa meminta reset password, abaikan email ini.');
    }
}
