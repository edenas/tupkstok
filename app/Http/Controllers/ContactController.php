<?php

namespace App\Http\Controllers;

use App\Mail\ContactFormMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ContactController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:5000'],
        ], [
            'name.required' => __('messages.contact.validation.name_required'),
            'email.required' => __('messages.contact.validation.email_required'),
            'email.email' => __('messages.contact.validation.email_valid'),
            'subject.required' => __('messages.contact.validation.subject_required'),
            'message.required' => __('messages.contact.validation.message_required'),
        ]);

        try {
            $recipient = config('mail.contact_to') ?: 'edenas.pocius@gmail.com';

            Mail::to($recipient)->send(new ContactFormMessage($validated));
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('contact_error', __('messages.contact.mail_error'));
        }

        return back()->with('contact_status', __('messages.contact.mail_success'));
    }
}
