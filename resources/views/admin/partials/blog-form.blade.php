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

@php($hasThumbnail = isset($blogPost) && $blogPost->thumbnail)
@php($blogCategories = \App\Support\BlogCategories::ALL)
@php($selectedCategory = \App\Support\BlogCategories::normalize(old('category', $blogPost->category ?? null)))
@php($articleInformation = old('project_details', $blogPost->project_details ?? []))
@php($articleInformation = is_array($articleInformation) ? $articleInformation : [])
@php($showDisclaimer = filter_var($articleInformation['show_disclaimer'] ?? true, FILTER_VALIDATE_BOOL))

<form method="POST" action="{{ $formAction }}" class="admin-form" enctype="multipart/form-data">
    @csrf

    @isset($formMethod)
        @method($formMethod)
    @endisset

    <section class="admin-form-card">
        <div class="admin-form-card__body">
            <div class="admin-form__field">
                <label for="title" class="admin-form__label">Pavadinimas</label>
                <input type="text" name="title" id="title" value="{{ old('title', $blogPost->title ?? '') }}" required class="admin-form__input">
            </div>

            <div class="admin-form__field">
                <label for="thumbnail" class="admin-form__label">Miniatiūros nuotrauka</label>

                @if ($hasThumbnail)
                    @php($thumbnailUrl = $blogPost->thumbnailUrl())
                    <div class="admin-form__thumbnail-area">
                        <div class="admin-form__thumbnail-preview">
                            <img src="{{ $thumbnailUrl }}" alt="{{ $blogPost->title }} miniatiūra">

                            @if (isset($thumbnailRemoveFormId))
                                <button
                                    type="button"
                                    class="admin-form__image-remove-button"
                                    title="Pašalinti miniatiūrą"
                                    aria-label="Pašalinti miniatiūrą"
                                    data-delete-confirmation-trigger
                                    data-delete-form-id="{{ $thumbnailRemoveFormId }}"
                                    data-delete-confirmation-title="Pašalinti nuotrauką"
                                    data-delete-confirmation-message="Ar tikrai norite pašalinti šią miniatiūrą?"
                                    data-delete-confirmation-submit-label="Pašalinti nuotrauką"
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
                        Rekomenduojamas dydis: 1200 × 1200 px. Įkelti paveikslėliai bus automatiškai apkarpyti ir optimizuoti.
                        @unless ($isThumbnailRequired)
                            Palikite tuščią, jei norite išsaugoti esamą miniatiūrą.
                        @endunless
                    </p>
                @endunless
            </div>
        </div>
    </section>

    <section class="admin-form-card">
        <header class="admin-form-card__header">
            <h2>Kategorija</h2>
        </header>

        <div class="admin-form-card__body">
            <div class="admin-form__field">
                <select name="category" id="category" required class="admin-form__input" aria-label="Kategorija">
                    @foreach ($blogCategories as $category)
                        <option value="{{ $category }}" @selected($selectedCategory === $category)>{{ $category }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </section>

    <section class="admin-form-card">
        <header class="admin-form-card__header">
            <h2>YouTube video</h2>
        </header>

        <div class="admin-form-card__body">
            <div class="admin-form__field">
                <input
                    type="url"
                    name="youtube_url"
                    id="youtube_url"
                    value="{{ old('youtube_url', $blogPost->youtube_url ?? '') }}"
                    placeholder="https://www.youtube.com/watch?v=VIDEO_ID"
                    aria-label="YouTube video"
                    class="admin-form__input"
                >
                <p class="admin-form__help-text">Pasirenkama YouTube nuoroda straipsniui.</p>
            </div>
        </div>
    </section>

    <section class="admin-form-card">
        <header class="admin-form-card__header">
            <h2>Straipsnio turinys</h2>
        </header>

        <div class="admin-form-card__body">
            <div class="admin-form__field">
                <textarea
                    name="description"
                    id="description"
                    aria-label="Straipsnio turinys"
                    class="admin-form__input admin-form__textarea admin-form__textarea--large"
                    data-rich-text-editor
                    data-image-upload-url="{{ route('admin.blog.editor-images.store') }}"
                >{{ old('description', $blogPost->description ?? '') }}</textarea>
                <p class="admin-form__help-text">Pilnas straipsnio tekstas. Eilučių lūžiai išsaugomi.</p>
            </div>
        </div>
    </section>

    <section class="admin-form-card">
        <header class="admin-form-card__header">
            <h2>Straipsnio informacija</h2>
        </header>

        <div class="admin-form-card__body">
            <div class="admin-form__field">
                <label for="article_author" class="admin-form__label">Straipsnio autorius</label>
                <input
                    type="text"
                    name="project_details[author]"
                    id="article_author"
                    value="{{ $articleInformation['author'] ?? '' }}"
                    class="admin-form__input"
                >
                <p class="admin-form__help-text">Jei paliksite tuščią, bus rodoma „Tūpk Stok redakcija“.</p>
            </div>

            <div class="admin-form__field">
                <label for="article_source" class="admin-form__label">Šaltinis</label>
                <input
                    type="text"
                    name="project_details[source]"
                    id="article_source"
                    value="{{ $articleInformation['source'] ?? '' }}"
                    class="admin-form__input"
                >
                <p class="admin-form__help-text">Jei paliksite tuščią, bus rodoma „www.tupkstok.lt“.</p>
            </div>

            <div class="admin-form__field">
                <input type="hidden" name="project_details[show_disclaimer]" value="0">
                <label class="admin-form__checkbox-label" for="show_disclaimer">
                    <input
                        type="checkbox"
                        name="project_details[show_disclaimer]"
                        id="show_disclaimer"
                        value="1"
                        @checked($showDisclaimer)
                    >
                    <span>Rodyti atsakomybės pranešimą</span>
                </label>
            </div>

            <div class="admin-form__field">
                <label for="disclaimer_text" class="admin-form__label">Atsakomybės pranešimo tekstas</label>
                <textarea
                    name="project_details[disclaimer_text]"
                    id="disclaimer_text"
                    class="admin-form__input admin-form__textarea admin-form__textarea--short"
                >{{ $articleInformation['disclaimer_text'] ?? '' }}</textarea>
            </div>
        </div>
    </section>

    <div class="admin-form__actions">
        <button type="submit" name="save_action" value="stay" class="admin-button admin-button--primary">
            {{ $submitButtonLabel }}
        </button>
        <button type="submit" name="save_action" value="return" class="admin-button admin-button--secondary">
            Išsaugoti ir grįžti
        </button>
        <a href="{{ route('admin.blog') }}" class="admin-button admin-button--secondary">
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


