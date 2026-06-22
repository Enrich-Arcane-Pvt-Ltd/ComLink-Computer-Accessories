<?php

namespace App\Notifications;

use App\Models\ContactInquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContactInquiryReplyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ContactInquiry $inquiry,
        public string $replyMessage
    ) {}

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('RE: ' . $this->inquiry->subject)
            ->greeting('Hello ' . $this->inquiry->name . ',')
            ->line('Thank you for contacting us. Regarding your inquiry about "' . $this->inquiry->subject . '", please find our response below:')
            ->markdown('mail.inquiry-reply', [
                'replyMessage' => $this->replyMessage,
                'originalMessage' => $this->inquiry->message
            ]);
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }
}
