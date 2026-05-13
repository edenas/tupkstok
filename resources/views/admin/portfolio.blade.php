@extends('layouts.admin')

@section('content')
<div class="admin-page">
    <div class="admin-page__header">
        <div>
            <h1 class="admin-page__title">Portfolio</h1>
        </div>

        <a href="{{ route('admin.portfolio.create') }}" class="admin-button admin-button--primary">
            Add post
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
                        <th>Title</th>
                        <th>Category</th>
                        <th>Date</th>
                        <th>Position</th>
                        <th class="admin-table__actions-heading">Actions</th>
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
                            <td>{{ $portfolioPost->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <div class="admin-table__position-controls">
                                    <span class="admin-table__position-number">{{ $portfolioPost->position }}</span>

                                    <form method="POST" action="{{ route('admin.portfolio.move-up', $portfolioPost->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button
                                            type="submit"
                                            title="Move up"
                                            aria-label="Move {{ $portfolioPost->title }} up"
                                            class="admin-icon-button admin-icon-button--position"
                                            @if ($loop->first) disabled @endif
                                        >
                                            <svg viewBox="0 0 20 20" focusable="false">
                                                <path fill="currentColor" fill-rule="evenodd" d="M10 4.5a1 1 0 0 1 .71.29l5 5a1 1 0 1 1-1.42 1.42L11 7.91V15a1 1 0 1 1-2 0V7.91l-3.29 3.3a1 1 0 0 1-1.42-1.42l5-5A1 1 0 0 1 10 4.5Z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.portfolio.move-down', $portfolioPost->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button
                                            type="submit"
                                            title="Move down"
                                            aria-label="Move {{ $portfolioPost->title }} down"
                                            class="admin-icon-button admin-icon-button--position"
                                            @if ($loop->last) disabled @endif
                                        >
                                            <svg viewBox="0 0 20 20" focusable="false">
                                                <path fill="currentColor" fill-rule="evenodd" d="M10 15.5a1 1 0 0 1-.71-.29l-5-5a1 1 0 1 1 1.42-1.42L9 12.09V5a1 1 0 1 1 2 0v7.09l3.29-3.3a1 1 0 0 1 1.42 1.42l-5 5a1 1 0 0 1-.71.29Z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
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
    </section>

    <x-admin-delete-confirmation-modal title="Delete post" message="Are you sure you want to delete this portfolio post?" />
</div>
@endsection
