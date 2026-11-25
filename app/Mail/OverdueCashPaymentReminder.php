<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class OverdueCashPaymentReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $memberName,
        public readonly Collection $overduePeriods,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pengingat pembayaran kas bulanan',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cash-payment-reminder',
        );
    }
}
