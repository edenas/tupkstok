<?php

namespace Tests\Feature;

use App\Models\SeoSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_view_seo_management_page(): void
    {
        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        session(['admin_locale' => 'en']);

        $response = $this
            ->actingAs($administrator)
            ->get(route('admin.seo.edit'));

        $response
            ->assertOk()
            ->assertSee('Global/default SEO')
            ->assertSee('Home')
            ->assertSee('Meta pavadinimas')
            ->assertSee('Meta Title')
            ->assertSee('Meta Title RU')
            ->assertSee('Current Meta Title');
    }

    public function test_seo_page_fills_default_values_when_fields_are_empty(): void
    {
        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        session(['admin_locale' => 'lt']);

        $response = $this
            ->actingAs($administrator)
            ->get(route('admin.seo.edit'));

        $response
            ->assertOk()
            ->assertSee('EPgalerija – WEB, mobiliosios aplikacijos ir dizaino sprendimai')
            ->assertSee('EPgalerija – skaitmeniniai sprendimai, kurie kuria vertę')
            ->assertDontSee('Nenustatyta');

        foreach (SeoSetting::PAGE_KEYS as $pageKey) {
            $this->assertDatabaseHas('seo_settings', [
                'page_key' => $pageKey,
            ]);

            $setting = SeoSetting::where('page_key', $pageKey)->firstOrFail();

            $this->assertNotEmpty($setting->meta_title_lt);
            $this->assertNotEmpty($setting->meta_description_lt);
            $this->assertNotEmpty($setting->keywords_lt);
            $this->assertNotEmpty($setting->meta_title_en);
            $this->assertNotEmpty($setting->meta_description_en);
            $this->assertNotEmpty($setting->keywords_en);
        }
    }

    public function test_default_values_do_not_overwrite_existing_seo_values(): void
    {
        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        SeoSetting::create([
            'page_key' => 'global',
            'meta_title_lt' => 'Manual global title',
            'meta_description_lt' => null,
        ]);

        $this
            ->actingAs($administrator)
            ->get(route('admin.seo.edit'))
            ->assertOk();

        $this->assertDatabaseHas('seo_settings', [
            'page_key' => 'global',
            'meta_title_lt' => 'Manual global title',
            'meta_description_lt' => 'EPgalerija kuria modernias interneto svetaines, mobiliąsias aplikacijas, grafikos dizaino, animacijos ir skaitmeninius sprendimus verslui.',
        ]);
    }

    public function test_administrator_can_update_one_seo_card(): void
    {
        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        session(['admin_locale' => 'en']);

        $response = $this
            ->actingAs($administrator)
            ->put(route('admin.seo.update'), [
                'page_key' => 'global',
                'meta_title_lt' => 'Global LT SEO',
                'meta_description_lt' => 'Global LT description',
                'keywords_lt' => 'globalu, seo',
                'meta_title_en' => 'Global SEO',
                'meta_description_en' => 'Global description',
                'keywords_en' => 'global, seo',
                'meta_title_ru' => 'Global RU SEO',
                'meta_description_ru' => 'Global RU description',
                'keywords_ru' => 'global, ru, seo',
            ]);

        $response
            ->assertRedirect(route('admin.seo.edit').'#seo-card-global')
            ->assertSessionHas('success', 'SEO settings saved.');

        $this->assertDatabaseHas('seo_settings', [
            'page_key' => 'global',
            'meta_title_lt' => 'Global LT SEO',
            'meta_title_en' => 'Global SEO',
            'meta_title_ru' => 'Global RU SEO',
            'meta_description_ru' => 'Global RU description',
            'keywords_ru' => 'global, ru, seo',
        ]);
    }

    public function test_saving_one_seo_card_does_not_overwrite_another_card(): void
    {
        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        SeoSetting::create([
            'page_key' => 'global',
            'meta_title_lt' => 'Existing global title',
            'meta_description_lt' => 'Existing global description',
            'keywords_lt' => 'existing, global',
        ]);

        SeoSetting::create([
            'page_key' => 'home',
            'meta_title_lt' => 'Existing home title',
        ]);

        $this
            ->actingAs($administrator)
            ->put(route('admin.seo.update'), [
                'page_key' => 'home',
                'meta_title_lt' => 'Updated home title',
                'meta_description_lt' => 'Updated home description',
                'keywords_lt' => 'updated, home',
            ])
            ->assertRedirect(route('admin.seo.edit').'#seo-card-home');

        $this->assertDatabaseHas('seo_settings', [
            'page_key' => 'home',
            'meta_title_lt' => 'Updated home title',
        ]);

        $this->assertDatabaseHas('seo_settings', [
            'page_key' => 'global',
            'meta_title_lt' => 'Existing global title',
            'meta_description_lt' => 'Existing global description',
            'keywords_lt' => 'existing, global',
        ]);
    }

    public function test_seo_page_shows_current_values_with_global_fallback(): void
    {
        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        SeoSetting::create([
            'page_key' => 'global',
            'meta_title_lt' => 'Global current title',
            'meta_description_lt' => 'Global current description',
            'keywords_lt' => 'global, current',
        ]);

        SeoSetting::create([
            'page_key' => 'about',
            'meta_title_lt' => 'About current title',
        ]);

        session(['admin_locale' => 'lt']);

        $response = $this
            ->actingAs($administrator)
            ->get(route('admin.seo.edit'));

        $response
            ->assertOk()
            ->assertSee('Dabartinis Meta pavadinimas')
            ->assertSee('About current title')
            ->assertSee('Global current description')
            ->assertDontSee('Nenustatyta');
    }

    public function test_public_lithuanian_page_uses_page_specific_seo_with_global_fallback(): void
    {
        SeoSetting::create([
            'page_key' => 'global',
            'meta_title_lt' => 'Globalus pavadinimas',
            'meta_description_lt' => 'Globalus aprasymas',
            'keywords_lt' => 'globalu, seo',
        ]);

        SeoSetting::create([
            'page_key' => 'home',
            'meta_title_lt' => 'Pradzios pavadinimas',
        ]);

        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('<title>Pradzios pavadinimas</title>', false)
            ->assertSee('<meta name="description" content="Globalus aprasymas">', false)
            ->assertSee('<meta name="keywords" content="globalu, seo">', false);
    }

    public function test_public_english_page_uses_english_global_fallback(): void
    {
        SeoSetting::create([
            'page_key' => 'global',
            'meta_title_en' => 'Global English title',
            'meta_description_en' => 'Global English description',
            'keywords_en' => 'english, seo',
        ]);

        SeoSetting::create([
            'page_key' => 'about',
            'meta_title_lt' => 'About LT SEO',
        ]);

        $response = $this->get('/en/about-me');

        $response
            ->assertOk()
            ->assertSee('<title>Global English title</title>', false)
            ->assertSee('<meta name="description" content="Global English description">', false)
            ->assertSee('<meta name="keywords" content="english, seo">', false);
    }
}
