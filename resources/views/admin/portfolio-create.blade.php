@extends('layouts.admin')

@section('content')
<div class="admin-page admin-page--edit-user">
    <a href="{{ route('admin.portfolio') }}" class="admin-back-link">
        {{ __('messages.admin.portfolio') }}
    </a>

    <div class="admin-page__header">
        <div>
            <h1 class="admin-page__title">{{ __('messages.admin.add_post') }}</h1>
        </div>
    </div>

    <section class="admin-panel admin-form-panel admin-form-panel--wide">
        @include('admin.partials.portfolio-form', [
            'formAction' => route('admin.portfolio.store'),
            'isThumbnailRequired' => true,
            'submitButtonLabel' => __('messages.admin.save'),
        ])
    </section>
</div>
@endsection
