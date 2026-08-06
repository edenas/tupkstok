@extends('layouts.admin')

@section('content')
<div class="admin-page admin-page--edit-user">
    <a href="{{ route('admin.blog') }}" class="admin-back-link">
        {{ __('messages.admin.blog') }}
    </a>

    <div class="admin-page__header">
        <div>
            <h1 class="admin-page__title">{{ __('messages.admin.add_post') }}</h1>
        </div>
    </div>

    <section class="admin-panel admin-form-panel admin-form-panel--wide">
        @include('admin.partials.blog-form', [
            'formAction' => route('admin.blog.store'),
            'isThumbnailRequired' => true,
            'submitButtonLabel' => __('messages.admin.save'),
        ])
    </section>
</div>
@endsection

