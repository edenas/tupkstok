<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactFormMessage extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array{name: string, email: string, subject: string, message: string}  $messageData
     */
    public function __construct(public array $messageData)
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [
                new Address($this->messageData['email'], $this->messageData['name']),
            ],
            subject: 'Kontaktine forma: '.$this->messageData['subject'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-form-message',
        );
    }
}
