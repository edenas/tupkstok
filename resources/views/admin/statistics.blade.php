@extends('layouts.admin')

@section('content')
<div class="admin-page">
    <div class="admin-page__header">
        <div>
            <h1 class="admin-page__title">{{ __('messages.admin.statistics.title') }}</h1>
        </div>
    </div>

    <section class="admin-statistics-grid" aria-label="{{ __('messages.admin.statistics.summary_label') }}">
        @foreach ($summaryCards as $card)
            <article class="admin-stat-card">
                <p class="admin-stat-card__label">{{ __($card['label_key']) }}</p>
                <strong class="admin-stat-card__value">{{ number_format($card['value']) }}</strong>
            </article>
        @endforeach
    </section>

    <section class="admin-panel admin-statistics-section">
        <div class="admin-table-header">
            <div>
                <h2 class="admin-table-header__title">{{ __('messages.admin.statistics.all_pages') }}</h2>
            </div>
        </div>

        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>{{ __('messages.admin.statistics.columns.page') }}</th>
                        <th>{{ __('messages.admin.statistics.columns.url') }}</th>
                        <th>{{ __('messages.admin.statistics.columns.visits') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($allPages as $page)
                        <tr>
                            <td>
                                <span class="admin-table__strong-text">{{ $page->title ?: $page->path }}</span>
                            </td>
                            <td>
                                <span class="admin-table__muted-text">{{ $page->path }}</span>
                            </td>
                            <td>
                                <span class="admin-table__count">{{ number_format($page->visits_count) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">{{ __('messages.admin.statistics.empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $allPages->onEachSide(1)->links('pagination.admin') }}
    </section>
</div>
@endsection
