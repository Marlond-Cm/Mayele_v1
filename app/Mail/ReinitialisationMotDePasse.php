<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ReinitialisationMotDePasse extends Mailable
{
    public function __construct(
        public User $utilisateur,
        public string $lien,
        public int $minutes,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Réinitialise ton mot de passe '.config('app.name'));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.reinitialisation');
    }
}