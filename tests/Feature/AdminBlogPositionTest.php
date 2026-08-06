<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBlogPositionTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_swap_blog_post_positions(): void
    {
        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        $firstPost = $this->createBlogPost('Post A', 25);
        $secondPost = $this->createBlogPost('Post B', 14);

        $response = $this
            ->actingAs($administrator)
            ->patch(route('admin.blog.position.update', $firstPost->id), [
                'position' => 14,
            ]);

        $response
            ->assertRedirect(route('admin.blog'))
            ->assertSessionHas('success', 'Straipsnių eiliškumas sėkmingai atnaujintas.');

        $this->assertDatabaseHas('portfolio_posts', [
            'id' => $firstPost->id,
            'position' => 14,
        ]);

        $this->assertDatabaseHas('portfolio_posts', [
            'id' => $secondPost->id,
            'position' => 25,
        ]);
    }

    public function test_administrator_can_set_unused_blog_post_position(): void
    {
        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        $blogPost = $this->createBlogPost('Post A', 25);

        $response = $this
            ->actingAs($administrator)
            ->patch(route('admin.blog.position.update', $blogPost->id), [
                'position' => 30,
            ]);

        $response
            ->assertRedirect(route('admin.blog'))
            ->assertSessionHas('success', 'Straipsnių eiliškumas sėkmingai atnaujintas.');

        $this->assertDatabaseHas('portfolio_posts', [
            'id' => $blogPost->id,
            'position' => 30,
        ]);
    }

    public function test_position_update_redirects_back_to_current_blog_page_query(): void
    {
        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        $blogPost = $this->createBlogPost('Post A', 25);
        $redirectTo = route('admin.blog', [
            'page' => 2,
            'search' => 'blog',
            'sort' => 'position',
        ]);

        $response = $this
            ->actingAs($administrator)
            ->patch(route('admin.blog.position.update', $blogPost->id), [
                'position' => 30,
                'redirect_to' => $redirectTo,
            ]);

        $response
            ->assertRedirect($redirectTo)
            ->assertSessionHas('success', 'Straipsnių eiliškumas sėkmingai atnaujintas.');

        $this->assertDatabaseHas('portfolio_posts', [
            'id' => $blogPost->id,
            'position' => 30,
        ]);
    }

    private function createBlogPost(string $title, int $position): BlogPost
    {
        return BlogPost::create([
            'title' => $title,
            'category' => 'Sportas',
            'description' => 'Blog description.',
            'thumbnail' => 'media-library/example.jpg',
            'position' => $position,
        ]);
    }
}


