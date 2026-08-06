@extends('layouts.admin')
@section('content')
@php
    $isReady = $report['readiness']['status'] === 'ready'; $hasWarnings = $report['readiness']['status'] === 'warnings';
    $tone = $isReady ? 'success' : ($hasWarnings ? 'warning' : 'danger');
    $title = $isReady ? 'Sistema paruošta' : ($hasWarnings ? 'Sistema paruošta su perspėjimais' : 'Importavimas užblokuotas');
    $description = $isReady ? 'Visi parengiamieji veiksmai baigti. Migracijos planas išsaugotas ir paruoštas.' : ($hasWarnings ? 'Parengimas baigtas. Prieš importuojant rekomenduojama peržiūrėti pažymėtus failus.' : 'Prieš importuojant būtina pašalinti analizėje nurodytas problemas.');
@endphp
<div class="admin-page wordpress-migration wordpress-migration__step-content">
    <div class="admin-page__header"><h1 class="admin-page__title">WordPress importavimo centras</h1></div>
    @include('admin.partials.wordpress-migration-wizard')
    @if(session('success'))<div class="admin-alert admin-alert--success" role="status">{{ session('success') }}</div>@endif
    <section class="wordpress-migration__completion is-{{ $tone }}" aria-labelledby="completion-title"><span class="wordpress-migration__completion-mark" aria-hidden="true">{{ $isReady ? '✓' : ($hasWarnings ? '⚠' : '✕') }}</span><div><span>Pasirengimo būsena</span><h2 id="completion-title">{{ $title }}</h2><p>{{ $description }}</p></div></section>
    <div class="wordpress-migration__completion-layout">
        <section class="admin-panel wordpress-migration__completion-panel is-primary"><div class="admin-table-header"><h3 class="admin-table-header__title">Bus importuojama</h3></div><div class="wordpress-migration__ready-summary">@foreach(['posts'=>'Straipsniai','images'=>'Paveikslėliai','featured_images'=>'Miniatiūros','categories'=>'Kategorijos','tags'=>'Žymos','authors'=>'Autoriai','comments'=>'Komentarai','seo_records'=>'SEO įrašai'] as $key=>$label)<div><strong>{{ $report['summary'][$key] }}</strong><span>{{ $label }}</span></div>@endforeach</div></section>
        <aside class="wordpress-migration__completion-side"><section class="admin-panel wordpress-migration__completion-panel"><h3>Reikia peržiūrėti</h3><dl><div><dt>Vykdomieji failai</dt><dd>{{ $report['summary']['suspicious_executable_files'] }}</dd></div><div><dt>Pavojingi failai</dt><dd>{{ $report['summary']['high_risk_executable_files'] }}</dd></div><div><dt>Perspėjimai</dt><dd>{{ count($report['warnings']) }}</dd></div><div><dt>Klaidos</dt><dd>{{ count($report['errors']) }}</dd></div></dl></section><section class="admin-panel wordpress-migration__completion-panel"><h3>Bus praleista</h3><dl><div><dt>Migracijai nereikalingi failai</dt><dd>{{ $report['summary']['ignored_files'] }}</dd></div><div><dt>WordPress apsauginiai failai</dt><dd>{{ $report['summary']['protection_files'] }}</dd></div><div><dt>Vienodos medijos kopijos</dt><dd>{{ $report['summary']['duplicate_files'] }}</dd></div></dl></section></aside>
    </div>
    <section class="admin-panel wordpress-migration__checklist"><div><h3>Pasirengimas baigtas</h3><p>Visi vedlio nustatymai išsaugoti privačioje migracijos sesijoje.</p></div><ul><li><span>✓</span>Šaltinio failai saugiai išsaugoti</li><li><span>✓</span>XML ir SQL informacija išanalizuota</li><li><span>✓</span>Medijos failai patikrinti</li><li><span>✓</span>Importavimo parinktys išsaugotos</li><li><span>✓</span>SEO ir adresų parinktys išsaugotos</li></ul></section>
    <div class="wordpress-migration__completion-actions"><div><strong>Migracijos planas išsaugotas</strong><span>Šiame etape jokie svetainės duomenys dar nepakeisti.</span></div><a class="admin-button admin-button--secondary" href="{{ route('admin.wordpress-migration.seo') }}">Atgal</a>@if($isReady || $hasWarnings)<form method="POST" action="{{ route('admin.wordpress-migration.dry-run', $manifest['id']) }}">@csrf<button class="admin-button" type="submit">Parengti bandomąjį importą</button></form>@endif</div>
    @include('admin.partials.wordpress-migration-history')
</div>
@endsection
