@extends('layouts.admin')
@section('content')
@php
    $saved = isset($manifest['import_settings_saved_at']); $settings = $manifest['import_settings'] ?? [];
    $duplicateMode = $settings['duplicate_media'] ?? (($settings['import_duplicate_media'] ?? false) ? 'import' : 'skip');
    $imageMode = $settings['image_variants'] ?? (($settings['import_generated_thumbnails'] ?? false) ? 'all' : 'originals');
    $contentOptions = ['posts' => 'Straipsnius', 'categories' => 'Kategorijas', 'tags' => 'Žymas', 'images' => 'Paveikslėlius', 'featured_images' => 'Miniatiūras', 'seo' => 'SEO informaciją', 'authors' => 'Autorius', 'comments' => 'Komentarus'];
    $advancedOptions = [
        'skip_ignored_files' => ['Praleisti migracijai nereikalingus failus', 'Bus importuojami tik medijos perkėlimui reikalingi failai.', true],
        'preserve_upload_structure' => ['Išsaugoti įkėlimo katalogų struktūrą', 'Paveikslėliai išlaikys WordPress naudotą aplankų hierarchiją.', true],
        'verify_media_checksums' => ['Patikrinti failų vientisumą', 'Patikrinama, ar perkeliami failai nebuvo pakeisti arba sugadinti.', true],
        'block_high_risk_executables' => ['Blokuoti pavojingus vykdomuosius failus', 'Failai su pavojingo kodo požymiais niekada nebus importuojami.', true],
        'warn_suspicious_executables' => ['Įspėti apie peržiūros reikalaujančius failus', 'Neįprasti vykdomieji failai bus aiškiai pažymėti administratoriui.', true],
        'ignore_protection_files' => ['Praleisti WordPress apsauginius failus', 'Standartiniai saugūs index.php failai nebus importuojami.', true],
    ];
@endphp
<div class="admin-page wordpress-migration wordpress-migration__step-content">
    <div class="admin-page__header"><h1 class="admin-page__title">WordPress importavimo centras</h1></div>
    @include('admin.partials.wordpress-migration-wizard')
    <section class="wordpress-migration__status-card is-{{ $wizardData['status']['tone'] }}" aria-label="Importavimo būsena"><span>{{ $wizardData['status']['symbol'] }}</span><div><strong>{{ $wizardData['status']['title'] }}</strong><p>{{ $wizardData['status']['text'] }}</p></div></section>

    <section class="admin-panel wordpress-migration__assistant-section"><div class="admin-table-header"><h2 class="admin-table-header__title">Migracijos santrauka</h2></div><div class="wordpress-migration__assistant-grid">
        @foreach($wizardData['summaryGroups'] as $group)<article class="wordpress-migration__assistant-card is-{{ $group['tone'] }}"><h3>{{ $group['title'] }}</h3>@foreach($group['items'] as $label => $value)<p><span>{{ $label }}</span><strong>{{ $value }}</strong></p>@endforeach</article>@endforeach
    </div></section>

    @if($wizardData['recommendations'])<section class="wordpress-migration__recommendations" aria-label="Rekomendacijos">@foreach($wizardData['recommendations'] as $recommendation)<article><span>Rekomendacija</span><h3>{{ $recommendation['title'] }}</h3><p>{{ $recommendation['text'] }}</p><strong>Siūlomas pasirinkimas: {{ $recommendation['action'] }}</strong></article>@endforeach</section>@endif

    <section class="admin-panel admin-form-panel"><form class="admin-form admin-form--full" method="POST" action="{{ route('admin.wordpress-migration.settings.update') }}" data-migration-autosave data-success-message="Nustatymai išsaugoti" data-saved="{{ $saved ? '1' : '0' }}">@csrf @method('PUT')
        <div class="admin-form-card__header"><h2>Importavimo nustatymai</h2><p>Pakeitimai išsaugomi automatiškai. Dabar jokie duomenys neimportuojami.</p></div>
        <fieldset class="wordpress-migration__fieldset"><legend>Importuojamas turinys</legend><div class="wordpress-migration__option-grid">@foreach($contentOptions as $key => $label)<label class="wordpress-migration__option"><input type="checkbox" name="options[{{ $key }}]" value="1" {{ ($saved ? ($settings[$key] ?? false) : true) ? 'checked' : '' }}><span><strong>{{ $label }}</strong><small>Įtraukti į būsimą WordPress turinio importą.</small></span></label>@endforeach</div></fieldset>
        <fieldset class="wordpress-migration__fieldset"><legend>Vienodos medijos kopijos</legend><p class="admin-form__help-text">Pasirinkite vieną veiksmą, kai kelių failų turinys sutampa.</p><div class="wordpress-migration__radio-list">
            <label><input type="radio" name="options[duplicate_media]" value="skip" {{ $duplicateMode === 'skip' ? 'checked' : '' }}><span><strong>Praleisti vienodas kopijas</strong><small>Bus importuojama tik viena identiškų paveikslėlių kopija.</small></span></label>
            <label><input type="radio" name="options[duplicate_media]" value="import" {{ $duplicateMode === 'import' ? 'checked' : '' }}><span><strong>Importuoti visas kopijas</strong><small>Pasirinkite tik tada, jei WordPress kopijas norite išsaugoti atskirai.</small></span></label>
        </div></fieldset>
        <fieldset class="wordpress-migration__fieldset"><legend>Paveikslėlių variantai</legend><div class="wordpress-migration__radio-list">
            <label><input type="radio" name="options[image_variants]" value="originals" {{ $imageMode === 'originals' ? 'checked' : '' }}><span><strong>Importuoti tik originalius paveikslėlius</strong><small>Nauja sistema galės pati sukurti jai reikalingus dydžius.</small></span></label>
            <label><input type="radio" name="options[image_variants]" value="all" {{ $imageMode === 'all' ? 'checked' : '' }}><span><strong>Importuoti originalus ir sugeneruotas miniatiūras</strong><small>Naudinga tik norint išsaugoti esamus WordPress miniatiūrų failus.</small></span></label>
        </div></fieldset>
        <fieldset class="wordpress-migration__fieldset"><legend>Medijos ir saugumo parinktys</legend><div class="wordpress-migration__option-grid">@foreach($advancedOptions as $key => [$label, $description, $default])<label class="wordpress-migration__option"><input type="checkbox" name="options[{{ $key }}]" value="1" {{ ($saved ? ($settings[$key] ?? false) : $default) ? 'checked' : '' }}><span><strong>{{ $label }}</strong><small>{{ $description }}</small></span></label>@endforeach</div></fieldset>
        <div data-save-notice class="wordpress-migration__save-notice" role="status" aria-live="polite"></div>
        <div class="admin-form__actions"><a class="admin-button admin-button--secondary" href="{{ route('admin.wordpress-migration.analysis') }}">Atgal</a><a class="admin-button admin-button--primary" data-continue href="{{ route('admin.wordpress-migration.seo') }}">Tęsti →</a></div>
    </form></section>

    <section class="admin-panel wordpress-migration__assistant-section"><div class="admin-table-header"><h2 class="admin-table-header__title">Po importavimo turėsite</h2></div><div class="wordpress-migration__forecast">@foreach($wizardData['forecast'] as $label => $value)<div><span>{{ $label }}</span><strong>{{ $value }}</strong></div>@endforeach</div></section>
    @include('admin.partials.wordpress-migration-history')
</div>
@endsection
