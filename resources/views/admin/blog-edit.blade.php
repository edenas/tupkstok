@extends('layouts.admin')

@section('content')
<div class="admin-page admin-page--edit-user">
    <a href="{{ route('admin.blog') }}" class="admin-back-link">
        {{ __('messages.admin.blog') }}
    </a>

    <div class="admin-page__header">
        <div>
            <h1 class="admin-page__title">Redaguoti straipsnį</h1>
        </div>
    </div>

    <section class="admin-panel admin-form-panel admin-form-panel--wide">
        @include('admin.partials.blog-form', [
            'deleteAction' => route('admin.blog.destroy', $blogPost->id),
            'deleteFormId' => 'delete-blog-post-form-' . $blogPost->id,
            'formAction' => route('admin.blog.update', $blogPost->id),
            'formMethod' => 'PUT',
            'isThumbnailRequired' => false,
            'blogPost' => $blogPost,
            'thumbnailRemoveAction' => route('admin.blog.thumbnail.destroy', $blogPost->id),
            'thumbnailRemoveFormId' => 'remove-blog-thumbnail-form-' . $blogPost->id,
            'submitButtonLabel' => __('messages.admin.save'),
        ])
    </section>

    <x-admin-delete-confirmation-modal title="Ištrinti straipsnį" message="Ar tikrai norite ištrinti šį straipsnį?" />
</div>
@endsection

