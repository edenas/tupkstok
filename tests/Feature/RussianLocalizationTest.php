<?php

namespace Tests\Feature;

use App\Models\PortfolioPost;
use App\Models\SeoSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RussianLocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_russian_home_route_uses_russian_locale_and_seo(): void
    {
        SeoSetting::create([
            'page_key' => 'global',
            'meta_title_ru' => 'Русский SEO заголовок',
            'meta_description_ru' => 'Русское SEO описание',
            'keywords_ru' => 'русский, seo',
        ]);

        $response = $this->get('/ru');

        $response
            ->assertOk()
            ->assertSee('lang="ru"', false)
            ->assertSee('<title>Русский SEO заголовок</title>', false)
            ->assertSee('<meta name="description" content="Русское SEO описание">', false)
            ->assertSee('Главная')
            ->assertSee('Обо мне')
            ->assertDontSee('About me')
            ->assertDontSee('Web Solutions')
            ->assertDontSee('Learn more');
    }

    public function test_russian_frontend_pages_do_not_fall_back_to_english_section_text(): void
    {
        $pages = [
            '/ru/apie-mane' => [
                'see' => ['Обо мне', 'Услуги', 'Технологии и инструменты'],
                'dontSee' => ['About me', 'Website Development', 'Professional links', 'Database', 'Responsive Design'],
            ],
            '/ru/web-sprendimai' => [
                'see' => ['Веб-решения', 'Разработка сайтов', 'Завершенные проекты'],
                'dontSee' => ['Web Solutions', 'Website development', 'Completed Projects'],
            ],
            '/ru/mobiliosios-aplikacijos' => [
                'see' => ['Мобильные приложения', 'Умные напоминания', 'Дизайн и пользовательский опыт'],
                'dontSee' => ['Mobile Apps', 'Track Intake', 'Design & UX'],
            ],
            '/ru/grafika' => [
                'see' => ['Графика', 'Портфолио анимации и видео'],
                'dontSee' => ['Graphics', 'Animation & Video Portfolio'],
            ],
            '/ru/kontaktai' => [
                'see' => ['Контакты', 'Отправить сообщение', 'Контактная информация'],
                'dontSee' => ['Send a message', 'Contact information'],
            ],
        ];

        foreach ($pages as $url => $assertions) {
            $response = $this->get($url);

            $response->assertOk();

            foreach ($assertions['see'] as $text) {
                $response->assertSee($text);
            }

            foreach ($assertions['dontSee'] as $text) {
                $response->assertDontSee($text);
            }
        }
    }

    public function test_russian_portfolio_post_uses_russian_fields(): void
    {
        $portfolioPost = PortfolioPost::create([
            'title' => 'LT title',
            'title_ru' => 'RU title',
            'category' => 'LT category',
            'category_ru' => 'RU category',
            'short_description' => 'LT short',
            'short_description_ru' => 'RU short',
            'description' => 'LT description',
            'description_ru' => 'RU description',
            'thumbnail' => 'portfolio-thumbnails/example.jpg',
            'position' => 1,
        ]);

        $response = $this->get('/ru/grafika/'.$portfolioPost->id);

        $response
            ->assertOk()
            ->assertSee('RU title')
            ->assertSee('RU category')
            ->assertSee('RU description');
    }
}
