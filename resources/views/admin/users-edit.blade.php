@extends('layouts.admin')

@section('content')
<div class="admin-page admin-page--edit-user">
    <a href="{{ route('admin.users') }}" class="admin-back-link">
        Back to users
    </a>

    <div class="admin-page__header">
        <div>
            <h1 class="admin-page__title">Edit user</h1>
        </div>
    </div>

    <section class="admin-panel admin-form-panel admin-form-panel--wide">
        @include('admin.partials.user-form', [
            'availableRoles' => $availableRoles,
            'deleteAction' => route('admin.users.destroy', $user->id),
            'deleteFormId' => 'delete-user-form-' . $user->id,
            'formAction' => route('admin.users.update', $user->id),
            'formMethod' => 'PUT',
            'isPasswordRequired' => false,
            'passwordHelpText' => 'Leave both password fields empty if you do not want to change the password. If you fill either field, both must be filled and match. Passwords must be at least 8 characters.',
            'passwordPlaceholder' => 'Leave empty if you do not want to change it',
            'submitButtonLabel' => 'Save',
            'user' => $user,
        ])
    </section>

    <x-admin-delete-confirmation-modal />
</div>
@endsection
