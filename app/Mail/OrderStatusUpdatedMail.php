<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;

    public string $previousStatus;

    public string $newStatus;

    public string $viewOrderUrl;

    public string $viewReceiptUrl;

    public function __construct(Order $order, string $previousStatus, string $newStatus)
    {
        $this->order = $order->loadMissing('items');
        $this->previousStatus = $previousStatus;
        $this->newStatus = $newStatus;
        $this->viewOrderUrl = $order->order_access_url;
        $this->viewReceiptUrl = $order->getReceiptUrl(true);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Order #{$this->order->order_number} Status Updated: {$this->newStatus}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.order-status-updated',
        );
    }
}
