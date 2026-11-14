<?php

namespace App\Mail;

use App\Models\RentInvoice;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OverdueReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public RentInvoice $invoice,
        public Setting $setting,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Overdue reminder — ' . $this->invoice->invoice_number,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.overdue-reminder');
    }
}
