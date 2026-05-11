@extends('layouts.app')

@section('content')
    @include('sections.section-home-hero')

    <section class="container mx-auto px-6 py-12">
        <div class="space-y-4 text-slate-700 dark:text-slate-300">
            <h1 class="text-3xl font-semibold">Project architecture is ready</h1>
            <p class="max-w-2xl leading-relaxed">
                This page uses a clean layout and organized view folders for layouts, components, sections, and pages.
            </p>
        </div>
    </section>
@endsection
