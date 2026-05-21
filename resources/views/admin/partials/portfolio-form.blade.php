@if ($errors->any())
    <div class="admin-alert admin-alert--error">
        <ul class="admin-alert__list">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('success'))
    <div class="admin-alert admin-alert--success">
        {{ session('success') }}
    </div>
@endif

@php($projectDetails = old('project_details', $portfolioPost->project_details ?? []))
@php($projectDetails = is_array($projectDetails) ? $projectDetails : [])
@php($projectDetailsEn = old('project_details_en', $portfolioPost->project_details_en ?? []))
@php($projectDetailsEn = is_array($projectDetailsEn) ? $projectDetailsEn : [])
@php($projectDetailsRu = old('project_details_ru', $portfolioPost->project_details_ru ?? []))
@php($projectDetailsRu = is_array($projectDetailsRu) ? $projectDetailsRu : [])
@php($hasThumbnail = isset($portfolioPost) && $portfolioPost->thumbnail)
@php($hasPostImage = isset($portfolioPost) && $portfolioPost->post_image)

<form method="POST" action="{{ $formAction }}" class="admin-form" enctype="multipart/form-data" data-language-tabs>
    @csrf

    @isset($formMethod)
        @method($formMethod)
    @endisset

    <div class="admin-form-tabs" role="tablist" aria-label="Portfolio language fields">
        <button type="button" class="admin-form-tabs__button admin-form-tabs__button--active" id="portfolio-tab-lt" role="tab" aria-selected="true" aria-controls="portfolio-panel-lt" data-language-tab="lt">
            LT
        </button>
        <button type="button" class="admin-form-tabs__button" id="portfolio-tab-en" role="tab" aria-selected="false" aria-controls="portfolio-panel-en" data-language-tab="en">
            EN
        </button>
        <button type="button" class="admin-form-tabs__button" id="portfolio-tab-ru" role="tab" aria-selected="false" aria-controls="portfolio-panel-ru" data-language-tab="ru">
            RU
        </button>
    </div>

    <div class="admin-form-tabs__panel" id="portfolio-panel-lt" role="tabpanel" aria-labelledby="portfolio-tab-lt" data-language-panel="lt">
        <section class="admin-form-card">
            <header class="admin-form-card__header">
                <h2>Short post</h2>
                <p>Content used on the public Graphics listing card.</p>
            </header>

            <div class="admin-form-card__body">
                <div class="admin-form__field">
                    <label for="title" class="admin-form__label">Title name</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $portfolioPost->title ?? '') }}" required class="admin-form__input">
                </div>

                <div class="admin-form__field">
                    <label for="short_description" class="admin-form__label">Short description</label>
                    <textarea name="short_description" id="short_description" class="admin-form__input admin-form__textarea admin-form__textarea--short">{{ old('short_description', $portfolioPost->short_description ?? $portfolioPost->description ?? '') }}</textarea>
                    <p class="admin-form__help-text">Used only on the public Graphics listing cards.</p>
                </div>

                <div class="admin-form__field">
                    <label for="thumbnail" class="admin-form__label">Thumbnail image</label>

                    @if ($hasThumbnail)
                        @php($thumbnailUrl = $portfolioPost->thumbnailUrl())
                        <div class="admin-form__thumbnail-area">
                            <div class="admin-form__thumbnail-preview">
                                <img src="{{ $thumbnailUrl }}" alt="{{ $portfolioPost->title }} thumbnail">

                                @if (isset($thumbnailRemoveFormId))
                                    <button
                                        type="button"
                                        class="admin-form__image-remove-button"
                                        title="Remove thumbnail image"
                                        aria-label="Remove thumbnail image"
                                        data-delete-confirmation-trigger
                                        data-delete-form-id="{{ $thumbnailRemoveFormId }}"
                                        data-delete-confirmation-title="Remove image"
                                        data-delete-confirmation-message="Are you sure you want to remove this thumbnail image?"
                                        data-delete-confirmation-submit-label="Remove image"
                                    >
                                        <svg viewBox="0 0 20 20" focusable="false" aria-hidden="true">
                                            <path fill="currentColor" fill-rule="evenodd" d="M8 2a2 2 0 0 0-2 2v1H3.75a.75.75 0 0 0 0 1.5h.8l.62 8.74A3 3 0 0 0 8.16 18h3.68a3 3 0 0 0 2.99-2.76l.62-8.74h.8a.75.75 0 0 0 0-1.5H14V4a2 2 0 0 0-2-2H8Zm1.5 7.25a.75.75 0 0 0-1.5 0v5a.75.75 0 0 0 1.5 0v-5Zm2.5 0a.75.75 0 0 0-1.5 0v5a.75.75 0 0 0 1.5 0v-5ZM7.5 4a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v1h-5V4Z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endif

                    @unless ($hasThumbnail)
                        <input
                            type="file"
                            name="thumbnail"
                            id="thumbnail"
                            accept="image/jpeg,image/png,image/webp"
                            class="admin-form__input admin-form__file-input"
                            @if ($isThumbnailRequired) required @endif
                        >
                        <p class="admin-form__help-text">
                            Upload a square 1:1 JPG, PNG, or WebP image up to 4 MB.
                            @unless ($isThumbnailRequired)
                                Leave empty to keep the current thumbnail.
                            @endunless
                        </p>
                    @endunless
                </div>
            </div>
        </section>

        <section class="admin-form-card">
            <header class="admin-form-card__header">
                <h2>Category</h2>
            </header>

            <div class="admin-form-card__body">
                <div class="admin-form__field">
                    <label for="category" class="admin-form__label">Category</label>
                    <input type="text" name="category" id="category" value="{{ old('category', $portfolioPost->category ?? '') }}" required class="admin-form__input">
                </div>
            </div>
        </section>

        <section class="admin-form-card">
            <header class="admin-form-card__header">
                <h2>YouTube video</h2>
            </header>

            <div class="admin-form-card__body">
                <div class="admin-form__field">
                    <label for="youtube_url" class="admin-form__label">YouTube video URL</label>
                    <input
                        type="url"
                        name="youtube_url"
                        id="youtube_url"
                        value="{{ old('youtube_url', $portfolioPost->youtube_url ?? '') }}"
                        placeholder="https://www.youtube.com/watch?v=VIDEO_ID"
                        class="admin-form__input"
                    >
                    <p class="admin-form__help-text">Optional YouTube link for the single project page.</p>
                </div>
            </div>
        </section>

        <section class="admin-form-card">
            <header class="admin-form-card__header">
                <h2>Post description</h2>
                <p>Full text content shown on the single portfolio post page.</p>
            </header>

            <div class="admin-form-card__body">
                <div class="admin-form__field">
                    <label for="content_heading" class="admin-form__label">Content heading</label>
                    <input type="text" name="content_heading" id="content_heading" value="{{ old('content_heading', $portfolioPost->content_heading ?? '') }}" class="admin-form__input">
                </div>

                <div class="admin-form__field">
                    <label for="description" class="admin-form__label">Description</label>
                    <textarea name="description" id="description" class="admin-form__input admin-form__textarea admin-form__textarea--large">{{ old('description', $portfolioPost->description ?? '') }}</textarea>
                    <p class="admin-form__help-text">Full description for the single project page. Line breaks are preserved.</p>
                </div>
            </div>
        </section>

        <section class="admin-form-card">
            <header class="admin-form-card__header">
                <h2>Project details</h2>
            </header>

            <div class="admin-form-card__body">
                <div class="admin-form__field" data-project-details>
                    <label for="project_detail_input" class="admin-form__label">Project details</label>
                    <div class="admin-form__project-detail-entry">
                        <input type="text" id="project_detail_input" class="admin-form__input" data-project-detail-input>
                        <button type="button" class="admin-form__project-detail-add" title="Add detail" aria-label="Add project detail" data-project-detail-add>
                            <svg viewBox="0 0 20 20" focusable="false" aria-hidden="true">
                                <path fill="currentColor" fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-8 8a1 1 0 0 1-1.4 0l-4-4a1 1 0 0 1 1.4-1.4L8 12.59l7.3-7.3a1 1 0 0 1 1.4 0Z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                    <div class="admin-form__project-detail-list" data-project-detail-list>
                        @foreach ($projectDetails as $detail)
                            @continue(trim((string) $detail) === '')
                            <div class="admin-form__project-detail-item" data-project-detail-item>
                                <input type="hidden" name="project_details[]" value="{{ $detail }}">
                                <span>{{ $detail }}</span>
                                <button type="button" class="admin-form__project-detail-remove" title="Remove detail" aria-label="Remove {{ $detail }}" data-project-detail-remove>
                                    <svg viewBox="0 0 20 20" focusable="false" aria-hidden="true">
                                        <path fill="currentColor" fill-rule="evenodd" d="M5.29 5.29a1 1 0 0 1 1.42 0L10 8.59l3.29-3.3a1 1 0 1 1 1.42 1.42L11.41 10l3.3 3.29a1 1 0 0 1-1.42 1.42L10 11.41l-3.29 3.3a1 1 0 0 1-1.42-1.42L8.59 10l-3.3-3.29a1 1 0 0 1 0-1.42Z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="admin-form-card">
            <header class="admin-form-card__header">
                <h2>Post image</h2>
            </header>

            <div class="admin-form-card__body">
                <div class="admin-form__field">
                    <label for="post_image" class="admin-form__label">Post image</label>

                    @if ($hasPostImage)
                        @php($postImageUrl = $portfolioPost->postImageUrl())
                        <div class="admin-form__thumbnail-area">
                            <div class="admin-form__thumbnail-preview admin-form__thumbnail-preview--post-image">
                                <img src="{{ $postImageUrl }}" alt="{{ $portfolioPost->title }} post image">

                                @if (isset($postImageRemoveFormId))
                                    <button
                                        type="button"
                                        class="admin-form__image-remove-button"
                                        title="Remove post image"
                                        aria-label="Remove post image"
                                        data-delete-confirmation-trigger
                                        data-delete-form-id="{{ $postImageRemoveFormId }}"
                                        data-delete-confirmation-title="Remove post image"
                                        data-delete-confirmation-message="Are you sure you want to remove this post image?"
                                        data-delete-confirmation-submit-label="Remove image"
                                    >
                                        <svg viewBox="0 0 20 20" focusable="false" aria-hidden="true">
                                            <path fill="currentColor" fill-rule="evenodd" d="M8 2a2 2 0 0 0-2 2v1H3.75a.75.75 0 0 0 0 1.5h.8l.62 8.74A3 3 0 0 0 8.16 18h3.68a3 3 0 0 0 2.99-2.76l.62-8.74h.8a.75.75 0 0 0 0-1.5H14V4a2 2 0 0 0-2-2H8Zm1.5 7.25a.75.75 0 0 0-1.5 0v5a.75.75 0 0 0 1.5 0v-5Zm2.5 0a.75.75 0 0 0-1.5 0v5a.75.75 0 0 0 1.5 0v-5ZM7.5 4a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v1h-5V4Z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endif

                    @unless ($hasPostImage)
                        <input
                            type="file"
                            name="post_image"
                            id="post_image"
                            accept="image/jpeg,image/png,image/webp"
                            class="admin-form__input admin-form__file-input"
                        >
                        <p class="admin-form__help-text">
                            Upload a JPG, PNG, or WebP image up to 4 MB for the single project page.
                            @unless ($isThumbnailRequired)
                                Leave empty to keep the current post image.
                            @endunless
                        </p>
                    @endunless
                </div>
            </div>
        </section>
    </div>

    <div class="admin-form-tabs__panel admin-form-tabs__panel--hidden" id="portfolio-panel-en" role="tabpanel" aria-labelledby="portfolio-tab-en" data-language-panel="en" hidden>
        <section class="admin-form-card">
            <header class="admin-form-card__header">
                <h2>Short post translation</h2>
                <p>Manual English translation for the public Graphics listing card.</p>
            </header>

            <div class="admin-form-card__body">
                <div class="admin-form__field">
                    <label for="title_en" class="admin-form__label">Title name EN</label>
                    <input type="text" name="title_en" id="title_en" value="{{ old('title_en', $portfolioPost->title_en ?? '') }}" class="admin-form__input">
                </div>

                <div class="admin-form__field">
                    <label for="short_description_en" class="admin-form__label">Short description EN</label>
                    <textarea name="short_description_en" id="short_description_en" class="admin-form__input admin-form__textarea admin-form__textarea--short">{{ old('short_description_en', $portfolioPost->short_description_en ?? '') }}</textarea>
                </div>
            </div>
        </section>

        <section class="admin-form-card">
            <header class="admin-form-card__header">
                <h2>Category translation</h2>
            </header>

            <div class="admin-form-card__body">
                <div class="admin-form__field">
                    <label for="category_en" class="admin-form__label">Category EN</label>
                    <input type="text" name="category_en" id="category_en" value="{{ old('category_en', $portfolioPost->category_en ?? '') }}" class="admin-form__input">
                </div>
            </div>
        </section>

        <section class="admin-form-card">
            <header class="admin-form-card__header">
                <h2>Post description translation</h2>
                <p>Manual English translation for the single project page.</p>
            </header>

            <div class="admin-form-card__body">
                <div class="admin-form__field">
                    <label for="content_heading_en" class="admin-form__label">Content heading EN</label>
                    <input type="text" name="content_heading_en" id="content_heading_en" value="{{ old('content_heading_en', $portfolioPost->content_heading_en ?? '') }}" class="admin-form__input">
                </div>

                <div class="admin-form__field">
                    <label for="description_en" class="admin-form__label">Description EN</label>
                    <textarea name="description_en" id="description_en" class="admin-form__input admin-form__textarea admin-form__textarea--large">{{ old('description_en', $portfolioPost->description_en ?? '') }}</textarea>
                </div>
            </div>
        </section>

        <section class="admin-form-card">
            <header class="admin-form-card__header">
                <h2>Project details translation</h2>
            </header>

            <div class="admin-form-card__body">
                <div class="admin-form__field" data-project-details data-project-detail-name="project_details_en[]">
                    <label for="project_detail_input_en" class="admin-form__label">Project details EN</label>
                    <div class="admin-form__project-detail-entry">
                        <input type="text" id="project_detail_input_en" class="admin-form__input" data-project-detail-input>
                        <button type="button" class="admin-form__project-detail-add" title="Add detail" aria-label="Add project detail" data-project-detail-add>
                            <svg viewBox="0 0 20 20" focusable="false" aria-hidden="true">
                                <path fill="currentColor" fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-8 8a1 1 0 0 1-1.4 0l-4-4a1 1 0 0 1 1.4-1.4L8 12.59l7.3-7.3a1 1 0 0 1 1.4 0Z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                    <div class="admin-form__project-detail-list" data-project-detail-list>
                        @foreach ($projectDetailsEn as $detail)
                            @continue(trim((string) $detail) === '')
                            <div class="admin-form__project-detail-item" data-project-detail-item>
                                <input type="hidden" name="project_details_en[]" value="{{ $detail }}">
                                <span>{{ $detail }}</span>
                                <button type="button" class="admin-form__project-detail-remove" title="Remove detail" aria-label="Remove {{ $detail }}" data-project-detail-remove>
                                    <svg viewBox="0 0 20 20" focusable="false" aria-hidden="true">
                                        <path fill="currentColor" fill-rule="evenodd" d="M5.29 5.29a1 1 0 0 1 1.42 0L10 8.59l3.29-3.3a1 1 0 1 1 1.42 1.42L11.41 10l3.3 3.29a1 1 0 0 1-1.42 1.42L10 11.41l-3.29 3.3a1 1 0 0 1-1.42-1.42L8.59 10l-3.3-3.29a1 1 0 0 1 0-1.42Z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="admin-form-tabs__panel admin-form-tabs__panel--hidden" id="portfolio-panel-ru" role="tabpanel" aria-labelledby="portfolio-tab-ru" data-language-panel="ru" hidden>
        <section class="admin-form-card">
            <header class="admin-form-card__header">
                <h2>Russian translation</h2>
                <p>Manual Russian translation for public Graphics pages.</p>
            </header>

            <div class="admin-form-card__body">
                <div class="admin-form__field">
                    <label for="title_ru" class="admin-form__label">Title RU</label>
                    <input type="text" name="title_ru" id="title_ru" value="{{ old('title_ru', $portfolioPost->title_ru ?? '') }}" class="admin-form__input">
                </div>

                <div class="admin-form__field">
                    <label for="short_description_ru" class="admin-form__label">Short description RU</label>
                    <textarea name="short_description_ru" id="short_description_ru" class="admin-form__input admin-form__textarea admin-form__textarea--short">{{ old('short_description_ru', $portfolioPost->short_description_ru ?? '') }}</textarea>
                </div>

                <div class="admin-form__field">
                    <label for="category_ru" class="admin-form__label">Category RU</label>
                    <input type="text" name="category_ru" id="category_ru" value="{{ old('category_ru', $portfolioPost->category_ru ?? '') }}" class="admin-form__input">
                </div>

                <div class="admin-form__field">
                    <label for="content_heading_ru" class="admin-form__label">Content heading RU</label>
                    <input type="text" name="content_heading_ru" id="content_heading_ru" value="{{ old('content_heading_ru', $portfolioPost->content_heading_ru ?? '') }}" class="admin-form__input">
                </div>

                <div class="admin-form__field">
                    <label for="description_ru" class="admin-form__label">Description RU</label>
                    <textarea name="description_ru" id="description_ru" class="admin-form__input admin-form__textarea admin-form__textarea--large">{{ old('description_ru', $portfolioPost->description_ru ?? '') }}</textarea>
                </div>

                <div class="admin-form__field" data-project-details data-project-detail-name="project_details_ru[]">
                    <label for="project_detail_input_ru" class="admin-form__label">Project details RU</label>
                    <div class="admin-form__project-detail-entry">
                        <input type="text" id="project_detail_input_ru" class="admin-form__input" data-project-detail-input>
                        <button type="button" class="admin-form__project-detail-add" title="Add detail" aria-label="Add project detail" data-project-detail-add>
                            <svg viewBox="0 0 20 20" focusable="false" aria-hidden="true">
                                <path fill="currentColor" fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-8 8a1 1 0 0 1-1.4 0l-4-4a1 1 0 0 1 1.4-1.4L8 12.59l7.3-7.3a1 1 0 0 1 1.4 0Z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                    <div class="admin-form__project-detail-list" data-project-detail-list>
                        @foreach ($projectDetailsRu as $detail)
                            @continue(trim((string) $detail) === '')
                            <div class="admin-form__project-detail-item" data-project-detail-item>
                                <input type="hidden" name="project_details_ru[]" value="{{ $detail }}">
                                <span>{{ $detail }}</span>
                                <button type="button" class="admin-form__project-detail-remove" title="Remove detail" aria-label="Remove {{ $detail }}" data-project-detail-remove>
                                    <svg viewBox="0 0 20 20" focusable="false" aria-hidden="true">
                                        <path fill="currentColor" fill-rule="evenodd" d="M5.29 5.29a1 1 0 0 1 1.42 0L10 8.59l3.29-3.3a1 1 0 1 1 1.42 1.42L11.41 10l3.3 3.29a1 1 0 0 1-1.42 1.42L10 11.41l-3.29 3.3a1 1 0 0 1-1.42-1.42L8.59 10l-3.3-3.29a1 1 0 0 1 0-1.42Z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="admin-form__actions">
        <button type="submit" class="admin-button admin-button--primary">
            {{ $submitButtonLabel }}
        </button>
        <a href="{{ route('admin.portfolio') }}" class="admin-button admin-button--secondary">
            {{ __('messages.admin.cancel') }}
        </a>

        @isset($deleteFormId)
            <button
                type="button"
                class="admin-button admin-button--danger admin-form__delete-button"
                data-delete-confirmation-trigger
                data-delete-form-id="{{ $deleteFormId }}"
            >
                {{ __('messages.admin.delete_post') }}
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

@isset($thumbnailRemoveFormId)
    <form id="{{ $thumbnailRemoveFormId }}" method="POST" action="{{ $thumbnailRemoveAction }}" class="admin-hidden-form">
        @csrf
        @method('DELETE')
    </form>
@endisset

@isset($postImageRemoveFormId)
    <form id="{{ $postImageRemoveFormId }}" method="POST" action="{{ $postImageRemoveAction }}" class="admin-hidden-form">
        @csrf
        @method('DELETE')
    </form>
@endisset
