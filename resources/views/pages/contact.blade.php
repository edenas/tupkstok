@extends('layouts.app')

@section('content')
@php
    $contactDetails = [
        [
            'key' => 'name',
            'label' => __('messages.contact.info_labels.name'),
            'value' => 'Edenas Pocius',
            'url' => null,
            'icon' => 'user',
        ],
        [
            'key' => 'email',
            'label' => __('messages.contact.info_labels.email'),
            'value' => 'edenas.pocius@gmail.com',
            'url' => 'mailto:edenas.pocius@gmail.com',
            'icon' => 'mail',
        ],
        [
            'key' => 'phone',
            'label' => __('messages.contact.info_labels.phone'),
            'value' => '+370 609 60686',
            'url' => 'tel:+37060960686',
            'icon' => 'phone',
        ],
        [
            'key' => 'messenger',
            'label' => __('messages.contact.info_labels.messenger'),
            'value' => __('messages.contact.messenger_value'),
            'url' => 'https://m.me/edenas.pocius.1',
            'external' => true,
            'icon' => 'messenger',
        ],
        [
            'key' => 'location',
            'label' => __('messages.contact.info_labels.location'),
            'value' => __('messages.contact.location_value'),
            'url' => null,
            'icon' => 'pin',
        ],
    ];

    $socialLinks = [
        [
            'label' => 'LinkedIn',
            'url' => 'https://www.linkedin.com/in/edenas-pocius-0b4a59191/',
            'icon' => 'linkedin',
        ],
        [
            'label' => 'Instagram',
            'url' => 'https://www.instagram.com/edenas_pocius/',
            'icon' => 'instagram',
        ],
        [
            'label' => 'Facebook',
            'url' => 'https://www.facebook.com/edenas.pocius.1/',
            'icon' => 'facebook',
        ],
    ];
@endphp

<section class="contact-page">
    <div class="contact-page__container">
        <section class="contact-page__hero">
            <div class="contact-page__hero-copy">
                <p class="contact-page__eyebrow">{{ __('messages.contact.eyebrow') }}</p>
                <h1 class="contact-page__title">{{ __('messages.contact.title') }}</h1>
                <p class="contact-page__lead">{{ __('messages.contact.lead') }}</p>
            </div>

            <div class="contact-page__hero-visual" aria-hidden="true">
                <svg class="contact-page__message-mark" viewBox="0 0 120 120" focusable="false">
                    <path d="M36 35h45a19 19 0 0 1 19 19v17a19 19 0 0 1-19 19H59L37 105V90h-1a19 19 0 0 1-19-19V54a19 19 0 0 1 19-19Z"/>
                    <path d="M25 26h45a19 19 0 0 1 19 19v11"/>
                    <circle cx="49" cy="63" r="5"/>
                    <circle cx="62" cy="63" r="5"/>
                    <circle cx="75" cy="63" r="5"/>
                </svg>
            </div>
        </section>

        <div class="contact-page__layout">
            <aside class="contact-page__info-panel">
                <h2>{{ __('messages.contact.information') }}</h2>

                <div class="contact-page__details">
                    @foreach ($contactDetails as $detail)
                        <{{ $detail['url'] ? 'a' : 'div' }}
                            @if ($detail['url'])
                                href="{{ $detail['url'] }}"
                                @if (!empty($detail['external'])) target="_blank" rel="noopener noreferrer" @endif
                            @endif
                            class="contact-page__detail"
                        >
                            <span class="contact-page__detail-icon" aria-hidden="true">
                                @if ($detail['icon'] === 'user')
                                    <svg viewBox="0 0 24 24" focusable="false"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>
                                @elseif ($detail['icon'] === 'mail')
                                    <svg viewBox="0 0 24 24" focusable="false"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 7 9-7"/></svg>
                                @elseif ($detail['icon'] === 'phone')
                                    <svg viewBox="0 0 24 24" focusable="false"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.32 1.77.59 2.61a2 2 0 0 1-.45 2.11L8 9.7a16 16 0 0 0 6.3 6.3l1.26-1.25a2 2 0 0 1 2.11-.45c.84.27 1.71.47 2.61.59A2 2 0 0 1 22 16.92Z"/></svg>
                                @elseif ($detail['icon'] === 'messenger')
                                    <svg viewBox="0 0 24 24" focusable="false"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7A8.38 8.38 0 0 1 4 11.5a8.5 8.5 0 0 1 17 0Z"/><path d="m8 13 2.5-2.5 2 2L16 9"/></svg>
                                @else
                                    <svg viewBox="0 0 24 24" focusable="false"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                @endif
                            </span>
                            <span class="contact-page__detail-label">{{ $detail['label'] }}</span>
                            <span class="contact-page__detail-value">{{ $detail['value'] }}</span>
                        </{{ $detail['url'] ? 'a' : 'div' }}>
                    @endforeach
                </div>

                <div class="contact-page__socials">
                    <h3>{{ __('messages.contact.social') }}</h3>
                    <div class="contact-page__social-list">
                        @foreach ($socialLinks as $link)
                            <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer">
                                @if ($link['icon'] === 'linkedin')
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.94 8.75H3.56v10.69h3.38V8.75ZM5.25 4.56a1.96 1.96 0 1 0 0 3.92 1.96 1.96 0 0 0 0-3.92Zm13.97 8.76c0-3.22-1.72-4.72-4.02-4.72a3.48 3.48 0 0 0-3.13 1.72V8.75H8.83v10.69h3.37v-5.29c0-1.4.27-2.75 2-2.75 1.7 0 1.72 1.59 1.72 2.84v5.2h3.38l-.08-6.12Z"/></svg>
                                @elseif ($link['icon'] === 'instagram')
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="4" width="16" height="16" rx="4"/><circle cx="12" cy="12" r="3.5"/><path d="M17.2 6.8h.01"/></svg>
                                @else
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 8h2V4h-3a5 5 0 0 0-5 5v3H6v4h3v4h4v-4h3l1-4h-4V9a1 1 0 0 1 1-1Z"/></svg>
                                @endif
                                <span>{{ $link['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </aside>

            <section class="contact-page__form-panel">
                <h2>{{ __('messages.contact.form_title') }}</h2>

                @if (session('contact_status'))
                    <div class="contact-page__alert contact-page__alert--success" role="status">
                        {{ session('contact_status') }}
                    </div>
                @endif

                @if (session('contact_error'))
                    <div class="contact-page__alert contact-page__alert--error" role="alert">
                        {{ session('contact_error') }}
                    </div>
                @endif

                <form class="contact-page__form" action="{{ \App\Support\LocalizedUrl::route('contact.submit') }}" method="POST" data-contact-form>
                    @csrf
                    <div class="contact-page__field">
                        <label for="contact-name">{{ __('messages.contact.your_name') }}</label>
                        <input id="contact-name" name="name" type="text" autocomplete="name" placeholder="{{ __('messages.contact.placeholders.name') }}" value="{{ old('name') }}" required @error('name') aria-invalid="true" aria-describedby="contact-name-error" @enderror>
                        @error('name')
                            <p id="contact-name-error" class="contact-page__error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="contact-page__field">
                        <label for="contact-email">{{ __('messages.contact.your_email') }}</label>
                        <input id="contact-email" name="email" type="email" autocomplete="email" placeholder="{{ __('messages.contact.placeholders.email') }}" value="{{ old('email') }}" required @error('email') aria-invalid="true" aria-describedby="contact-email-error" @enderror>
                        @error('email')
                            <p id="contact-email-error" class="contact-page__error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="contact-page__field">
                        <label for="contact-subject">{{ __('messages.contact.subject') }}</label>
                        <input id="contact-subject" name="subject" type="text" placeholder="{{ __('messages.contact.placeholders.subject') }}" value="{{ old('subject') }}" required @error('subject') aria-invalid="true" aria-describedby="contact-subject-error" @enderror>
                        @error('subject')
                            <p id="contact-subject-error" class="contact-page__error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="contact-page__field">
                        <label for="contact-message">{{ __('messages.contact.message') }}</label>
                        <textarea id="contact-message" name="message" rows="7" placeholder="{{ __('messages.contact.placeholders.message') }}" required @error('message') aria-invalid="true" aria-describedby="contact-message-error" @enderror>{{ old('message') }}</textarea>
                        @error('message')
                            <p id="contact-message-error" class="contact-page__error">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="contact-page__submit" data-contact-submit>
                        {{ __('messages.common.send_message') }}
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>
                        </svg>
                    </button>
                </form>
            </section>
        </div>

        <section class="contact-page__cta-card">
            <div class="contact-page__cta-copy">
                <span class="contact-page__cta-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" focusable="false">
                        <path d="M4 13a8 8 0 0 1 16 0"/><path d="M5 13v4a2 2 0 0 0 2 2h1v-8H7a2 2 0 0 0-2 2Z"/><path d="M19 13v4a2 2 0 0 1-2 2h-1v-8h1a2 2 0 0 1 2 2Z"/><path d="M12 21h3"/>
                    </svg>
                </span>
                <div>
                    <h2>{{ __('messages.contact.cta_title') }}</h2>
                    <p>{{ __('messages.contact.cta_text') }}</p>
                </div>
            </div>

            <div class="contact-page__map-visual" aria-hidden="true">
                <svg class="contact-page__pin" viewBox="0 0 24 24" focusable="false">
                    <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="3"/>
                </svg>
            </div>
        </section>
    </div>
</section>
@endsection

@push('scripts')
    <script>
        document.querySelector('[data-contact-form]')?.addEventListener('submit', (event) => {
            if (!event.currentTarget.checkValidity()) {
                return;
            }

            event.currentTarget.querySelector('[data-contact-submit]')?.setAttribute('disabled', 'disabled');
        });
    </script>
@endpush
