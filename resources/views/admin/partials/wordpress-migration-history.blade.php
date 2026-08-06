<section class="admin-panel wordpress-migration__history">
    <div class="admin-table-header"><h2 class="admin-table-header__title">Importavimo istorija</h2></div>
    <div class="wordpress-migration__history-table"><div class="wordpress-migration__history-head"><span>Data</span><span>Šaltinis</span><span>Būsena</span><span>Straipsniai</span><span>Paveikslėliai</span><span>Pastabos</span></div>
        @forelse(($history ?? collect()) as $entry)<div class="wordpress-migration__history-head"><span>{{ $entry->created_at?->format('Y-m-d H:i') }}</span><span>{{ $entry->source_type }}</span><span>{{ $entry->status }}</span><span>{{ $entry->created_count + $entry->updated_count }}</span><span>{{ $entry->media_copied }}</span><span><a href="{{ route('admin.wordpress-migration.import.result', $entry) }}">Ataskaita</a></span></div>@empty<p>Importavimo istorijos dar nėra.</p>@endforelse
    </div>
</section>
