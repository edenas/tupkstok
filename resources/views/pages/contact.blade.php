@extends('layouts.app')

@section('content')
@php
    $contactDetails = [
        [
            'label' => 'Name',
            'value' => 'Edenas Pocius',
            'url' => null,
        ],
        [
            'label' => 'Email',
            'value' => 'edenas.pocius@gmail.com',
            'url' => 'mailto:edenas.pocius@gmail.com',
        ],
        [
            'label' => 'Phone',
            'value' => '+370 609 60686',
            'url' => 'tel:+37060960686',
        ],
    ];

    $socialLinks = [
        [
            'label' => 'LinkedIn',
            'url' => 'https://www.linkedin.com/in/edenas-pocius-0b4a59191/',
        ],
        [
            'label' => 'Instagram',
            'url' => 'https://www.instagram.com/edenas_pocius/',
        ],
        [
            'label' => 'Facebook',
            'url' => 'https://www.facebook.com/edenas.pocius.1/',
        ],
    ];
@endphp

<section class="contact-page">
    <div class="contact-page__container">
        <header class="contact-page__hero">
            <p class="contact-page__eyebrow">Contact</p>
            <h1 class="contact-page__title">Let’s talk about your next project</h1>
            <p class="contact-page__lead">
                For web solutions, mobile apps, animation, video projects, or creative digital work, send a message or reach out directly.
            </p>
        </header>

        <div class="contact-page__layout">
            <aside class="contact-page__info-panel">
                <h2>Contact information</h2>

                <div class="contact-page__details">
                    @foreach ($contactDetails as $detail)
                        <div class="contact-page__detail">
                            <span>{{ $detail['label'] }}</span>
                            @if ($detail['url'])
                                <a href="{{ $detail['url'] }}">{{ $detail['value'] }}</a>
                            @else
                                <p>{{ $detail['value'] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="contact-page__socials">
                    <h3>Social links</h3>
                    <div class="contact-page__social-list">
                        @foreach ($socialLinks as $link)
                            <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer">
                                {{ $link['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </aside>

            <section class="contact-page__form-panel">
                <h2>Send a message</h2>

                <form class="contact-page__form" method="POST" action="#">
                    <div class="contact-page__field">
                        <label for="contact-name">Your name</label>
                        <input id="contact-name" name="name" type="text" autocomplete="name">
                    </div>

                    <div class="contact-page__field">
                        <label for="contact-email">Your email</label>
                        <input id="contact-email" name="email" type="email" autocomplete="email">
                    </div>

                    <div class="contact-page__field">
                        <label for="contact-subject">Subject</label>
                        <input id="contact-subject" name="subject" type="text">
                    </div>

                    <div class="contact-page__field">
                        <label for="contact-message">Your message</label>
                        <textarea id="contact-message" name="message" rows="7"></textarea>
                    </div>

                    <button type="button" class="contact-page__submit">Send message</button>
                </form>
            </section>
        </div>
    </div>
</section>
@endsection
