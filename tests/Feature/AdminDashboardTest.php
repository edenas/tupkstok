<?php

namespace Tests\Feature;

use App\Models\PortfolioPost;
use App\Models\User;
use App\Models\WebsiteVisit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_view_dashboard_metrics(): void
    {
        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        PortfolioPost::create([
            'title' => 'Portfolio post',
            'category' => 'Graphics',
            'description' => 'Portfolio description.',
            'thumbnail' => 'portfolio-thumbnails/example.jpg',
            'position' => 1,
        ]);

        WebsiteVisit::create([
            'path' => '/grafika',
            'title' => 'Grafika',
            'visited_at' => now(),
        ]);

        WebsiteVisit::create([
            'path' => '/grafika',
            'title' => 'Grafika',
            'visited_at' => now(),
        ]);

        session(['admin_locale' => 'lt']);

        $response = $this
            ->actingAs($administrator)
            ->get(route('admin.dashboard'));

        $response
            ->assertOk()
            ->assertSee('Sukurti puslapiai')
            ->assertSee('Portfolio įrašai')
            ->assertSee('Šiandienos apsilankymai')
            ->assertSee('10 populiariausių puslapių')
            ->assertSee('Puslapis')
            ->assertSee('URL')
            ->assertSee('Apsilankymai')
            ->assertSee('Grafika')
            ->assertSee('/grafika');
    }

    public function test_dashboard_shows_empty_state_without_visit_data(): void
    {
        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        session(['admin_locale' => 'lt']);

        $response = $this
            ->actingAs($administrator)
            ->get(route('admin.dashboard'));

        $response
            ->assertOk()
            ->assertSee('Apsilankymų dar nėra.');
    }
}
