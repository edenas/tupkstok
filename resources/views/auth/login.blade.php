@extends('layouts.auth')

@section('content')
<section class="auth-login" aria-label="Administratoriaus prisijungimas">
    <article class="auth-login__card">
        <div class="auth-login__header">
            <img src="{{ asset('storage/logo.png') }}" alt="Tupk Stok" class="auth-login__logo">
        </div>

        @if ($errors->any())
            <div class="auth-login__errors" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="auth-login__form">
            @csrf

            <div class="auth-login__field">
                <label for="username">Vartotojo vardas</label>
                <input
                    type="text"
                    name="username"
                    id="username"
                    value="{{ old('username') }}"
                    autocomplete="username"
                    placeholder="Įveskite vartotojo vardą"
                    required
                >
            </div>

            <div class="auth-login__field">
                <label for="password">Slaptažodis</label>
                <div class="admin-password-field auth-login__password-field">
                    <input
                        type="password"
                        name="password"
                        id="password"
                        autocomplete="current-password"
                        required
                    >
                    <x-admin-password-toggle-button target="#password" />
                </div>
            </div>

            <label class="auth-login__remember">
                <input type="checkbox" name="remember">
                <span>Prisiminti mane</span>
            </label>

            <button type="submit" class="auth-login__submit">
                Prisijungti
            </button>
        </form>
    </article>
</section>
@endsection
