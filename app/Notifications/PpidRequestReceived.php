<?php

namespace App\Notifications;

use App\Models\PpidRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PpidRequestReceived extends Notification implements ShouldQueue
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
            ->subject('PPID Request Received - '.$this->ppidRequest->request_number)
            ->greeting('Dear '.$this->ppidRequest->applicant_name.',')
            ->line('Your PPID request has been successfully received.')
            ->line('**Request Number:** '.$this->ppidRequest->request_number)
            ->line('**Subject:** '.$this->ppidRequest->subject)
            ->line('**Request Type:** '.ucfirst(str_replace('_', ' ', $this->ppidRequest->request_type)))
            ->line('We will process your request within the statutory timeframe. You can track the status of your request using the request number above.')
            ->action('Track Request', url('/ppid/tracking?number='.$this->ppidRequest->request_number))
            ->line('If you have any questions, please contact us.')
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
            'subject' => $this->ppidRequest->subject,
            'status' => $this->ppidRequest->status,
        ];
    }
}
