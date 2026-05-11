@extends('layouts.admin')

@section('content')
<div class="admin-page">
    <div class="admin-page__header">
        <div>
            <h1 class="admin-page__title">Admin Dashboard</h1>
        </div>
    </div>

    <section class="admin-panel admin-dashboard-panel">
        <p>Welcome to the admin panel, {{ auth()->user()->name }}.</p>
        <p>This is a protected area. Future features will be added here.</p>
    </section>
</div>
@endsection
