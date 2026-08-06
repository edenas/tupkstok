<?php

namespace Tests\Feature;

use App\Models\BlogPost;
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

        BlogPost::create([
            'title' => 'blog post',
            'category' => 'Sportas',
            'description' => 'Blog description.',
            'thumbnail' => 'media-library/example.jpg',
            'position' => 1,
        ]);

        WebsiteVisit::create([
            'path' => '/blogas',
            'title' => 'Blog\'as',
            'visited_at' => now(),
        ]);

        WebsiteVisit::create([
            'path' => '/blogas',
            'title' => 'Blog\'as',
            'visited_at' => now(),
        ]);

        $response = $this
            ->actingAs($administrator)
            ->get(route('admin.dashboard'));

        $response
            ->assertOk()
            ->assertSee('Sukurti puslapiai')
            ->assertSee('Blog&#039;o straipsniai', false)
            ->assertSee('Šiandienos apsilankymai')
            ->assertSee('Šiandienos statistika')
            ->assertSee('Puslapis')
            ->assertSee('URL')
            ->assertSee('Apsilankymai')
            ->assertSee('Blog&#039;as', false)
            ->assertSee('/blogas');
    }

    public function test_dashboard_popular_pages_only_include_todays_visits(): void
    {
        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        foreach (range(1, 20) as $visit) {
            WebsiteVisit::create([
                'path' => '/historical-popular',
                'title' => 'Historical popular',
                'visited_at' => now()->subDay(),
            ]);
        }

        WebsiteVisit::create([
            'path' => '/today-page',
            'title' => 'Today page',
            'visited_at' => now()->startOfDay(),
        ]);

        WebsiteVisit::create([
            'path' => '/today-page',
            'title' => 'Today page',
            'visited_at' => now()->endOfDay(),
        ]);

        $this
            ->actingAs($administrator)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Šiandienos statistika')
            ->assertSee('/today-page')
            ->assertSee('2')
            ->assertDontSee('/historical-popular');
    }

    public function test_dashboard_todays_statistics_are_paginated(): void
    {
        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        foreach (range(1, 17) as $index) {
            foreach (range(1, $index) as $visit) {
                WebsiteVisit::create([
                    'path' => sprintf('/today-page-%02d', $index),
                    'title' => sprintf('Today page %02d', $index),
                    'visited_at' => now(),
                ]);
            }
        }

        WebsiteVisit::create([
            'path' => '/historical-page',
            'title' => 'Historical page',
            'visited_at' => now()->subDay(),
        ]);

        $this
            ->actingAs($administrator)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Šiandienos statistika')
            ->assertSee('/today-page-17')
            ->assertSee('/today-page-08')
            ->assertDontSee('/today-page-07')
            ->assertDontSee('/historical-page')
            ->assertSee('pages=2');

        $this
            ->actingAs($administrator)
            ->get(route('admin.dashboard', ['pages' => 2]))
            ->assertOk()
            ->assertSee('/today-page-07')
            ->assertSee('/today-page-01')
            ->assertDontSee('/today-page-08')
            ->assertDontSee('/historical-page');
    }

    public function test_dashboard_shows_empty_state_without_visit_data(): void
    {
        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        $response = $this
            ->actingAs($administrator)
            ->get(route('admin.dashboard'));

        $response
            ->assertOk()
            ->assertSee('Šiandien apsilankymų dar neužfiksuota.');
    }

    public function test_dashboard_shows_today_empty_state_when_only_historical_visits_exist(): void
    {
        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        WebsiteVisit::create([
            'path' => '/yesterday',
            'title' => 'Yesterday',
            'visited_at' => now()->subDay(),
        ]);

        $this
            ->actingAs($administrator)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Šiandien apsilankymų dar neužfiksuota.')
            ->assertDontSee('/yesterday');
    }
}


