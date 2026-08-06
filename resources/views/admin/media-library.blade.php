@extends('layouts.admin')

@section('content')
<div class="admin-page">
    <div class="admin-page__header">
        <div>
            <h1 class="admin-page__title">Failų saugykla</h1>
        </div>
    </div>

    @if (session('success'))
        <div class="admin-alert admin-alert--success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="admin-alert admin-alert--error">
            <ul class="admin-alert__list">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="admin-panel admin-media-upload">
        <form method="POST" action="{{ route('admin.media-library.store') }}" class="admin-media-upload__form" enctype="multipart/form-data">
            @csrf

            <div class="admin-form__field admin-media-upload__field">
                <label for="media-library-file" class="admin-form__label">Įkelti failą</label>
                <div class="admin-media-upload__picker-row">
                    <label for="media-library-file" class="admin-button admin-button--secondary admin-media-upload__picker">
                        Pasirinkti failą
                    </label>
                    <span class="admin-media-upload__filename" data-media-file-name>Failas nepasirinktas</span>
                    <input
                        id="media-library-file"
                        type="file"
                        name="file"
                        class="admin-media-upload__native-input"
                        accept="image/jpeg,image/png,image/webp,image/gif"
                        data-media-file-input
                        required
                    >
                </div>
                <p class="admin-form__help-text">Pasirinkti failą: jpg, jpeg, png, webp arba gif iki 5 MB.</p>
            </div>

            <button type="submit" class="admin-button admin-button--primary">Įkelti</button>
        </form>
    </section>

    <section class="admin-panel admin-media-library">
        @forelse ($files as $file)
            <article class="admin-media-card">
                <div class="admin-media-card__thumbnail">
                    <img src="{{ $file['url'] }}" alt="{{ $file['filename'] }}">
                </div>

                <div class="admin-media-card__body">
                    <h2 class="admin-media-card__title">{{ $file['filename'] }}</h2>
                    <p class="admin-media-card__meta">
                        {{ $file['sourceLabel'] }} ·
                        {{ number_format($file['size'] / 1024, 1, ',', ' ') }} KB
                    </p>

                    <input
                        type="text"
                        class="admin-form__input admin-media-card__url"
                        value="{{ $file['url'] }}"
                        aria-label="Viešas failo URL"
                        readonly
                    >

                    <div class="admin-media-card__actions">
                        <button
                            type="button"
                            class="admin-button admin-button--secondary admin-media-card__copy"
                            data-copy-url="{{ $file['url'] }}"
                            data-copy-default-label="Kopijuoti URL"
                            data-copy-success-label="URL nukopijuotas"
                        >
                            Kopijuoti URL
                        </button>

                        @if ($file['canDelete'])
                            <button
                                type="button"
                                class="admin-button admin-button--danger"
                                data-delete-confirmation-trigger
                                data-delete-form-id="delete-media-file-{{ md5($file['filename']) }}"
                                data-delete-confirmation-title="Ištrinti failą"
                                data-delete-confirmation-message="Ar tikrai norite ištrinti šį failą?"
                                data-delete-confirmation-submit-label="Ištrinti"
                            >
                                Ištrinti
                            </button>

                            <form
                                id="delete-media-file-{{ md5($file['filename']) }}"
                                method="POST"
                                action="{{ route('admin.media-library.destroy', $file['filename']) }}"
                                class="admin-hidden-form"
                            >
                                @csrf
                                @method('DELETE')
                            </form>
                        @else
                            <button
                                type="button"
                                class="admin-button admin-button--secondary admin-media-card__protected"
                                title="{{ $file['deleteDisabledReason'] }}"
                                disabled
                            >
                                Apsaugota
                            </button>
                        @endif
                    </div>

                    @if (! $file['canDelete'] && $file['deleteDisabledReason'])
                        <p class="admin-media-card__notice">{{ $file['deleteDisabledReason'] }}</p>
                    @endif
                </div>
            </article>
        @empty
            <p class="admin-media-library__empty">Failų dar nėra.</p>
        @endforelse
    </section>

    <x-admin-delete-confirmation-modal title="Ištrinti failą" message="Ar tikrai norite ištrinti šį failą?" />
</div>
@endsection
