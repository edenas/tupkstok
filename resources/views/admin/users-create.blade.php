@extends('layouts.admin')

@section('content')
<div class="admin-page admin-page--edit-user">
    <a href="{{ route('admin.users') }}" class="admin-back-link">
        Back to users
    </a>

    <div class="admin-page__header">
        <div>
            <h1 class="admin-page__title">Create user</h1>
        </div>
    </div>

    <section class="admin-panel admin-form-panel admin-form-panel--wide">
        @include('admin.partials.user-form', [
            'availableRoles' => $availableRoles,
            'formAction' => route('admin.users.store'),
            'isPasswordRequired' => true,
            'passwordHelpText' => 'Password and confirmation are required. Passwords must be at least 8 characters.',
            'passwordPlaceholder' => 'Enter a password',
            'submitButtonLabel' => 'Create User',
        ])
    </section>
</div>
@endsection
