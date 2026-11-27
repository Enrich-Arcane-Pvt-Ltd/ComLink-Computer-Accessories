<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPlacedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;

    public string $viewOrderUrl;

    public string $viewReceiptUrl;

    public string $downloadReceiptUrl;

    public function __construct(Order $order)
    {
        $this->order = $order->loadMissing('items');
        $this->viewOrderUrl = $order->order_access_url;
        $this->viewReceiptUrl = $order->getReceiptUrl(true);
        $this->downloadReceiptUrl = $order->getReceiptUrl(false);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Order Confirmed #{$this->order->order_number} - ComLink Computer Accessories",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.order-placed',
        );
    }
}
