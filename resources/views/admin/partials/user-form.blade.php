@php
    $selectedRole = old('role', $user->role ?? 'user');
    $roleLabels = [
        'administrator' => 'Administratorius',
        'editor' => 'Redaktorius',
        'user' => 'Vartotojas',
    ];
@endphp

@if ($errors->any())
    <div class="admin-alert admin-alert--error">
        <ul class="admin-alert__list">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ $formAction }}" class="admin-form">
    @csrf

    @isset($formMethod)
        @method($formMethod)
    @endisset

    <div class="admin-form__field">
        <label for="name" class="admin-form__label">Vardas</label>
        <input type="text" name="name" id="name" value="{{ old('name', $user->name ?? '') }}" required class="admin-form__input">
    </div>

    <div class="admin-form__field">
        <label for="email" class="admin-form__label">El. pašto adresas</label>
        <input type="email" name="email" id="email" value="{{ old('email', $user->email ?? '') }}" required class="admin-form__input">
    </div>

    <div class="admin-form__field">
        <label for="role" class="admin-form__label">Rolė</label>
        <select name="role" id="role" required class="admin-form__input admin-form__select">
            @foreach ($availableRoles as $availableRole)
                <option value="{{ $availableRole }}" @selected($selectedRole === $availableRole)>
                    {{ $roleLabels[$availableRole] ?? $availableRole }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="admin-form__field">
        <label for="password" class="admin-form__label">Slaptažodis</label>
        <div class="admin-password-field">
            <input
                type="password"
                name="password"
                id="password"
                class="admin-form__input admin-form__input--with-button"
                placeholder="{{ $passwordPlaceholder }}"
                data-password-toggle-target="#password"
                @if ($isPasswordRequired) required @endif
            >
            <x-admin-password-toggle-button target="#password" />
        </div>
    </div>

    <div class="admin-form__field">
        <label for="password_confirmation" class="admin-form__label">Pakartokite slaptažodį</label>
        <div class="admin-password-field">
            <input
                type="password"
                name="password_confirmation"
                id="password_confirmation"
                class="admin-form__input admin-form__input--with-button"
                placeholder="{{ $passwordPlaceholder }}"
                data-password-toggle-target="#password_confirmation"
                @if ($isPasswordRequired) required @endif
            >
            <x-admin-password-toggle-button target="#password_confirmation" />
        </div>
        <p class="admin-form__help-text">{{ $passwordHelpText }}</p>
    </div>

    <div class="admin-form__actions {{ isset($deleteFormId) ? 'admin-form__actions--with-delete' : '' }}">
        <div class="admin-form__primary-actions">
            <button type="submit" class="admin-button admin-button--primary">
                {{ $submitButtonLabel }}
            </button>
            <a href="{{ route('admin.users') }}" class="admin-button admin-button--secondary">
                Atšaukti
            </a>
        </div>

        @isset($deleteFormId)
            <button
                type="button"
                class="admin-button admin-button--danger admin-form__delete-button"
                data-delete-confirmation-trigger
                data-delete-form-id="{{ $deleteFormId }}"
            >
                Ištrinti vartotoją
            </button>
        @endisset
    </div>
</form>

@isset($deleteFormId)
    <form id="{{ $deleteFormId }}" method="POST" action="{{ $deleteAction }}" class="admin-hidden-form">
        @csrf
        @method('DELETE')
    </form>
@endisset
