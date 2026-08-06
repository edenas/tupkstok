<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfirmWordPressImportRequest;
use App\Http\Requests\SaveWordPressImportSettingsRequest;
use App\Http\Requests\SaveWordPressMigrationSourcesRequest;
use App\Http\Requests\SaveWordPressSeoSettingsRequest;
use App\Jobs\AnalyzeWordPressMigration;
use App\Jobs\GenerateWordPressImportDryRun;
use App\Jobs\RollbackWordPressImport;
use App\Jobs\StartWordPressImport;
use App\Models\WordPressImportRun;
use App\Services\WordPressMigration\WordPressImportPlanService;
use App\Services\WordPressMigration\WordPressMigrationManager;
use App\Services\WordPressMigration\WordPressMigrationWizardPresenter;
use App\Services\WordPressMigration\WordPressWorkspaceScanner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;

class AdminWordPressMigrationController extends Controller
{
    public function index(WordPressMigrationManager $manager, WordPressWorkspaceScanner $scanner): View
    {
        $manifest = $manager->latest();

        return view('admin.wordpress-migration', ['manifest' => $manifest, 'report' => $manifest ? $manager->report($manifest) : null, 'workspace' => $scanner->scan(), 'analysisQueueIsStalled' => $manifest ? $manager->analysisQueueIsStalled($manifest) : false, 'currentStep' => 1]);
    }

    public function store(SaveWordPressMigrationSourcesRequest $request, WordPressMigrationManager $manager): RedirectResponse
    {
        $manager->create($request->validated());

        return redirect()->route('admin.wordpress-migration.index')->with('success', 'Šaltinio failai sėkmingai išsaugoti.');
    }

    public function storeWorkspace(WordPressMigrationManager $manager, WordPressWorkspaceScanner $scanner): RedirectResponse
    {
        $workspace = $scanner->scan();
        if (! $scanner->complete($workspace)) {
            return back()->with('error', 'Workspace nėra visų privalomų failų.');
        }
        try {
            $manager->createFromWorkspace($workspace);
        } catch (RuntimeException $exception) {
            report($exception);

            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('admin.wordpress-migration.index')->with('success', 'Workspace šaltinio failai sėkmingai išsaugoti privačioje sesijoje.');
    }

    public function analyze(string $importId, WordPressMigrationManager $manager): RedirectResponse
    {
        $manager->find($importId);
        $manager->markAnalysisQueued($importId);
        AnalyzeWordPressMigration::dispatch($importId);

        return redirect()->route('admin.wordpress-migration.index')->with('success', 'Analizė perduota vykdyti fone.');
    }

    public function analysis(WordPressMigrationManager $manager): View|RedirectResponse
    {
        $manifest = $manager->latest();
        if (! $manifest || $manifest['analysis_status'] !== 'complete') {
            return redirect()->route('admin.wordpress-migration.index');
        }

        return view('admin.wordpress-migration-analysis', ['manifest' => $manifest, 'report' => $manager->report($manifest), 'currentStep' => 2]);
    }

    public function settings(WordPressMigrationManager $manager, WordPressMigrationWizardPresenter $presenter): View|RedirectResponse
    {
        $manifest = $manager->latest();
        if (! $manifest || $manifest['analysis_status'] !== 'complete') {
            return redirect()->route('admin.wordpress-migration.index');
        }
        $report = $manager->report($manifest);

        return view('admin.wordpress-migration-settings', ['manifest' => $manifest, 'report' => $report, 'wizardData' => $presenter->settings($report), 'currentStep' => 3]);
    }

    public function saveSettings(SaveWordPressImportSettingsRequest $request, WordPressMigrationManager $manager): RedirectResponse|JsonResponse
    {
        $manifest = $manager->latest();
        if (! $manifest || $manifest['analysis_status'] !== 'complete') {
            return redirect()->route('admin.wordpress-migration.index');
        }
        $manager->saveImportSettings($manifest['id'], $request->importOptions());
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Nustatymai išsaugoti.']);
        }

        return redirect()->route('admin.wordpress-migration.seo')->with('success', 'Importavimo nustatymai išsaugoti.');
    }

    public function seo(WordPressMigrationManager $manager): View|RedirectResponse
    {
        $manifest = $manager->latest();
        if (! $manifest || ! isset($manifest['import_settings_saved_at'])) {
            return redirect()->route('admin.wordpress-migration.settings');
        }

        return view('admin.wordpress-migration-seo', ['manifest' => $manifest, 'currentStep' => 4]);
    }

    public function saveSeo(SaveWordPressSeoSettingsRequest $request, WordPressMigrationManager $manager): RedirectResponse|JsonResponse
    {
        $manifest = $manager->latest();
        if (! $manifest || ! isset($manifest['import_settings_saved_at'])) {
            return redirect()->route('admin.wordpress-migration.settings');
        }
        $manager->saveSeoSettings($manifest['id'], $request->seoOptions());
        if ($request->expectsJson()) {
            return response()->json(['message' => 'SEO nustatymai išsaugoti.']);
        }

        return redirect()->route('admin.wordpress-migration.ready')->with('success', 'SEO ir URL nustatymai išsaugoti.');
    }

    public function ready(WordPressMigrationManager $manager): View|RedirectResponse
    {
        $manifest = $manager->latest();
        if (! $manifest || ! isset($manifest['seo_settings_saved_at'])) {
            return redirect()->route('admin.wordpress-migration.seo');
        }
        $history = Schema::hasTable('wordpress_import_runs') ? WordPressImportRun::query()->latest()->limit(20)->get() : collect();

        return view('admin.wordpress-migration-ready', ['manifest' => $manifest, 'report' => $manager->report($manifest), 'history' => $history, 'currentStep' => 5]);
    }

    public function dryRun(string $importId, WordPressImportPlanService $plans): RedirectResponse
    {
        if (Schema::hasTable('wordpress_import_runs') && WordPressImportRun::query()->where('migration_id', $importId)->whereIn('status', ['queued', 'running', 'rolling_back'])->exists()) {
            return back()->with('error', 'Negalima pakeisti bandomojo plano, kol vyksta importavimas arba saugus atšaukimas.');
        }
        $plans->loadPrerequisites($importId);
        app(WordPressMigrationManager::class)->updateManifest($importId, ['dry_run_status' => 'queued', 'dry_run_path' => null, 'dry_run_queued_at' => now()->toIso8601String(), 'dry_run_error' => null]);
        GenerateWordPressImportDryRun::dispatch($importId);

        return redirect()->route('admin.wordpress-migration.dry-run.show', $importId)->with('success', 'Bandomasis importas perduotas vykdyti fone.');
    }

    public function showDryRun(string $importId, WordPressMigrationManager $manager): View
    {
        $manifest = $manager->find($importId);
        $plan = ! empty($manifest['dry_run_path']) ? $manager->readPrivateJson($importId, $manifest['dry_run_path']) : null;

        return view('admin.wordpress-migration-dry-run', ['manifest' => $manifest, 'plan' => $plan, 'currentStep' => 6]);
    }

    public function confirm(ConfirmWordPressImportRequest $request, string $importId, WordPressImportPlanService $plans, WordPressMigrationManager $manager): RedirectResponse
    {
        try {
            $plan = $plans->loadAndValidate($importId, false);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }
        if (WordPressImportRun::query()->where('migration_id', $importId)->whereIn('status', ['queued', 'running'])->exists()) {
            return back()->with('error', 'Ši importavimo sesija jau vykdoma arba laukia eilėje.');
        }
        $manifest = $manager->find($importId);
        $runId = (string) Str::uuid();
        $limit = $request->integer('test_limit') ?: null;
        $importFingerprint = hash('sha256', $plan['fingerprint'].'|'.($limit ?: 'full'));
        $previous = WordPressImportRun::query()->where('import_fingerprint', $importFingerprint)->first();
        if ($previous) {
            $message = $previous->status === 'failed' ? 'Šis importas jau buvo pradėtas ir nepavyko. Tęskite jį iš ataskaitos.' : 'Šis patvirtintas importo planas jau buvo panaudotas.';

            return back()->with('error', $message);
        }
        $run = WordPressImportRun::firstOrCreate(['import_fingerprint' => $importFingerprint], [
            'id' => $runId, 'migration_id' => $importId, 'administrator_id' => $request->user()->id,
            'source_type' => $manifest['source_mode'] ?? 'manual', 'status' => 'queued', 'stage' => 'Laukia eilėje',
            'is_test' => $limit !== null, 'test_limit' => $limit, 'settings_snapshot' => $plan['settings'],
            'seo_settings_snapshot' => $plan['seo_settings'], 'source_fingerprints' => $plan['source_fingerprints'],
            'dry_run_fingerprint' => $plan['fingerprint'],
            'plan_path' => $manifest['dry_run_path'], 'warnings' => $plan['warnings'], 'errors' => [], 'last_activity_at' => now(),
        ]);
        if (! $run->wasRecentlyCreated) {
            return back()->with('error', 'Šis patvirtintas importo planas jau buvo panaudotas.');
        }
        StartWordPressImport::dispatch($run->id);

        return redirect()->route('admin.wordpress-migration.import.progress', $run)->with('success', 'Importavimas perduotas vykdyti fone.');
    }

    public function progress(WordPressImportRun $run): View
    {
        return view('admin.wordpress-migration-progress', ['run' => $run]);
    }

    public function status(WordPressImportRun $run): JsonResponse
    {
        $run->refresh();
        $total = max(1, $run->total_items);

        return response()->json(['id' => $run->id, 'status' => $run->status, 'stage' => $run->stage, 'percentage' => min(100, (int) floor($run->processed_items / $total * 100)), 'processed' => $run->processed_items, 'total' => $run->total_items, 'created' => $run->created_count, 'updated' => $run->updated_count, 'skipped' => $run->skipped_count, 'failed' => $run->failed_count, 'media_copied' => $run->media_copied, 'media_reused' => $run->media_reused, 'media_failed' => $run->media_failed, 'redirects' => $run->redirects_created, 'warnings' => $run->warnings ?? [], 'errors' => $run->errors ?? [], 'started_at' => $run->started_at?->toIso8601String(), 'last_activity_at' => $run->last_activity_at?->toIso8601String(), 'queue_stalled' => in_array($run->status, ['queued', 'running'], true) && $run->last_activity_at?->copy()->addSeconds((int) config('wordpress-migration.queue_stale_after_seconds', 120))->isPast(), 'result_url' => route('admin.wordpress-migration.import.result', $run)]);
    }

    public function result(WordPressImportRun $run, WordPressMigrationManager $manager): View
    {
        $report = $run->report_path ? $manager->readPrivateJson($run->migration_id, $run->report_path) : null;

        return view('admin.wordpress-migration-result', compact('run', 'report'));
    }

    public function download(WordPressImportRun $run, WordPressMigrationManager $manager)
    {
        if (! $run->report_path) {
            abort(404);
        }

        return response()->download($manager->sessionPath($run->migration_id, $run->report_path), 'wordpress-import-'.$run->id.'.json', ['Content-Type' => 'application/json']);
    }

    public function rollback(Request $request, WordPressImportRun $run): RedirectResponse
    {
        $request->validate(['confirmation_phrase' => ['required', 'in:ATŠAUKTI']]);
        $claimed = WordPressImportRun::query()->whereKey($run->id)->whereIn('status', ['completed', 'completed_with_warnings', 'failed', 'rollback_failed'])->update(['status' => 'rolling_back', 'stage' => 'Laukia saugaus atšaukimo', 'last_activity_at' => now()]);
        if ($claimed !== 1) {
            return back()->with('error', 'Šio importo dabar saugiai atšaukti negalima.');
        }
        RollbackWordPressImport::dispatch($run->id);

        return redirect()->route('admin.wordpress-migration.import.progress', $run)->with('success', 'Saugus atšaukimas perduotas vykdyti fone.');
    }

    public function retry(WordPressImportRun $run): RedirectResponse
    {
        if ($run->status !== 'failed') {
            return back()->with('error', 'Pakartoti galima tik nepavykusį importą.');
        }
        $run->update(['status' => 'queued', 'stage' => 'Laukia eilėje', 'errors' => [], 'last_activity_at' => now()]);
        StartWordPressImport::dispatch($run->id);

        return redirect()->route('admin.wordpress-migration.import.progress', $run)->with('success', 'Importavimas saugiai tęsiamas nuo išsaugotos būsenos.');
    }

    public function destroy(string $importId, WordPressMigrationManager $manager): RedirectResponse
    {
        if (Schema::hasTable('wordpress_import_runs') && WordPressImportRun::query()->where('migration_id', $importId)->whereIn('status', ['queued', 'running', 'rolling_back'])->exists()) {
            return back()->with('error', 'Negalima pašalinti sesijos, kol vyksta importavimas arba saugus atšaukimas.');
        }
        $manager->delete($importId);

        return redirect()->route('admin.wordpress-migration.index')->with('success', 'Migracijos failai pašalinti.');
    }
}
