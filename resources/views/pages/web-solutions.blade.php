@extends('layouts.app')

@section('content')
@php
    $webServices = [
        'Interneto svetainių kūrimas',
        'WEB aplikacijų kūrimas',
        'Front-End development',
        'Responsive dizainas',
        'UI/UX dizainas',
        'Laravel development',
        'WordPress sprendimai',
        'Performance optimizacija',
        'SEO pagrindų integracija',
        'AI sprendimų integracija WEB projektuose',
    ];

    $projects = [
        [
            'name' => 'eadvokatai.lt',
            'url' => 'https://eadvokatai.lt/',
            'description' => '',
        ],
        [
            'name' => 'gardamas.lt',
            'url' => 'https://gardamas.lt/',
            'description' => '',
        ],
        [
            'name' => 'saulesmainai.lt',
            'url' => 'https://saulesmainai.lt/',
            'description' => '',
        ],
        [
            'name' => 'tupkstok.lt',
            'url' => 'https://tupkstok.lt/',
            'description' => '',
        ],
        [
            'name' => 'sleepangel.lt',
            'url' => 'https://sleepangel.lt/',
            'description' => '',
        ],
    ];
@endphp

<section class="web-solutions-page">
    <div class="web-solutions-page__container">
        <header class="web-solutions-page__hero">
            <p class="web-solutions-page__eyebrow">WEB sprendimai</p>
            <h1 class="web-solutions-page__title">Profesionalus WEB kūrimas ir skaitmeniniai sprendimai</h1>
            <p class="web-solutions-page__lead">
                Kuriu modernias interneto svetaines, WEB aplikacijas ir skaitmeninius produktus, kuriuose aiški vartotojo patirtis, našumas ir vizualinis identitetas veikia kaip viena sistema.
            </p>
        </header>

        <section class="web-solutions-page__section">
            <div class="web-solutions-page__section-header">
                <h2>Paslaugos</h2>
                <p>Struktūruoti WEB sprendimai nuo vartotojo sąsajos iki techninio įgyvendinimo.</p>
            </div>

            <div class="web-solutions-page__services-grid">
                @foreach ($webServices as $service)
                    <article class="web-solutions-page__service-card">
                        <h3>{{ $service }}</h3>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="web-solutions-page__section">
            <div class="web-solutions-page__section-header">
                <h2>Atlikti projektai</h2>
                <p>Projektų kortelės paruoštos logotipams, trumpiems aprašymams ir tolimesniam portfolio plėtimui.</p>
            </div>

            <div class="web-solutions-page__projects-grid">
                @foreach ($projects as $project)
                    <article class="web-solutions-page__project-card">
                        <div class="web-solutions-page__logo-placeholder" aria-label="{{ $project['name'] }} logo placeholder"></div>

                        <div class="web-solutions-page__project-content">
                            <h3>{{ $project['name'] }}</h3>
                            <div class="web-solutions-page__project-description">
                                @if ($project['description'])
                                    <p>{{ $project['description'] }}</p>
                                @endif
                            </div>
                        </div>

                        <a href="{{ $project['url'] }}" target="_blank" rel="noopener noreferrer" class="web-solutions-page__project-link">
                            Visit website
                        </a>
                    </article>
                @endforeach
            </div>
        </section>
    </div>
</section>
@endsection
