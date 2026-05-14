@extends('layouts.admin')

@section('content')
<div class="admin-page">
    <div class="admin-page__header">
        <div>
            <h1 class="admin-page__title">{{ __('messages.admin.users') }}</h1>
        </div>

        <a href="{{ route('admin.users.create') }}" class="admin-button admin-button--primary">
            {{ __('messages.admin.create_user') }}
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
                        <th>Email address</th>
                        <th>Role</th>
                        <th>{{ __('messages.admin.date') }}</th>
                        <th class="admin-table__actions-heading">{{ __('messages.admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="admin-table__title-link">
                                    {{ $user->name }}
                                </a>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>{{ ucfirst($user->role) }}</td>
                            <td>
                                <time data-local-timestamp="{{ $user->created_at->toIso8601String() }}" datetime="{{ $user->created_at->toIso8601String() }}">
                                    {{ $user->created_at->format('Y-m-d H:i') }}
                                </time>
                            </td>
                            <td>
                                <div class="admin-table__actions">
                                    <a href="{{ route('admin.users.edit', $user->id) }}" title="Edit user" aria-label="Edit {{ $user->name }}" class="admin-icon-button admin-icon-button--edit">
                                        <svg viewBox="0 0 20 20" focusable="false">
                                            <path fill="currentColor" d="M13.59 3.59a2 2 0 0 1 2.82 2.82l-.79.8-2.83-2.83.8-.79ZM11.38 5.79 3 14.17V17h2.83l8.38-8.38-2.83-2.83Z" />
                                        </svg>
                                    </a>
                                    <button
                                        type="button"
                                        title="Delete user"
                                        aria-label="Delete {{ $user->name }}"
                                        class="admin-icon-button admin-icon-button--delete"
                                        data-delete-confirmation-trigger
                                        data-delete-form-id="delete-user-form-{{ $user->id }}"
                                    >
                                        <svg viewBox="0 0 20 20" focusable="false">
                                            <path fill="currentColor" fill-rule="evenodd" d="M4.29 4.29a1 1 0 0 1 1.42 0L10 8.59l4.29-4.3a1 1 0 1 1 1.42 1.42L11.41 10l4.3 4.29a1 1 0 0 1-1.42 1.42L10 11.41l-4.29 4.3a1 1 0 0 1-1.42-1.42L8.59 10l-4.3-4.29a1 1 0 0 1 0-1.42Z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <form id="delete-user-form-{{ $user->id }}" method="POST" action="{{ route('admin.users.destroy', $user->id) }}" class="admin-hidden-form">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $users->onEachSide(1)->links('pagination.admin') }}
    </section>

    <x-admin-delete-confirmation-modal />
</div>
@endsection
