@extends('layouts.admin')

@section('content')
<div class="admin-page">
    <div class="admin-page__header">
        <div>
            <h1 class="admin-page__title">{{ __('messages.admin.blog') }}</h1>
        </div>

        <a href="{{ route('admin.blog.create') }}" class="admin-button admin-button--primary">
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
                    @forelse ($blogPosts as $blogPost)
                        <tr>
                            <td>
                                <a href="{{ route('admin.blog.edit', $blogPost->id) }}" class="admin-table__title-link">
                                    {{ $blogPost->title }}
                                </a>
                            </td>
                            <td>{{ $blogPost->category }}</td>
                            <td>
                                <time data-local-timestamp="{{ $blogPost->created_at->toIso8601String() }}" datetime="{{ $blogPost->created_at->toIso8601String() }}">
                                    {{ $blogPost->created_at->format('Y-m-d H:i') }}
                                </time>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.blog.position.update', $blogPost->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
                                    <input
                                        type="number"
                                        name="position"
                                        value="{{ $blogPost->position }}"
                                        min="1"
                                        step="1"
                                        aria-label="Position for {{ $blogPost->title }}"
                                        class="admin-table__position-input"
                                        onchange="this.form.submit()"
                                        required
                                    >
                                </form>
                            </td>
                            <td>
                                <div class="admin-table__actions">
                                    <a href="{{ route('admin.blog.edit', $blogPost->id) }}" title="Redaguoti straipsnį" aria-label="Redaguoti {{ $blogPost->title }}" class="admin-icon-button admin-icon-button--edit">
                                        <svg viewBox="0 0 20 20" focusable="false">
                                            <path fill="currentColor" d="M13.59 3.59a2 2 0 0 1 2.82 2.82l-.79.8-2.83-2.83.8-.79ZM11.38 5.79 3 14.17V17h2.83l8.38-8.38-2.83-2.83Z" />
                                        </svg>
                                    </a>
                                    <button
                                        type="button"
                                        title="Ištrinti straipsnį"
                                        aria-label="Ištrinti {{ $blogPost->title }}"
                                        class="admin-icon-button admin-icon-button--delete"
                                        data-delete-confirmation-trigger
                                        data-delete-form-id="delete-blog-post-form-{{ $blogPost->id }}"
                                    >
                                        <svg viewBox="0 0 20 20" focusable="false">
                                            <path fill="currentColor" fill-rule="evenodd" d="M4.29 4.29a1 1 0 0 1 1.42 0L10 8.59l4.29-4.3a1 1 0 1 1 1.42 1.42L11.41 10l4.3 4.29a1 1 0 0 1-1.42 1.42L10 11.41l-4.29 4.3a1 1 0 0 1-1.42-1.42L8.59 10l-4.3-4.29a1 1 0 0 1 0-1.42Z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <form id="delete-blog-post-form-{{ $blogPost->id }}" method="POST" action="{{ route('admin.blog.destroy', $blogPost->id) }}" class="admin-hidden-form">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">Straipsnių dar nėra.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $blogPosts->onEachSide(1)->links('pagination.admin') }}
    </section>

    <x-admin-delete-confirmation-modal title="Ištrinti straipsnį" message="Ar tikrai norite ištrinti šį straipsnį?" />
</div>
@endsection

