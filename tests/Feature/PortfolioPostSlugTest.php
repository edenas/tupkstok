<?php

namespace Tests\Feature;

use App\Models\PortfolioPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortfolioPostSlugTest extends TestCase
{
    use RefreshDatabase;

    public function test_portfolio_post_generates_slug_from_lithuanian_title(): void
    {
        $portfolioPost = $this->createPortfolioPost('Žemaitukai - Man pasakyk');

        $this->assertSame('zemaitukai-man-pasakyk', $portfolioPost->slug);
    }

    public function test_duplicate_portfolio_titles_get_incremental_unique_slugs(): void
    {
        $firstPost = $this->createPortfolioPost('Zemaitukai - Man pasakyk');
        $secondPost = $this->createPortfolioPost('Zemaitukai - Man pasakyk');
        $thirdPost = $this->createPortfolioPost('Zemaitukai - Man pasakyk');

        $this->assertSame('zemaitukai-man-pasakyk', $firstPost->slug);
        $this->assertSame('zemaitukai-man-pasakyk-1', $secondPost->slug);
        $this->assertSame('zemaitukai-man-pasakyk-2', $thirdPost->slug);
    }

    public function test_slug_regenerates_when_title_changes(): void
    {
        $this->createPortfolioPost('Naujas projektas');
        $portfolioPost = $this->createPortfolioPost('Senas projektas');

        $portfolioPost->update(['title' => 'Naujas projektas']);

        $this->assertSame('naujas-projektas-1', $portfolioPost->refresh()->slug);
    }

    public function test_public_portfolio_routes_bind_posts_by_slug_in_every_locale(): void
    {
        $portfolioPost = $this->createPortfolioPost('Zemaitukai - Man pasakyk');

        foreach ([
            '/grafika/zemaitukai-man-pasakyk',
            '/en/graphics/zemaitukai-man-pasakyk',
            '/ru/grafika/zemaitukai-man-pasakyk',
        ] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('Zemaitukai - Man pasakyk')
                ->assertSee('href="http://localhost/grafika/zemaitukai-man-pasakyk"', false)
                ->assertSee('href="http://localhost/en/graphics/zemaitukai-man-pasakyk"', false)
                ->assertSee('href="http://localhost/ru/grafika/zemaitukai-man-pasakyk"', false);
        }

        $this->get('/grafika/'.$portfolioPost->id)->assertNotFound();
    }

    private function createPortfolioPost(string $title): PortfolioPost
    {
        return PortfolioPost::create([
            'title' => $title,
            'category' => 'Graphics',
            'description' => 'Portfolio description.',
            'thumbnail' => 'portfolio-thumbnails/example.jpg',
            'position' => ((int) PortfolioPost::max('position')) + 1,
        ]);
    }
}
