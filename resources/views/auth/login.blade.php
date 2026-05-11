@extends('layouts.app')

@section('content')
<section class="container mx-auto px-6 py-12">
    <div class="max-w-md mx-auto bg-white dark:bg-slate-800 p-8 rounded-lg shadow-md">
        <h1 class="text-2xl font-semibold text-center mb-6 text-slate-900 dark:text-white">Admin Login</h1>

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 rounded">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-4">
                <label for="login_identifier" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Email or username</label>
                <input type="text" name="login_identifier" id="login_identifier" value="{{ old('login_identifier') }}" required
                       class="mt-1 block w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white">
            </div>

            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Password</label>
                <input type="password" name="password" id="password" required
                       class="mt-1 block w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white">
            </div>

            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 dark:border-slate-600 text-blue-600 shadow-sm focus:ring-blue-500">
                    <span class="ml-2 text-sm text-slate-600 dark:text-slate-400">Remember me</span>
                </label>
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Log In
            </button>
        </form>
    </div>
</section>
@endsection