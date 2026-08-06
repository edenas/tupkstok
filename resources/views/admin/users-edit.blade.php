@extends('layouts.admin')

@section('content')
<div class="admin-page admin-page--edit-user">
    <a href="{{ route('admin.users') }}" class="admin-back-link">
        Grįžti į vartotojus
    </a>

    <div class="admin-page__header">
        <div>
            <h1 class="admin-page__title">Redaguoti vartotoją</h1>
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
            'passwordHelpText' => 'Palikite abu slaptažodžio laukus tuščius, jei nenorite keisti slaptažodžio. Jei užpildote vieną lauką, abu turi būti užpildyti ir sutapti. Slaptažodis turi būti bent 8 simbolių.',
            'passwordPlaceholder' => 'Palikite tuščią, jei nenorite keisti',
            'submitButtonLabel' => 'Išsaugoti',
            'user' => $user,
        ])
    </section>

    <x-admin-delete-confirmation-modal />
</div>
@endsection
