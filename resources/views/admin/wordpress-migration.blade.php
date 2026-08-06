@extends('layouts.admin')

@section('content')
<div class="admin-page wordpress-migration wordpress-migration__step-content">
    <div class="admin-page__header"><h1 class="admin-page__title">WordPress importavimo centras</h1></div>
    @include('admin.partials.wordpress-migration-wizard')
    @if(session('success'))<div class="admin-alert admin-alert--success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="admin-alert admin-alert--error">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="admin-alert admin-alert--error"><ul class="admin-alert__list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <div class="wordpress-migration__privacy-note"><strong>Privatūs duomenys.</strong> Migracijos failai saugomi privačiai ir nėra viešai pasiekiami.</div>

    @unless($manifest)
    <section class="wordpress-migration__source-modes" data-source-modes>
        <label class="wordpress-migration__mode"><input type="radio" name="source_mode" value="workspace" checked><strong>Naudoti Workspace</strong><span>Automatiškai pasirinkti naujausius konfigūruoto katalogo failus.</span></label>
        <label class="wordpress-migration__mode"><input type="radio" name="source_mode" value="manual"><strong>Įkelti rankiniu būdu</strong><span>Pasirinkti XML, SQL ir ZIP failus šiame įrenginyje.</span></label>
    </section>
    <section class="admin-panel admin-form-panel" data-mode-panel="workspace">
        <div class="wordpress-migration__workspace-header"><div><h2>Workspace</h2><p>{{ $workspace['path'] }}</p></div><a class="admin-button admin-button--secondary" href="{{ route('admin.wordpress-migration.index') }}">Atnaujinti Workspace</a></div>
        <div class="wordpress-migration__workspace-files">
            @foreach(['xml' => 'XML', 'sql' => 'SQL'] as $key => $label)<div class="wordpress-migration__workspace-file {{ $workspace[$key] ? 'is-found' : 'is-missing' }}"><strong>{{ $workspace[$key] ? '✓' : '✕' }} {{ $label }} {{ $workspace[$key] ? 'rastas' : 'failas nerastas' }}</strong>@if($workspace[$key])<span>{{ $workspace[$key]['name'] }} · {{ number_format($workspace[$key]['size'] / 1048576, 2) }} MB · {{ \Illuminate\Support\Carbon::parse($workspace[$key]['modified_at'])->format('Y-m-d H:i') }}</span>@endif</div>@endforeach
            <div class="wordpress-migration__workspace-file {{ $workspace['uploads'] ? 'is-found' : 'is-missing' }}">
                <strong>{{ $workspace['uploads'] ? '✓' : '✕' }} {{ $workspace['uploads'] ? ($workspace['uploads']['source_type'] === 'directory' ? '„uploads“ katalogas rastas' : '„uploads“ ZIP archyvas rastas') : '„uploads“ šaltinis nerastas' }}</strong>
                @if($workspace['uploads'])
                    <span>Naudojamas šaltinis: {{ $workspace['uploads']['source_type'] === 'directory' ? 'Katalogas' : 'ZIP archyvas' }} · {{ $workspace['uploads']['display_name'] }} · {{ number_format($workspace['uploads']['size']/1048576, 2) }} MB @if(isset($workspace['uploads']['file_count'])) · {{ $workspace['uploads']['file_count'] }} failai @endif · {{ \Illuminate\Support\Carbon::parse($workspace['uploads']['modified_at'])->format('Y-m-d H:i') }}</span>
                    <small>{{ $workspace['uploads']['requires_extraction'] ? 'Archyvas bus saugiai išskleistas analizės metu.' : 'Archyvo išskleisti nereikia.' }}</small>
                @else
                    <span>Workspace kataloge turi būti „uploads“ katalogas arba „uploads.zip“ archyvas.</span>
                @endif
            </div>
        </div>
        @if($workspace['media_error'])<div class="admin-alert admin-alert--error">{{ $workspace['media_error'] }}</div>@endif
        <form method="POST" action="{{ route('admin.wordpress-migration.workspace.store') }}">@csrf<button class="admin-button admin-button--primary" type="submit" {{ !($workspace['xml'] && $workspace['sql'] && $workspace['uploads']) ? 'disabled' : '' }}>Naudoti Workspace failus</button></form>
    </section>
    <section class="admin-panel admin-form-panel" data-mode-panel="manual" hidden>
        <form class="admin-form admin-form--full" method="POST" action="{{ route('admin.wordpress-migration.store') }}" enctype="multipart/form-data">@csrf
            <div class="admin-form__field"><label class="admin-form__label" for="wordpress_xml">WordPress XML failas</label><input class="admin-form__input admin-form__file-input" id="wordpress_xml" type="file" name="wordpress_xml" accept=".xml" required><small class="admin-form__help-text">WordPress eksporto XML failas.</small></div>
            <div class="admin-form__field"><label class="admin-form__label" for="wordpress_sql">WordPress SQL failas</label><input class="admin-form__input admin-form__file-input" id="wordpress_sql" type="file" name="wordpress_sql" accept=".sql,.gz" required><small class="admin-form__help-text">SQL, SQL.GZ arba GZ. Failas nebus vykdomas.</small></div>
            <div class="admin-form__field"><label class="admin-form__label" for="uploads_zip">„uploads“ archyvas</label><input class="admin-form__input admin-form__file-input" id="uploads_zip" type="file" name="uploads_zip" accept=".zip" required><small class="admin-form__help-text">ZIP archyvas, išsaugantis pradinę metų ir mėnesių struktūrą.</small></div>
            <div class="admin-form__actions"><button class="admin-button admin-button--primary" type="submit">Įkelti failus</button></div>
        </form>
    </section>
    @else
    @if($analysisQueueIsStalled)
    <div class="wordpress-migration__warning"><strong>Eilės užduotis nepradėta.</strong> Analizė laukia ilgiau nei tikėtasi, todėl greičiausiai neveikia eilės darbuotojas. Serveryje paleiskite <code>php artisan queue:work</code> ir palikite procesą veikti.</div>
    @endif
    <section class="admin-panel wordpress-migration__section">
        <div class="admin-table-header"><h2 class="admin-table-header__title">Dabartinė migracijos sesija</h2><span class="wordpress-migration__status wordpress-migration__status--{{ $manifest['analysis_status'] }}">{{ match($manifest['analysis_status']) {'pending' => 'Laukia analizės', 'queued' => 'Analizė eilėje', 'running' => 'Analizė vykdoma', 'complete' => 'Analizė baigta', 'failed' => 'Analizė nepavyko', default => 'Nežinoma būsena'} }}</span></div>
        <div class="wordpress-migration__body"><dl class="wordpress-migration__details"><div><dt>Sesijos ID</dt><dd>{{ $manifest['id'] }}</dd></div>@foreach($manifest['files'] as $file)<div><dt>{{ $file['original_name'] }}</dt><dd>{{ number_format($file['size'] / 1048576, 2) }} MB · SHA-256 {{ Str::limit($file['sha256'], 18) }}</dd></div>@endforeach @if(isset($manifest['media_source']))<div><dt>Medijos šaltinis</dt><dd>{{ $manifest['media_source']['source_type'] === 'directory' ? 'Workspace katalogas' : 'ZIP archyvas' }} · {{ $manifest['media_source']['original_name'] }} · {{ number_format($manifest['media_source']['size']/1048576, 2) }} MB @if($manifest['media_source']['file_count'] ?? null) · {{ $manifest['media_source']['file_count'] }} failai @endif</dd></div>@endif</dl>
        @if($manifest['errors'] ?? [])<div class="admin-alert admin-alert--error"><strong>Klaidos</strong><ul class="admin-alert__list">@foreach($manifest['errors'] as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <div class="admin-form__actions">@if(in_array($manifest['analysis_status'], ['pending', 'failed'], true))<form method="POST" action="{{ route('admin.wordpress-migration.analyze', $manifest['id']) }}">@csrf<button class="admin-button admin-button--primary">Analizuoti</button></form>@elseif($manifest['analysis_status'] === 'complete')<a class="admin-button admin-button--primary" href="{{ route('admin.wordpress-migration.analysis') }}">Peržiūrėti analizę</a>@endif<form method="POST" action="{{ route('admin.wordpress-migration.destroy', $manifest['id']) }}" onsubmit="return confirm('Ar tikrai pašalinti migracijos failus?')">@csrf @method('DELETE')<button class="admin-button admin-button--secondary">Pašalinti migracijos failus</button></form></div></div>
    </section>
    @endif

    @include('admin.partials.wordpress-migration-history')
</div>
@endsection

@push('scripts')
<script>document.querySelectorAll('[name="source_mode"]').forEach((input)=>input.addEventListener('change',()=>document.querySelectorAll('[data-mode-panel]').forEach((panel)=>panel.hidden=panel.dataset.modePanel!==input.value)));</script>
@endpush
