@extends('layouts.admin')

@section('content')
<div class="admin-page admin-page--edit-user">
    <a href="{{ route('admin.users') }}" class="admin-back-link">
        Grįžti į vartotojus
    </a>

    <div class="admin-page__header">
        <div>
            <h1 class="admin-page__title">Sukurti vartotoją</h1>
        </div>
    </div>

    <section class="admin-panel admin-form-panel admin-form-panel--wide">
        @include('admin.partials.user-form', [
            'availableRoles' => $availableRoles,
            'formAction' => route('admin.users.store'),
            'isPasswordRequired' => true,
            'passwordHelpText' => 'Slaptažodis ir jo patvirtinimas yra privalomi. Slaptažodis turi būti bent 8 simbolių.',
            'passwordPlaceholder' => 'Įveskite slaptažodį',
            'submitButtonLabel' => 'Sukurti vartotoją',
        ])
    </section>
</div>
@endsection
