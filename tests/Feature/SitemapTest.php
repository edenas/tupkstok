<?php

namespace Tests\Feature;

use App\Models\PortfolioPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_contains_multilingual_public_pages_with_hreflang_alternates(): void
    {
        Carbon::setTestNow('2026-05-22 10:00:00');

        $response = $this->get('/sitemap.xml');
        $content = $response->getContent();

        $response->assertOk();
        $this->assertSame('application/xml; charset=UTF-8', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $content);
        $this->assertStringContainsString('<urlset', $content);
        $this->assertStringContainsString('xmlns:xhtml="http://www.w3.org/1999/xhtml"', $content);
        $this->assertStringContainsString("\n    <url>\n", $content);
        $this->assertStringContainsString('<loc>', $content);
        $this->assertStringContainsString('<lastmod>', $content);
        $this->assertStringContainsString('<xhtml:link rel="alternate"', $content);
        $this->assertStringNotContainsString('&lt;urlset', $content);
        $this->assertStringNotContainsString('&lt;url&gt;', $content);
        $this->assertStringNotContainsString('&lt;loc&gt;', $content);
        $this->assertStringNotContainsString('&lt;lastmod&gt;', $content);
        $this->assertStringNotContainsString('&lt;xhtml:link', $content);
        $this->assertNotFalse(simplexml_load_string($content));

        foreach ([
            'http://localhost/',
            'http://localhost/apie-mane',
            'http://localhost/web-sprendimai',
            'http://localhost/mobiliosios-aplikacijos',
            'http://localhost/grafika',
            'http://localhost/kontaktai',
            'http://localhost/en',
            'http://localhost/en/about-me',
            'http://localhost/en/web-solutions',
            'http://localhost/en/mobile-apps',
            'http://localhost/en/graphics',
            'http://localhost/en/contact',
            'http://localhost/ru',
            'http://localhost/ru/apie-mane',
            'http://localhost/ru/web-sprendimai',
            'http://localhost/ru/mobiliosios-aplikacijos',
            'http://localhost/ru/grafika',
            'http://localhost/ru/kontaktai',
        ] as $url) {
            $this->assertStringContainsString('<loc>'.$url.'</loc>', $content);
        }

        foreach (['hreflang="lt"', 'hreflang="en"', 'hreflang="ru"', 'hreflang="x-default"'] as $hreflang) {
            $this->assertStringContainsString($hreflang, $content);
        }

        $this->assertStringNotContainsString('/admin', $content);
        $this->assertStringNotContainsString('/login', $content);
        $this->assertStringNotContainsString('/storage/', $content);
        $this->assertStringNotContainsString('/build/', $content);
    }

    public function test_sitemap_contains_portfolio_detail_pages_with_post_lastmod(): void
    {
        $portfolioPost = PortfolioPost::create([
            'title' => 'LT title',
            'title_en' => 'EN title',
            'title_ru' => 'RU title',
            'category' => 'Category',
            'short_description' => 'Short description',
            'description' => 'Description',
            'position' => 1,
        ]);

        DB::table('portfolio_posts')
            ->where('id', $portfolioPost->id)
            ->update([
                'created_at' => '2026-05-10 12:00:00',
                'updated_at' => '2026-05-20 12:00:00',
            ]);

        $content = $this->get('/sitemap.xml')
            ->assertOk()
            ->getContent();

        foreach ([
            'http://localhost/grafika/'.$portfolioPost->slug,
            'http://localhost/en/graphics/'.$portfolioPost->slug,
            'http://localhost/ru/grafika/'.$portfolioPost->slug,
        ] as $url) {
            $this->assertStringContainsString('<loc>'.$url.'</loc>', $content);
        }

        $this->assertStringNotContainsString('/grafika/'.$portfolioPost->id, $content);

        $this->assertStringContainsString('<lastmod>2026-05-20</lastmod>', $content);
    }
}
