@extends('layouts.admin')

@section('content')
<div class="admin-page">
    <div class="admin-page__header">
        <div>
            <h1 class="admin-page__title">{{ __('messages.admin.dashboard') }}</h1>
        </div>
    </div>

    <section class="admin-dashboard-summary" aria-label="{{ __('messages.admin.dashboard_summary') }}">
        <article class="admin-dashboard-card">
            <p class="admin-dashboard-card__label">{{ __('messages.admin.created_pages') }}</p>
            <strong class="admin-dashboard-card__value">{{ number_format($totalPages) }}</strong>
        </article>

        <article class="admin-dashboard-card">
            <p class="admin-dashboard-card__label">{{ __('messages.admin.blog_posts') }}</p>
            <strong class="admin-dashboard-card__value">{{ number_format($totalBlogPosts) }}</strong>
        </article>

        <article class="admin-dashboard-card">
            <p class="admin-dashboard-card__label">{{ __('messages.admin.today_visits') }}</p>
            <strong class="admin-dashboard-card__value">{{ number_format($todayVisits) }}</strong>
        </article>
    </section>

    <section class="admin-panel admin-dashboard-popular">
        <div class="admin-table-header">
            <div>
                <h2 class="admin-table-header__title">{{ __('messages.admin.statistics.today_title') }}</h2>
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
                    @forelse ($popularPages as $page)
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
                            <td colspan="3">{{ __('messages.admin.statistics.today_empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $popularPages->onEachSide(1)->links('pagination.admin') }}
    </section>
</div>
@endsection

