@extends('layouts.auth')

@section('content')
<section class="auth-login" aria-label="Admin login">
    <article class="auth-login__card">
        <div class="auth-login__header">
            <img src="{{ asset('images/logo.png') }}" alt="EPgalerija" class="auth-login__logo">
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
                <label for="login_identifier">Email or username</label>
                <input
                    type="text"
                    name="login_identifier"
                    id="login_identifier"
                    value="{{ old('login_identifier') }}"
                    autocomplete="username"
                    required
                >
            </div>

            <div class="auth-login__field">
                <label for="password">Password</label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    autocomplete="current-password"
                    required
                >
            </div>

            <label class="auth-login__remember">
                <input type="checkbox" name="remember">
                <span>Remember me</span>
            </label>

            <button type="submit" class="auth-login__submit">
                Log in
            </button>
        </form>
    </article>
</section>
@endsection
