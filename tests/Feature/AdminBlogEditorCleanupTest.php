<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminBlogEditorCleanupTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_editor_no_longer_shows_duplicate_description_fields(): void
    {
        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        $this
            ->actingAs($administrator)
            ->get(route('admin.blog.create'))
            ->assertOk()
            ->assertSee('Pavadinimas')
            ->assertSee('Miniatiūros nuotrauka')
            ->assertSee('Straipsnio turinys')
            ->assertDontSee('Trumpas aprašymas')
            ->assertDontSee('Turinio antraštė')
            ->assertDontSee('Trumpas įrašas');
    }

    public function test_public_blog_listing_uses_description_as_excerpt(): void
    {
        $blogPost = BlogPost::create([
            'title' => 'Straipsnio pavadinimas',
            'category' => 'Sportas',
            'description' => 'Pilnas straipsnio aprašymas naudojamas kaip kortelės ištrauka.',
            'thumbnail' => 'media-library/example.jpg',
            'position' => 1,
        ]);

        DB::table('portfolio_posts')
            ->where('id', $blogPost->id)
            ->update(['short_description' => 'Senas trumpas aprašymas']);

        $this
            ->get(route('blog'))
            ->assertOk()
            ->assertSee('Pilnas straipsnio aprašymas naudojamas kaip kortelės ištrauka.')
            ->assertDontSee('Senas trumpas aprašymas');
    }
}


