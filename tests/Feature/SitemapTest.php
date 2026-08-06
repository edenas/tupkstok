<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_contains_lithuanian_public_pages(): void
    {
        $content = $this->get('/sitemap.xml')
            ->assertOk()
            ->getContent();

        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $content);
        $this->assertStringContainsString('<urlset', $content);
        $this->assertStringContainsString('<loc>http://127.0.0.1:8000/</loc>', $content);
        $this->assertStringContainsString('<loc>http://127.0.0.1:8000/blogas</loc>', $content);
        $this->assertStringContainsString('<loc>http://127.0.0.1:8000/kontaktai</loc>', $content);
        $this->assertStringNotContainsString('hreflang=', $content);
        $this->assertStringNotContainsString('/admin', $content);
        $this->assertNotFalse(simplexml_load_string($content));
    }

    public function test_sitemap_contains_blog_detail_pages_with_post_lastmod(): void
    {
        $blogPost = BlogPost::create([
            'title' => 'LT title',
            'category' => 'Sportas',
            'description' => 'Description',
            'position' => 1,
        ]);

        DB::table('portfolio_posts')
            ->where('id', $blogPost->id)
            ->update([
                'created_at' => '2026-05-10 12:00:00',
                'updated_at' => '2026-05-20 12:00:00',
            ]);

        $content = $this->get('/sitemap.xml')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('<loc>http://127.0.0.1:8000/blogas/'.$blogPost->slug.'</loc>', $content);
        $this->assertStringNotContainsString('/blogas/'.$blogPost->id, $content);
        $this->assertStringContainsString('<lastmod>2026-05-20</lastmod>', $content);
    }
}
