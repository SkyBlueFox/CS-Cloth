<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otpCode,
        public string $headline,
        public string $instruction,
        public int $expiresInMinutes = 10
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->headline
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp-code'
        );
    }
}
