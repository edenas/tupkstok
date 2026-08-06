<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\User;
use App\Services\BlogThumbnailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminBlogSaveRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_is_redirected_to_edit_page_after_creating_blog_post(): void
    {
        Storage::fake('public');
        $this->withoutThumbnailProcessing();

        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        $response = $this
            ->actingAs($administrator)
            ->post(route('admin.blog.store'), [
                'title' => 'New Blog Post',
                'category' => 'Sportas',
                'description' => 'Full blog post text.',
                'thumbnail' => $this->fakeImageUpload(),
            ]);

        $blogPost = BlogPost::query()->where('title', 'New Blog Post')->firstOrFail();

        $response
            ->assertRedirect(route('admin.blog.edit', $blogPost->id))
            ->assertSessionHas('success', 'Straipsnis sėkmingai sukurtas.');
    }

    public function test_administrator_can_return_to_list_after_creating_blog_post(): void
    {
        Storage::fake('public');
        $this->withoutThumbnailProcessing();

        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        $response = $this
            ->actingAs($administrator)
            ->post(route('admin.blog.store'), [
                'title' => 'New Blog Post',
                'category' => 'Sportas',
                'description' => 'Full blog post text.',
                'thumbnail' => $this->fakeImageUpload(),
                'save_action' => 'return',
            ]);

        $response
            ->assertRedirect(route('admin.blog'))
            ->assertSessionHas('success', 'Straipsnis sėkmingai sukurtas.');
    }

    public function test_administrator_stays_on_edit_page_after_updating_blog_post(): void
    {
        Storage::fake('public');

        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        $blogPost = BlogPost::create([
            'title' => 'Existing Blog Post',
            'category' => 'Sportas',
            'description' => 'Existing blog post text.',
            'thumbnail' => 'media-library/existing.jpg',
            'position' => 1,
        ]);

        $response = $this
            ->actingAs($administrator)
            ->put(route('admin.blog.update', $blogPost->id), [
                'title' => 'Updated Blog Post',
                'category' => 'Sportas',
                'description' => 'Updated blog post text.',
            ]);

        $response
            ->assertRedirect(route('admin.blog.edit', $blogPost->id))
            ->assertSessionHas('success', 'Straipsnis sėkmingai atnaujintas.');

        $this->assertDatabaseHas('portfolio_posts', [
            'id' => $blogPost->id,
            'title' => 'Updated Blog Post',
        ]);
    }

    public function test_administrator_can_return_to_list_after_updating_blog_post(): void
    {
        Storage::fake('public');

        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        $blogPost = BlogPost::create([
            'title' => 'Existing Blog Post',
            'category' => 'Sportas',
            'description' => 'Existing blog post text.',
            'thumbnail' => 'media-library/existing.jpg',
            'position' => 1,
        ]);

        $response = $this
            ->actingAs($administrator)
            ->put(route('admin.blog.update', $blogPost->id), [
                'title' => 'Updated Blog Post',
                'category' => 'Sportas',
                'description' => 'Updated blog post text.',
                'save_action' => 'return',
            ]);

        $response
            ->assertRedirect(route('admin.blog'))
            ->assertSessionHas('success', 'Straipsnis sėkmingai atnaujintas.');

        $this->assertDatabaseHas('portfolio_posts', [
            'id' => $blogPost->id,
            'title' => 'Updated Blog Post',
        ]);
    }

    private function withoutThumbnailProcessing(): void
    {
        $this->app->instance(BlogThumbnailService::class, new class extends BlogThumbnailService {
            public function store(UploadedFile $file): string
            {
                return 'media-library/thumbnail.webp';
            }
        });
    }

    private function fakeImageUpload(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'thumbnail');

        file_put_contents(
            $path,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=')
        );

        return new UploadedFile($path, 'thumbnail.png', 'image/png', null, true);
    }
}


