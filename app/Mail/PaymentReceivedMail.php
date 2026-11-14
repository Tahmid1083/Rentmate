<?php

namespace App\Mail;

use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Payment $payment,
        public Setting $setting,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment received — $' . number_format((float) $this->payment->amount, 2),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.payment-received');
    }
}
