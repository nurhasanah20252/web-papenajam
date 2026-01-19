<?php

namespace App\Notifications;

use App\Models\PpidRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PpidRequestCompleted extends Notification implements ShouldQueue
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
        $statusText = $this->ppidRequest->status->value === 'completed' ? 'Completed' : 'Response Provided';

        return (new MailMessage)
            ->subject('PPID Request '.$statusText.' - '.$this->ppidRequest->request_number)
            ->greeting('Dear '.$this->ppidRequest->applicant_name.',')
            ->line('Your PPID request has been processed.')
            ->line('**Request Number:** '.$this->ppidRequest->request_number)
            ->line('**Status:** '.ucfirst($this->ppidRequest->status->value))
            ->line('**Response:**')
            ->line($this->ppidRequest->response)
            ->line('If you have any questions or need further assistance, please do not hesitate to contact us.')
            ->action('View Request', url('/ppid/tracking?number='.$this->ppidRequest->request_number))
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
            'response' => $this->ppidRequest->response,
        ];
    }
}
