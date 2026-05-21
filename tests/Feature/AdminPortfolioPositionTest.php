<?php

namespace Tests\Feature;

use App\Models\PortfolioPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPortfolioPositionTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_swap_portfolio_post_positions(): void
    {
        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        $firstPost = $this->createPortfolioPost('Post A', 25);
        $secondPost = $this->createPortfolioPost('Post B', 14);

        $response = $this
            ->actingAs($administrator)
            ->patch(route('admin.portfolio.position.update', $firstPost), [
                'position' => 14,
            ]);

        $response
            ->assertRedirect(route('admin.portfolio'))
            ->assertSessionHas('success', 'Portfolio order updated successfully.');

        $this->assertDatabaseHas('portfolio_posts', [
            'id' => $firstPost->id,
            'position' => 14,
        ]);

        $this->assertDatabaseHas('portfolio_posts', [
            'id' => $secondPost->id,
            'position' => 25,
        ]);
    }

    public function test_administrator_can_set_unused_portfolio_post_position(): void
    {
        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        $portfolioPost = $this->createPortfolioPost('Post A', 25);

        $response = $this
            ->actingAs($administrator)
            ->patch(route('admin.portfolio.position.update', $portfolioPost), [
                'position' => 30,
            ]);

        $response
            ->assertRedirect(route('admin.portfolio'))
            ->assertSessionHas('success', 'Portfolio order updated successfully.');

        $this->assertDatabaseHas('portfolio_posts', [
            'id' => $portfolioPost->id,
            'position' => 30,
        ]);
    }

    public function test_position_update_redirects_back_to_current_portfolio_page_query(): void
    {
        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        $portfolioPost = $this->createPortfolioPost('Post A', 25);
        $redirectTo = route('admin.portfolio', [
            'page' => 2,
            'search' => 'graphics',
            'sort' => 'position',
        ]);

        $response = $this
            ->actingAs($administrator)
            ->patch(route('admin.portfolio.position.update', $portfolioPost), [
                'position' => 30,
                'redirect_to' => $redirectTo,
            ]);

        $response
            ->assertRedirect($redirectTo)
            ->assertSessionHas('success', 'Portfolio order updated successfully.');

        $this->assertDatabaseHas('portfolio_posts', [
            'id' => $portfolioPost->id,
            'position' => 30,
        ]);
    }

    private function createPortfolioPost(string $title, int $position): PortfolioPost
    {
        return PortfolioPost::create([
            'title' => $title,
            'category' => 'Graphics',
            'description' => 'Portfolio description.',
            'thumbnail' => 'portfolio-thumbnails/example.jpg',
            'position' => $position,
        ]);
    }
}
