<header class="border-b border-slate-200 bg-slate-50 px-6 py-4 dark:border-slate-700 dark:bg-slate-900">
    <div class="container mx-auto flex items-center justify-between">
        <div class="text-lg font-semibold">{{ config('app.name', 'Laravel') }}</div>
        <nav aria-label="Main navigation">
            <ul class="flex items-center gap-4 text-sm text-slate-700 dark:text-slate-300">
                <li><a href="{{ url('/') }}" class="hover:underline">Home</a></li>
            </ul>
        </nav>
    </div>
</header>
