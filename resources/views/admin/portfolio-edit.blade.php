@extends('layouts.admin')

@section('content')
<div class="admin-page admin-page--edit-user">
    <a href="{{ route('admin.portfolio') }}" class="admin-back-link">
        Back to portfolio
    </a>

    <div class="admin-page__header">
        <div>
            <h1 class="admin-page__title">Edit portfolio post</h1>
        </div>
    </div>

    <section class="admin-panel admin-form-panel admin-form-panel--wide">
        @include('admin.partials.portfolio-form', [
            'deleteAction' => route('admin.portfolio.destroy', $portfolioPost->id),
            'deleteFormId' => 'delete-portfolio-post-form-' . $portfolioPost->id,
            'formAction' => route('admin.portfolio.update', $portfolioPost->id),
            'formMethod' => 'PUT',
            'isThumbnailRequired' => false,
            'postImageRemoveAction' => route('admin.portfolio.post-image.destroy', $portfolioPost->id),
            'postImageRemoveFormId' => 'remove-portfolio-post-image-form-' . $portfolioPost->id,
            'portfolioPost' => $portfolioPost,
            'thumbnailRemoveAction' => route('admin.portfolio.thumbnail.destroy', $portfolioPost->id),
            'thumbnailRemoveFormId' => 'remove-portfolio-thumbnail-form-' . $portfolioPost->id,
            'submitButtonLabel' => 'Save',
        ])
    </section>

    <x-admin-delete-confirmation-modal title="Delete post" message="Are you sure you want to delete this portfolio post?" />
</div>
@endsection
