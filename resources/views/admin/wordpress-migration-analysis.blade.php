@extends('layouts.admin')
@section('content')
<div class="admin-page wordpress-migration wordpress-migration__step-content">
    <div class="admin-page__header"><h1 class="admin-page__title">WordPress importavimo centras</h1></div>
    @include('admin.partials.wordpress-migration-wizard')
    @if(session('success'))<div class="admin-alert admin-alert--success">{{ session('success') }}</div>@endif
    <section class="wordpress-migration__report"><div class="wordpress-migration__report-heading"><div><h2>Analizė</h2><p class="admin-form__help-text">Trumpa būsena ir išsami failų patikra.</p></div><div class="wordpress-migration__readiness wordpress-migration__readiness--{{ $report['readiness']['status'] }}"><span>Importavimo būsena</span><strong>{{ match($report['readiness']['status']) {'ready' => '● Paruošta', 'warnings' => '● Yra perspėjimų', default => '● Yra klaidų'} }}</strong></div></div>
        <div class="wordpress-migration__summary">@foreach(['posts'=>'Straipsniai','pages'=>'Puslapiai','categories'=>'Kategorijos','tags'=>'Žymos','authors'=>'Autoriai','comments'=>'Komentarai','importable_media'=>'Paruošta importuoti','images'=>'Paveikslėliai','featured_images'=>'Miniatiūros','seo_records'=>'SEO įrašai','protection_files'=>'Apsauginiai failai','ignored_files'=>'Praleidžiami failai','suspicious_executable_files'=>'Reikia peržiūrėti','high_risk_executable_files'=>'Galimai pavojingi failai','duplicate_files'=>'Medijos kopijos','missing_files'=>'Nerasti paveikslėliai','warnings'=>'Įspėjimai'] as $key=>$label)<div><span>{{ $label }}</span><strong>{{ $report['summary'][$key] }}</strong></div>@endforeach<div><span>Klaidos</span><strong>{{ count($report['errors']) }}</strong></div><div><span>WordPress versija</span><strong>{{ $report['wordpress']['export_version'] ?: '—' }}</strong></div><div><span>Duomenų bazės priešdėlis</span><strong>{{ $report['sql']['table_prefix'] ?: '—' }}</strong></div><div><span>Medijos šaltinis</span><strong>{{ ($report['uploads']['media_source_type'] ?? $manifest['media_source']['source_type']) === 'directory' ? 'Workspace katalogas' : 'ZIP archyvas' }}</strong></div><div><span>XML dydis</span><strong>{{ number_format($manifest['files']['wordpress_xml']['size']/1048576, 2) }} MB</strong></div><div><span>SQL dydis</span><strong>{{ number_format($manifest['files']['wordpress_sql']['size']/1048576, 2) }} MB</strong></div><div><span>Uploads dydis</span><strong>{{ number_format($manifest['media_source']['size']/1048576, 2) }} MB</strong></div></div>
        <div class="admin-panel wordpress-migration__validation"><div class="admin-table-header"><h3 class="admin-table-header__title">Patikros rezultatai</h3></div><div>@foreach($report['validation'] as $check)<p class="is-{{ $check['status'] }}"><span>{{ match($check['status']) {'success' => '✓', 'info' => 'ℹ', 'warning' => '⚠', default => '✕'} }}</span>{{ $check['label'] }}</p>@endforeach</div></div>
        @if($report['warnings'])<div class="wordpress-migration__warning"><strong>Perspėjimai</strong><ul>@foreach($report['warnings'] as $warning)<li>{{ $warning }}</li>@endforeach</ul></div>@endif
        @if($report['errors'])<div class="admin-alert admin-alert--error"><strong>Klaidos</strong><ul>@foreach($report['errors'] as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <div class="admin-panel wordpress-migration__finding-group"><strong>Importuojama medija — {{ $report['summary']['importable_media'] }} failai</strong><p>Šie paveikslėliai atitinka medijos importavimo taisykles ir yra paruošti būsimam importui.</p></div>
        @if($report['summary']['ignored_files'])<div class="admin-panel wordpress-migration__finding-group"><strong>Praleidžiami failai — {{ $report['summary']['ignored_files'] }} failai</strong><p>Šie failai nėra reikalingi medijos migracijai, todėl bus saugiai praleisti. Administratoriaus veiksmai nereikalingi.</p></div>@endif
        @if($report['summary']['duplicate_files'])<div class="admin-panel wordpress-migration__finding-group"><strong>Aptiktos medijos kopijos — {{ $report['summary']['duplicate_files'] }} failai</strong><p>Tai dažniausiai sugeneruotos miniatiūros arba vienodos medijos kopijos. Jos netrukdo tęsti migracijos.</p></div>@endif
        @php($findingGroups = [
            ['key' => 'protection_files', 'title' => 'Standartiniai WordPress apsauginiai failai', 'text' => 'Šie įprasti failai saugo WordPress aplankus. Jie bus praleisti, o administratoriaus veiksmai nereikalingi.', 'danger' => false],
            ['key' => 'suspicious_executable_files', 'title' => 'Vykdomieji failai, kuriuos reikia peržiūrėti', 'text' => 'Šie failai nėra medija ir nebus importuojami. Pavojingų požymių neaptikta, tačiau rekomenduojama juos peržiūrėti.', 'danger' => false],
            ['key' => 'high_risk_executable_files', 'title' => 'Pavojingi vykdomieji failai', 'text' => 'Šiuose failuose aptikta pavojingo arba užmaskuoto kodo požymių. Jie nebus importuojami ir turi būti pašalinti prieš tęsiant.', 'danger' => true],
        ])
        @foreach($findingGroups as $group)
            @php($findings = $report['uploads'][$group['key']])
            @if($findings)
            <details class="{{ $group['danger'] ? 'admin-alert admin-alert--error' : 'admin-panel wordpress-migration__finding-group' }}" @if($group['danger']) open @endif>
                <summary><strong>{{ $group['title'] }} ({{ count($findings) }})</strong></summary>
                <p>{{ $group['text'] }}</p>
                <div class="wordpress-migration__finding-list">
                    @foreach(array_slice($findings, 0, 20) as $finding)
                    <div><code>{{ $finding['path'] }}</code><span>{{ $finding['extension'] ?: 'be plėtinio' }} · {{ number_format($finding['size'] / 1024, 2) }} KB</span><small>{{ $finding['reason'] }}</small></div>
                    @endforeach
                </div>
                @if(count($findings) > 20)<p>Rodomi pirmi 20 iš {{ count($findings) }} failų.</p>@endif
            </details>
            @endif
        @endforeach
        <div class="admin-form__actions"><a class="admin-button admin-button--secondary" href="{{ route('admin.wordpress-migration.index') }}">Atgal</a><a class="admin-button admin-button--primary" href="{{ route('admin.wordpress-migration.settings') }}">Tęsti →</a></div>
    </section>
    @include('admin.partials.wordpress-migration-history')
</div>
@endsection
