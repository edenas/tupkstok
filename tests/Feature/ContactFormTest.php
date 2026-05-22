<?php

namespace Tests\Feature;

use App\Mail\ContactFormMessage;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    public function test_contact_form_sends_email_to_configured_recipient(): void
    {
        Mail::fake();
        config(['mail.contact_to' => 'edenas.pocius@gmail.com']);

        $response = $this->from('/kontaktai')->post('/kontaktai', [
            'name' => 'Visitor Name',
            'email' => 'visitor@example.com',
            'subject' => 'Project question',
            'message' => 'Hello from the contact form.',
        ]);

        $response
            ->assertRedirect('/kontaktai')
            ->assertSessionHas('contact_status');

        Mail::assertSent(ContactFormMessage::class, function (ContactFormMessage $mail) {
            $envelope = $mail->envelope();

            return $mail->hasTo('edenas.pocius@gmail.com')
                && str_contains($envelope->subject, 'Project question')
                && $envelope->replyTo[0]->address === 'visitor@example.com'
                && $mail->messageData['message'] === 'Hello from the contact form.';
        });
    }

    public function test_contact_form_validates_required_fields(): void
    {
        Mail::fake();

        $response = $this->from('/kontaktai')->post('/kontaktai', [
            'name' => '',
            'email' => 'not-an-email',
            'subject' => '',
            'message' => '',
        ]);

        $response
            ->assertRedirect('/kontaktai')
            ->assertSessionHasErrors(['name', 'email', 'subject', 'message']);

        Mail::assertNothingSent();
    }

    public function test_contact_form_shows_friendly_error_when_mail_fails(): void
    {
        Mail::shouldReceive('to')
            ->once()
            ->with('edenas.pocius@gmail.com')
            ->andThrow(new \RuntimeException('SMTP unavailable'));

        $response = $this->from('/kontaktai')->post('/kontaktai', [
            'name' => 'Visitor Name',
            'email' => 'visitor@example.com',
            'subject' => 'Project question',
            'message' => 'Hello from the contact form.',
        ]);

        $response
            ->assertRedirect('/kontaktai')
            ->assertSessionHas('contact_error')
            ->assertSessionHasInput('email', 'visitor@example.com');

        Mockery::close();
    }
}
