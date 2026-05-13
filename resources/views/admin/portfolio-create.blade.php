@extends('layouts.admin')

@section('content')
<div class="admin-page admin-page--edit-user">
    <a href="{{ route('admin.portfolio') }}" class="admin-back-link">
        Back to portfolio
    </a>

    <div class="admin-page__header">
        <div>
            <h1 class="admin-page__title">Create portfolio post</h1>
        </div>
    </div>

    <section class="admin-panel admin-form-panel admin-form-panel--wide">
        @include('admin.partials.portfolio-form', [
            'formAction' => route('admin.portfolio.store'),
            'isThumbnailRequired' => true,
            'submitButtonLabel' => 'Create post',
        ])
    </section>
</div>
@endsection
