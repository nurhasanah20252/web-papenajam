<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Throwable;

class SippSyncFailedNotification extends Notification
{
    use Queueable;

    protected Throwable $exception;

    protected array $stats;

    public function __construct(Throwable $exception, array $stats = [])
    {
        $this->exception = $exception;
        $this->stats = $stats;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $exceptionClass = class_basename($this->exception);

        return (new MailMessage)
            ->subject("Peringatan: Sinkronisasi SIPP Gagal - {$exceptionClass}")
            ->line('Sinkronisasi data SIPP telah gagal.')
            ->line("Error: {$this->exception->getMessage()}")
            ->line("Class: {$exceptionClass}")
            ->when(! empty($this->stats), function (MailMessage $mail) {
                return $mail->line('Statistik Sinkronisasi:')
                    ->line(json_encode($this->stats, JSON_PRETTY_PRINT));
            })
            ->line('Silakan periksa log aplikasi untuk detail lebih lanjut.')
            ->action('Lihat Dashboard', route('sipp.dashboard'))
            ->line('Terima kasih.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Sinkronisasi SIPP Gagal',
            'message' => $this->exception->getMessage(),
            'exception_class' => class_basename($this->exception),
            'stats' => $this->stats,
            'action_url' => route('sipp.dashboard'),
        ];
    }
}
