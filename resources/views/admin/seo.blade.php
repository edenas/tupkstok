@extends('layouts.admin')

@section('content')
<div class="admin-page">
    <div class="admin-page__header">
        <div>
            <h1 class="admin-page__title">{{ __('messages.admin.seo.title') }}</h1>
        </div>
    </div>

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

    @php
        $globalSetting = $seoSettings->get('global');
        $notSetLabel = __('messages.admin.seo.current.not_set');
        $currentSeoValue = function ($setting, $field, $pageKey) use ($globalSetting, $notSetLabel) {
            $value = trim((string) ($setting?->{$field} ?? ''));

            if ($value !== '') {
                return $value;
            }

            if ($pageKey !== 'global') {
                $fallback = trim((string) ($globalSetting?->{$field} ?? ''));

                if ($fallback !== '') {
                    return $fallback;
                }
            }

            return $notSetLabel;
        };
    @endphp

    <div class="admin-form admin-form--full">
        <section class="admin-seo-card-grid">
            @foreach ($cards as $card)
                @php($pageKey = $card['key'])
                @php($setting = $seoSettings->get($pageKey))
                <form id="seo-card-{{ $pageKey }}" method="POST" action="{{ route('admin.seo.update') }}" class="admin-form-card admin-seo-card">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="page_key" value="{{ $pageKey }}">

                    <header class="admin-form-card__header">
                        <h2>{{ __($card['title_key']) }}</h2>
                        @if ($card['description_key'])
                            <p>{{ __($card['description_key']) }}</p>
                        @endif
                    </header>

                    <div class="admin-form-card__body">
                        <div class="admin-seo-current-box">
                            <dl class="admin-seo-current-list">
                                <div>
                                    <dt>{{ __('messages.admin.seo.current.meta_title') }}</dt>
                                    <dd>{{ $currentSeoValue($setting, 'meta_title_lt', $pageKey) }}</dd>
                                </div>
                                <div>
                                    <dt>{{ __('messages.admin.seo.current.meta_description') }}</dt>
                                    <dd>{{ $currentSeoValue($setting, 'meta_description_lt', $pageKey) }}</dd>
                                </div>
                                <div>
                                    <dt>{{ __('messages.admin.seo.current.keywords') }}</dt>
                                    <dd>{{ $currentSeoValue($setting, 'keywords_lt', $pageKey) }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="admin-form__field">
                            <label for="seo-{{ $pageKey }}-meta-title-lt" class="admin-form__label">{{ __('messages.admin.seo.fields.meta_title_lt') }}</label>
                            <input id="seo-{{ $pageKey }}-meta-title-lt" type="text" name="meta_title_lt" value="{{ old('meta_title_lt', $setting?->meta_title_lt) }}" class="admin-form__input">
                        </div>

                        <div class="admin-form__field">
                            <label for="seo-{{ $pageKey }}-meta-description-lt" class="admin-form__label">{{ __('messages.admin.seo.fields.meta_description_lt') }}</label>
                            <textarea id="seo-{{ $pageKey }}-meta-description-lt" name="meta_description_lt" class="admin-form__input admin-form__textarea admin-form__textarea--short">{{ old('meta_description_lt', $setting?->meta_description_lt) }}</textarea>
                        </div>

                        <div class="admin-form__field">
                            <label for="seo-{{ $pageKey }}-keywords-lt" class="admin-form__label">{{ __('messages.admin.seo.fields.keywords_lt') }}</label>
                            <input id="seo-{{ $pageKey }}-keywords-lt" type="text" name="keywords_lt" value="{{ old('keywords_lt', $setting?->keywords_lt) }}" class="admin-form__input" placeholder="{{ __('messages.admin.seo.keywords_placeholder') }}">
                        </div>
                    </div>

                    <div class="admin-seo-card__actions">
                        <button type="submit" class="admin-button admin-button--primary">
                            {{ __('messages.admin.seo.save_card') }}
                        </button>
                    </div>
                </form>
            @endforeach
        </section>
    </div>
</div>
@endsection
