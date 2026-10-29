<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class AlerteSurveillance extends Mailable
{
    /** @param array<string, string> $problemes */
    public function __construct(public array $problemes)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: '⚠ Alerte paiements '.config('app.name'));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.alerte-surveillance');
    }
}