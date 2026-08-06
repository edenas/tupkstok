<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogPostSlugTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_post_generates_slug_from_lithuanian_title(): void
    {
        $blogPost = $this->createBlogPost('Žemaitukai - Man pasakyk');

        $this->assertSame('zemaitukai-man-pasakyk', $blogPost->slug);
    }

    public function test_duplicate_blog_titles_get_incremental_unique_slugs(): void
    {
        $firstPost = $this->createBlogPost('Zemaitukai - Man pasakyk');
        $secondPost = $this->createBlogPost('Zemaitukai - Man pasakyk');
        $thirdPost = $this->createBlogPost('Zemaitukai - Man pasakyk');

        $this->assertSame('zemaitukai-man-pasakyk', $firstPost->slug);
        $this->assertSame('zemaitukai-man-pasakyk-1', $secondPost->slug);
        $this->assertSame('zemaitukai-man-pasakyk-2', $thirdPost->slug);
    }

    public function test_slug_regenerates_when_title_changes(): void
    {
        $this->createBlogPost('Naujas projektas');
        $blogPost = $this->createBlogPost('Senas projektas');

        $blogPost->update(['title' => 'Naujas projektas']);

        $this->assertSame('naujas-projektas-1', $blogPost->refresh()->slug);
    }

    public function test_public_blog_route_binds_posts_by_slug(): void
    {
        $blogPost = $this->createBlogPost('Zemaitukai - Man pasakyk');

        $this->get('/blogas/zemaitukai-man-pasakyk')
            ->assertOk()
            ->assertSee('Zemaitukai - Man pasakyk');

        $this->get('/blogas/'.$blogPost->id)->assertNotFound();
    }

    public function test_legacy_blog_urls_redirect_to_lithuanian_blog_routes(): void
    {
        $blogPost = $this->createBlogPost('Zemaitukai - Man pasakyk');

        $this->get('/grafika/'.$blogPost->slug)->assertRedirect('/blogas/'.$blogPost->slug);
        $this->get('/en/graphics/'.$blogPost->slug)->assertRedirect('/blogas/'.$blogPost->slug);
        $this->get('/ru/grafika/'.$blogPost->slug)->assertRedirect('/blogas/'.$blogPost->slug);
    }

    public function test_youtube_embed_accepts_supported_hosts_and_rejects_lookalikes(): void
    {
        $blogPost = $this->createBlogPost('Video straipsnis');

        $blogPost->youtube_url = 'https://www.youtube.com/watch?v=abc_123-def';
        $this->assertSame('https://www.youtube.com/embed/abc_123-def', $blogPost->youtubeEmbedUrl());

        $blogPost->youtube_url = 'https://youtube.com.evil.example/watch?v=abc123';
        $this->assertSame('', $blogPost->youtubeEmbedUrl());

        $blogPost->youtube_url = null;
        $this->assertSame('', $blogPost->youtubeEmbedUrl());
    }

    private function createBlogPost(string $title): BlogPost
    {
        return BlogPost::create([
            'title' => $title,
            'category' => 'Sportas',
            'description' => 'Blog description.',
            'thumbnail' => 'media-library/example.jpg',
            'position' => ((int) BlogPost::max('position')) + 1,
        ]);
    }
}


