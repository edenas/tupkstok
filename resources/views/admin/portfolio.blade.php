@extends('layouts.admin')

@section('content')
<div class="admin-page">
    <div class="admin-page__header">
        <div>
            <h1 class="admin-page__title">{{ __('messages.admin.portfolio') }}</h1>
        </div>

        <a href="{{ route('admin.portfolio.create') }}" class="admin-button admin-button--primary">
            {{ __('messages.admin.add_post') }}
        </a>
    </div>

    @if (session('success'))
        <div class="admin-alert admin-alert--success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="admin-alert admin-alert--error">
            {{ session('error') }}
        </div>
    @endif

    <section class="admin-panel">
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>{{ __('messages.admin.title') }}</th>
                        <th>{{ __('messages.admin.category') }}</th>
                        <th>{{ __('messages.admin.date') }}</th>
                        <th>{{ __('messages.admin.position') }}</th>
                        <th class="admin-table__actions-heading">{{ __('messages.admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($portfolioPosts as $portfolioPost)
                        <tr>
                            <td>
                                <a href="{{ route('admin.portfolio.edit', $portfolioPost->id) }}" class="admin-table__title-link">
                                    {{ $portfolioPost->title }}
                                </a>
                            </td>
                            <td>{{ $portfolioPost->category }}</td>
                            <td>
                                <time data-local-timestamp="{{ $portfolioPost->created_at->toIso8601String() }}" datetime="{{ $portfolioPost->created_at->toIso8601String() }}">
                                    {{ $portfolioPost->created_at->format('Y-m-d H:i') }}
                                </time>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.portfolio.position.update', $portfolioPost->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
                                    <input
                                        type="number"
                                        name="position"
                                        value="{{ $portfolioPost->position }}"
                                        min="1"
                                        step="1"
                                        aria-label="Position for {{ $portfolioPost->title }}"
                                        class="admin-table__position-input"
                                        onchange="this.form.submit()"
                                        required
                                    >
                                </form>
                            </td>
                            <td>
                                <div class="admin-table__actions">
                                    <a href="{{ route('admin.portfolio.edit', $portfolioPost->id) }}" title="Edit post" aria-label="Edit {{ $portfolioPost->title }}" class="admin-icon-button admin-icon-button--edit">
                                        <svg viewBox="0 0 20 20" focusable="false">
                                            <path fill="currentColor" d="M13.59 3.59a2 2 0 0 1 2.82 2.82l-.79.8-2.83-2.83.8-.79ZM11.38 5.79 3 14.17V17h2.83l8.38-8.38-2.83-2.83Z" />
                                        </svg>
                                    </a>
                                    <button
                                        type="button"
                                        title="Delete post"
                                        aria-label="Delete {{ $portfolioPost->title }}"
                                        class="admin-icon-button admin-icon-button--delete"
                                        data-delete-confirmation-trigger
                                        data-delete-form-id="delete-portfolio-post-form-{{ $portfolioPost->id }}"
                                    >
                                        <svg viewBox="0 0 20 20" focusable="false">
                                            <path fill="currentColor" fill-rule="evenodd" d="M4.29 4.29a1 1 0 0 1 1.42 0L10 8.59l4.29-4.3a1 1 0 1 1 1.42 1.42L11.41 10l4.3 4.29a1 1 0 0 1-1.42 1.42L10 11.41l-4.29 4.3a1 1 0 0 1-1.42-1.42L8.59 10l-4.3-4.29a1 1 0 0 1 0-1.42Z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <form id="delete-portfolio-post-form-{{ $portfolioPost->id }}" method="POST" action="{{ route('admin.portfolio.destroy', $portfolioPost->id) }}" class="admin-hidden-form">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">No portfolio posts yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $portfolioPosts->onEachSide(1)->links('pagination.admin') }}
    </section>

    <x-admin-delete-confirmation-modal title="Delete post" message="Are you sure you want to delete this portfolio post?" />
</div>
@endsection
