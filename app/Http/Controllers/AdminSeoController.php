<?php

namespace App\Http\Controllers;

use App\Models\SeoSetting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminSeoController extends Controller
{
    /**
     * Display SEO settings for all managed pages.
     */
    public function edit()
    {
        $this->ensureSeoSettingsExist();

        $seoSettings = SeoSetting::query()
            ->whereIn('page_key', SeoSetting::PAGE_KEYS)
            ->get()
            ->keyBy('page_key');

        return view('admin.seo', [
            'cards' => $this->cards(),
            'seoSettings' => $seoSettings,
        ]);
    }

    /**
     * Save SEO settings for one managed page.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'page_key' => ['required', Rule::in(SeoSetting::PAGE_KEYS)],
            'meta_title_lt' => 'nullable|string|max:255',
            'meta_description_lt' => 'nullable|string|max:1000',
            'keywords_lt' => 'nullable|string|max:1000',
        ]);

        $pageKey = $validated['page_key'];
        unset($validated['page_key']);

        SeoSetting::updateOrCreate(
            ['page_key' => $pageKey],
            $this->normalizeSettingValues($validated)
        );

        return redirect()
            ->to(route('admin.seo.edit').'#seo-card-'.$pageKey)
            ->with('success', __('messages.admin.seo.saved'));
    }

    /**
     * @return array<int, array{key: string, title_key: string, description_key: string|null}>
     */
    private function cards(): array
    {
        return [
            ['key' => 'global', 'title_key' => 'messages.admin.seo.cards.global', 'description_key' => 'messages.admin.seo.card_descriptions.global'],
            ['key' => 'home', 'title_key' => 'messages.admin.seo.cards.home', 'description_key' => null],
            ['key' => 'blog', 'title_key' => 'messages.admin.seo.cards.blog', 'description_key' => null],
            ['key' => 'contact', 'title_key' => 'messages.admin.seo.cards.contact', 'description_key' => null],
        ];
    }

    private function ensureSeoSettingsExist(): void
    {
        $defaults = SeoSetting::defaultValues();

        foreach (SeoSetting::PAGE_KEYS as $pageKey) {
            $seoSetting = SeoSetting::firstOrCreate(['page_key' => $pageKey]);
            $updates = [];

            foreach (($defaults[$pageKey] ?? []) as $field => $value) {
                if (trim((string) $seoSetting->{$field}) === '') {
                    $updates[$field] = $value;
                }
            }

            if ($updates !== []) {
                $seoSetting->update($updates);
            }
        }
    }

    /**
     * @param array<string, string|null> $values
     * @return array<string, string|null>
     */
    private function normalizeSettingValues(array $values): array
    {
        $fields = [
            'meta_title_lt',
            'meta_description_lt',
            'keywords_lt',
        ];

        $normalized = [];

        foreach ($fields as $field) {
            $value = trim((string) ($values[$field] ?? ''));
            $normalized[$field] = $value !== '' ? $value : null;
        }

        return $normalized;
    }
}
