<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WebsiteVisit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStatisticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_view_statistics_page(): void
    {
        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        WebsiteVisit::create([
            'path' => '/blogas',
            'title' => 'blog',
            'visited_at' => now(),
        ]);

        $response = $this
            ->actingAs($administrator)
            ->get(route('admin.statistics'));

        $response
            ->assertOk()
            ->assertSee('Statistika')
            ->assertSee('Iš viso apsilankymų')
            ->assertSee('Visi puslapių apsilankymai')
            ->assertSee('/blogas');
    }

    public function test_administrator_can_paginate_all_tracked_pages(): void
    {
        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        foreach (range(1, 25) as $index) {
            foreach (range(1, $index) as $visit) {
                WebsiteVisit::create([
                    'path' => sprintf('/page-%02d', $index),
                    'title' => sprintf('Page %02d', $index),
                    'visited_at' => now(),
                ]);
            }
        }

        $this
            ->actingAs($administrator)
            ->get(route('admin.statistics'))
            ->assertOk()
            ->assertSee('Visi puslapių apsilankymai')
            ->assertSee('/page-25')
            ->assertSee('/page-16')
            ->assertDontSee('/page-15');

        $this
            ->actingAs($administrator)
            ->get(route('admin.statistics', ['pages' => 2]))
            ->assertOk()
            ->assertSee('Visi puslapių apsilankymai')
            ->assertSee('/page-15')
            ->assertSee('/page-06')
            ->assertDontSee('/page-16')
            ->assertDontSee('/page-05');
    }

    public function test_administrator_can_view_lithuanian_statistics_page_texts(): void
    {
        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        $response = $this
            ->actingAs($administrator)
            ->get(route('admin.statistics'));

        $response
            ->assertOk()
            ->assertSee('Statistika')
            ->assertSee('Iš viso apsilankymų')
            ->assertSee('Apsilankymai šiais metais')
            ->assertSee('Apsilankymai šį mėnesį')
            ->assertSee('Apsilankymai šią savaitę')
            ->assertSee('Apsilankymai šiandien')
            ->assertSee('Visi puslapių apsilankymai')
            ->assertSee('Puslapis')
            ->assertSee('Apsilankymai')
            ->assertSee('Apsilankymų dar nėra.');
    }

    public function test_public_frontend_page_visits_are_tracked(): void
    {
        $this->get('/')->assertOk();

        $this->assertDatabaseHas('website_visits', [
            'path' => '/',
            'title' => 'Tupk Stok',
        ]);
    }

    public function test_admin_page_visits_are_not_tracked(): void
    {
        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        $this
            ->actingAs($administrator)
            ->get(route('admin.statistics'))
            ->assertOk();

        $this->assertDatabaseCount('website_visits', 0);
    }
}


