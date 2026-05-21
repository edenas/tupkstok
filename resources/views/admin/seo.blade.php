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
                <form id="seo-card-{{ $pageKey }}" method="POST" action="{{ route('admin.seo.update') }}" class="admin-form-card admin-seo-card" data-language-tabs>
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="page_key" value="{{ $pageKey }}">

                    <header class="admin-form-card__header">
                        <h2>{{ __($card['title_key']) }}</h2>
                        @if ($card['description_key'])
                            <p>{{ __($card['description_key']) }}</p>
                        @endif
                    </header>

                    <div class="admin-form-tabs" role="tablist" aria-label="{{ __('messages.admin.seo.language_tabs') }}">
                        <button type="button" class="admin-form-tabs__button admin-form-tabs__button--active" id="seo-{{ $pageKey }}-tab-lt" role="tab" aria-selected="true" aria-controls="seo-{{ $pageKey }}-panel-lt" data-language-tab="lt">
                            LT
                        </button>
                        <button type="button" class="admin-form-tabs__button" id="seo-{{ $pageKey }}-tab-en" role="tab" aria-selected="false" aria-controls="seo-{{ $pageKey }}-panel-en" data-language-tab="en">
                            EN
                        </button>
                        <button type="button" class="admin-form-tabs__button" id="seo-{{ $pageKey }}-tab-ru" role="tab" aria-selected="false" aria-controls="seo-{{ $pageKey }}-panel-ru" data-language-tab="ru">
                            RU
                        </button>
                    </div>

                    <div class="admin-form-tabs__panel" id="seo-{{ $pageKey }}-panel-lt" role="tabpanel" aria-labelledby="seo-{{ $pageKey }}-tab-lt" data-language-panel="lt">
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
                    </div>

                    <div class="admin-form-tabs__panel admin-form-tabs__panel--hidden" id="seo-{{ $pageKey }}-panel-en" role="tabpanel" aria-labelledby="seo-{{ $pageKey }}-tab-en" data-language-panel="en" hidden>
                        <div class="admin-form-card__body">
                            <div class="admin-seo-current-box">
                                <dl class="admin-seo-current-list">
                                    <div>
                                        <dt>{{ __('messages.admin.seo.current.meta_title') }}</dt>
                                        <dd>{{ $currentSeoValue($setting, 'meta_title_en', $pageKey) }}</dd>
                                    </div>
                                    <div>
                                        <dt>{{ __('messages.admin.seo.current.meta_description') }}</dt>
                                        <dd>{{ $currentSeoValue($setting, 'meta_description_en', $pageKey) }}</dd>
                                    </div>
                                    <div>
                                        <dt>{{ __('messages.admin.seo.current.keywords') }}</dt>
                                        <dd>{{ $currentSeoValue($setting, 'keywords_en', $pageKey) }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <div class="admin-form__field">
                                <label for="seo-{{ $pageKey }}-meta-title-en" class="admin-form__label">{{ __('messages.admin.seo.fields.meta_title_en') }}</label>
                                <input id="seo-{{ $pageKey }}-meta-title-en" type="text" name="meta_title_en" value="{{ old('meta_title_en', $setting?->meta_title_en) }}" class="admin-form__input">
                            </div>

                            <div class="admin-form__field">
                                <label for="seo-{{ $pageKey }}-meta-description-en" class="admin-form__label">{{ __('messages.admin.seo.fields.meta_description_en') }}</label>
                                <textarea id="seo-{{ $pageKey }}-meta-description-en" name="meta_description_en" class="admin-form__input admin-form__textarea admin-form__textarea--short">{{ old('meta_description_en', $setting?->meta_description_en) }}</textarea>
                            </div>

                            <div class="admin-form__field">
                                <label for="seo-{{ $pageKey }}-keywords-en" class="admin-form__label">{{ __('messages.admin.seo.fields.keywords_en') }}</label>
                                <input id="seo-{{ $pageKey }}-keywords-en" type="text" name="keywords_en" value="{{ old('keywords_en', $setting?->keywords_en) }}" class="admin-form__input" placeholder="{{ __('messages.admin.seo.keywords_placeholder') }}">
                            </div>
                        </div>
                    </div>

                    <div class="admin-form-tabs__panel admin-form-tabs__panel--hidden" id="seo-{{ $pageKey }}-panel-ru" role="tabpanel" aria-labelledby="seo-{{ $pageKey }}-tab-ru" data-language-panel="ru" hidden>
                        <div class="admin-form-card__body">
                            <div class="admin-seo-current-box">
                                <dl class="admin-seo-current-list">
                                    <div>
                                        <dt>{{ __('messages.admin.seo.current.meta_title') }}</dt>
                                        <dd>{{ $currentSeoValue($setting, 'meta_title_ru', $pageKey) }}</dd>
                                    </div>
                                    <div>
                                        <dt>{{ __('messages.admin.seo.current.meta_description') }}</dt>
                                        <dd>{{ $currentSeoValue($setting, 'meta_description_ru', $pageKey) }}</dd>
                                    </div>
                                    <div>
                                        <dt>{{ __('messages.admin.seo.current.keywords') }}</dt>
                                        <dd>{{ $currentSeoValue($setting, 'keywords_ru', $pageKey) }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <div class="admin-form__field">
                                <label for="seo-{{ $pageKey }}-meta-title-ru" class="admin-form__label">{{ __('messages.admin.seo.fields.meta_title_ru') }}</label>
                                <input id="seo-{{ $pageKey }}-meta-title-ru" type="text" name="meta_title_ru" value="{{ old('meta_title_ru', $setting?->meta_title_ru) }}" class="admin-form__input">
                            </div>

                            <div class="admin-form__field">
                                <label for="seo-{{ $pageKey }}-meta-description-ru" class="admin-form__label">{{ __('messages.admin.seo.fields.meta_description_ru') }}</label>
                                <textarea id="seo-{{ $pageKey }}-meta-description-ru" name="meta_description_ru" class="admin-form__input admin-form__textarea admin-form__textarea--short">{{ old('meta_description_ru', $setting?->meta_description_ru) }}</textarea>
                            </div>

                            <div class="admin-form__field">
                                <label for="seo-{{ $pageKey }}-keywords-ru" class="admin-form__label">{{ __('messages.admin.seo.fields.keywords_ru') }}</label>
                                <input id="seo-{{ $pageKey }}-keywords-ru" type="text" name="keywords_ru" value="{{ old('keywords_ru', $setting?->keywords_ru) }}" class="admin-form__input" placeholder="{{ __('messages.admin.seo.keywords_placeholder') }}">
                            </div>
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
