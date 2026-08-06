<?php

namespace App\Services\WordPressMigration;

class WordPressMigrationWizardPresenter
{
    public function settings(array $report): array
    {
        $summary = $report['summary'];
        $reviewCount = $summary['suspicious_executable_files'];
        $dangerCount = $summary['high_risk_executable_files'];
        $status = $dangerCount ? ['tone' => 'danger', 'title' => 'Importavimas užblokuotas', 'symbol' => '✕', 'text' => "{$dangerCount} vykdomieji failai turi galimai pavojingų požymių."]
            : ($report['warnings'] ? ['tone' => 'warning', 'title' => 'Paruošta su perspėjimais', 'symbol' => '⚠', 'text' => $reviewCount ? "{$reviewCount} vykdomieji failai turi būti peržiūrėti prieš importuojant." : 'Aptikta smulkių neatitikimų, tačiau galima saugiai tęsti.']
            : ['tone' => 'success', 'title' => 'Paruošta importuoti', 'symbol' => '✓', 'text' => 'Blokuojančių problemų neaptikta, todėl galima saugiai tęsti.']);
        $recommendations = [];
        if ($summary['duplicate_files']) $recommendations[] = ['title' => 'Rekomendacija dėl medijos kopijų', 'text' => "Aptikta {$summary['duplicate_files']} vienodų medijos failų. Rekomenduojame praleisti pasikartojančias kopijas.", 'action' => 'Praleisti vienodas medijos kopijas'];
        if ($reviewCount) $recommendations[] = ['title' => 'Rekomenduojama peržiūra', 'text' => "Aptikta {$reviewCount} vykdomųjų failų. Jie nebus importuojami, tačiau rekomenduojame juos peržiūrėti.", 'action' => 'Peržiūrėti analizės rezultatus'];
        return [
            'status' => $status,
            'summaryGroups' => [
                ['title' => 'Importuojama', 'tone' => 'success', 'items' => ['Straipsniai' => $summary['posts'], 'Autoriai' => $summary['authors'], 'Kategorijos' => $summary['categories'], 'Paveikslėliai' => $summary['images'], 'Miniatiūros' => $summary['featured_images'], 'SEO informacija' => $summary['seo_records']]],
                ['title' => 'Praleidžiama', 'tone' => 'info', 'items' => ['Migracijai nereikalingi failai' => $summary['ignored_files'], 'WordPress apsauginiai failai' => $summary['protection_files']]],
                ['title' => 'Reikia peržiūrėti', 'tone' => $reviewCount ? 'warning' : 'success', 'items' => ['Vykdomieji failai' => $reviewCount]],
                ['title' => 'Medijos kopijos', 'tone' => 'info', 'items' => ['Vienodos medijos kopijos' => $summary['duplicate_files']]],
            ],
            'forecast' => ['Straipsniai' => $summary['posts'], 'Autoriai' => $summary['authors'], 'Paveikslėliai' => $summary['images'], 'Miniatiūros' => $summary['featured_images'], 'SEO įrašai' => $summary['seo_records'], 'Kategorijos' => $summary['categories'], 'Žymos' => $summary['tags'], 'Komentarai' => $summary['comments']],
            'recommendations' => $recommendations,
        ];
    }
}
