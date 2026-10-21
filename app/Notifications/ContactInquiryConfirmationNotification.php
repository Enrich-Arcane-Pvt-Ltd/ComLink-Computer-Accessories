<?php

namespace App\Notifications;

use App\Models\ContactInquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContactInquiryConfirmationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    // ContactInquiry Record එක Notification එක ඇතුළට ගන්නවා
    public function __construct(public ContactInquiry $inquiry) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('We received your inquiry: '.$this->inquiry->subject)
            ->greeting('Dear '.$this->inquiry->name.',')
            ->line('Thank you for reaching out to us! We have successfully received your inquiry regarding "'.$this->inquiry->subject.'".')
            ->line('Our support team is currently reviewing your message and will get back to you as soon as possible (usually within 24 hours).')
            ->markdown('mail.inquiry-confirmation', [
                'originalMessage' => $this->inquiry->message,
            ]);
    }
}
