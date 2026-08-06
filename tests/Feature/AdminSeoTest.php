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

        $this
            ->actingAs($administrator)
            ->get(route('admin.seo.edit'))
            ->assertOk()
            ->assertSee('SEO')
            ->assertSee('Meta pavadinimas')
            ->assertSee('Dabartinis Meta pavadinimas');
    }

    public function test_seo_page_fills_default_lithuanian_values_when_fields_are_empty(): void
    {
        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        $this
            ->actingAs($administrator)
            ->get(route('admin.seo.edit'))
            ->assertOk()
            ->assertSee('Tupk Stok')
            ->assertDontSee('Nenustatyta');

        foreach (SeoSetting::PAGE_KEYS as $pageKey) {
            $setting = SeoSetting::where('page_key', $pageKey)->firstOrFail();

            $this->assertNotEmpty($setting->meta_title_lt);
            $this->assertNotEmpty($setting->meta_description_lt);
            $this->assertNotEmpty($setting->keywords_lt);
        }
    }

    public function test_administrator_can_update_one_seo_card(): void
    {
        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        $this
            ->actingAs($administrator)
            ->put(route('admin.seo.update'), [
                'page_key' => 'global',
                'meta_title_lt' => 'Global LT SEO',
                'meta_description_lt' => 'Global LT description',
                'keywords_lt' => 'globalu, seo',
            ])
            ->assertRedirect(route('admin.seo.edit').'#seo-card-global')
            ->assertSessionHas('success', 'SEO nustatymai išsaugoti.');

        $this->assertDatabaseHas('seo_settings', [
            'page_key' => 'global',
            'meta_title_lt' => 'Global LT SEO',
            'meta_description_lt' => 'Global LT description',
            'keywords_lt' => 'globalu, seo',
        ]);
    }

    public function test_public_page_uses_page_specific_seo_with_global_fallback(): void
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

        $this->get('/')
            ->assertOk()
            ->assertSee('<title>Pradzios pavadinimas</title>', false)
            ->assertSee('<meta name="description" content="Globalus aprasymas">', false)
            ->assertSee('<meta name="keywords" content="globalu, seo">', false);
    }
}
