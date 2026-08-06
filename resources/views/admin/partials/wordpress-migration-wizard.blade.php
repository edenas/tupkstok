@php
    $analysisComplete = ($manifest['analysis_status'] ?? null) === 'complete';
    $settingsComplete = isset($manifest['import_settings_saved_at']); $seoComplete = isset($manifest['seo_settings_saved_at']);
    $steps = [1 => ['label' => 'Šaltiniai', 'route' => 'admin.wordpress-migration.index', 'available' => true], 2 => ['label' => 'Analizė', 'route' => 'admin.wordpress-migration.analysis', 'available' => $analysisComplete], 3 => ['label' => 'Importavimo nustatymai', 'route' => 'admin.wordpress-migration.settings', 'available' => $analysisComplete], 4 => ['label' => 'SEO ir URL', 'route' => 'admin.wordpress-migration.seo', 'available' => $settingsComplete], 5 => ['label' => 'Paruošta importui', 'route' => 'admin.wordpress-migration.ready', 'available' => $seoComplete]];
    $progress = $currentStep * 20;
    $progressState = $seoComplete ? 'Visi nustatymai paruošti' : ($settingsComplete ? 'Importavimo nustatymai išsaugoti' : ($analysisComplete ? 'Analizė baigta' : 'Ruošiami šaltiniai'));
@endphp
<section class="wordpress-wizard-shell" aria-label="Migracijos eiga">
    <div class="wordpress-wizard-progress"><div><span>Dabartinis žingsnis</span><strong>{{ $steps[$currentStep]['label'] }}</strong></div><div><span>Parengta</span><strong>{{ $progressState }}</strong></div><b>{{ $progress }}%</b></div>
    <div class="wordpress-wizard-progress__track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $progress }}" aria-label="Migracijos eiga"><span style="width:{{ $progress }}%"></span></div>
    <nav class="wordpress-wizard" aria-label="Importavimo žingsniai">
        @foreach($steps as $number => $step)
            @if($step['available'])<a href="{{ route($step['route']) }}" class="wordpress-wizard__step {{ $number === $currentStep ? 'is-current' : '' }} {{ $number < $currentStep ? 'is-complete' : '' }}" @if($number === $currentStep) aria-current="step" @endif>@else<span class="wordpress-wizard__step is-locked" aria-disabled="true">@endif
                <span class="wordpress-wizard__number">{{ $number < $currentStep ? '✓' : $number }}</span><span>{{ $step['label'] }}</span>
            @if($step['available'])</a>@else</span>@endif
        @endforeach
    </nav>
</section>
