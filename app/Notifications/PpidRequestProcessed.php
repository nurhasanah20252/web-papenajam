<?php

namespace App\Notifications;

use App\Models\PpidRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PpidRequestProcessed extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public PpidRequest $ppidRequest) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('PPID Request Update - '.$this->ppidRequest->request_number)
            ->greeting('Dear '.$this->ppidRequest->applicant_name.',')
            ->line('Your PPID request status has been updated.')
            ->line('**Request Number:** '.$this->ppidRequest->request_number)
            ->line('**Current Status:** '.ucfirst($this->ppidRequest->status->value))
            ->line('We are currently processing your request. You will receive another notification when it is completed.')
            ->action('Track Request', url('/ppid/tracking?number='.$this->ppidRequest->request_number))
            ->line('Thank you for your patience.')
            ->salutation('Regards, PPID Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'request_number' => $this->ppidRequest->request_number,
            'status' => $this->ppidRequest->status,
        ];
    }
}
