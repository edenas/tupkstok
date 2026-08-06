<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminMediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_upload_and_view_media_file(): void
    {
        Storage::fake('public');

        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        $response = $this
            ->actingAs($administrator)
            ->post(route('admin.media-library.store'), [
                'file' => UploadedFile::fake()->createWithContent('example.png', base64_decode(
                    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII='
                )),
            ]);

        $response
            ->assertRedirect(route('admin.media-library'))
            ->assertSessionHas('success', 'Failas sėkmingai įkeltas.');

        $files = Storage::disk('public')->files('media-library');

        $this->assertCount(1, $files);
        $this->assertStringEndsWith('.png', $files[0]);

        $this
            ->actingAs($administrator)
            ->get(route('admin.media-library'))
            ->assertOk()
            ->assertSee('Failų saugykla')
            ->assertSee('Kopijuoti URL')
            ->assertSee('/storage/' . $files[0]);
    }

    public function test_administrator_can_delete_media_file(): void
    {
        Storage::fake('public');

        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        Storage::disk('public')->put('media-library/example.webp', 'image-bytes');

        $response = $this
            ->actingAs($administrator)
            ->delete(route('admin.media-library.destroy', 'example.webp'));

        $response
            ->assertRedirect(route('admin.media-library'))
            ->assertSessionHas('success', 'Failas sėkmingai ištrintas.');

        Storage::disk('public')->assertMissing('media-library/example.webp');
    }

    public function test_media_library_shows_protected_logo_files(): void
    {
        Storage::fake('public');

        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        Storage::disk('public')->put('media-library/uploaded.webp', 'image-bytes');
        Storage::disk('public')->put('logo.png', 'image-bytes');
        Storage::disk('public')->put('logo_white.png', 'image-bytes');

        $this
            ->actingAs($administrator)
            ->get(route('admin.media-library'))
            ->assertOk()
            ->assertSee('/storage/media-library/uploaded.webp')
            ->assertSee('/storage/logo.png')
            ->assertSee('/storage/logo_white.png')
            ->assertSee('Apsaugota')
            ->assertSee('Sistemos failas, jo trinti negalima.');
    }

    public function test_blog_thumbnail_in_media_library_is_protected_when_used(): void
    {
        Storage::fake('public');

        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        Storage::disk('public')->put('media-library/used.jpg', 'image-bytes');

        BlogPost::create([
            'title' => 'Existing Blog Post',
            'category' => 'Sportas',
            'description' => 'Existing blog post text.',
            'thumbnail' => 'media-library/used.jpg',
            'position' => 1,
        ]);

        $this
            ->actingAs($administrator)
            ->get(route('admin.media-library'))
            ->assertOk()
            ->assertSee('/storage/media-library/used.jpg')
            ->assertSee('Naudojama Blog straipsnyje.');

        $this
            ->actingAs($administrator)
            ->delete(route('admin.media-library.destroy', 'used.jpg'))
            ->assertNotFound();

        Storage::disk('public')->assertExists('media-library/used.jpg');
    }

    public function test_media_library_rejects_non_image_files(): void
    {
        Storage::fake('public');

        $administrator = User::factory()->create([
            'role' => 'administrator',
        ]);

        $this
            ->actingAs($administrator)
            ->post(route('admin.media-library.store'), [
                'file' => UploadedFile::fake()->create('document.pdf', 128, 'application/pdf'),
            ])
            ->assertSessionHasErrors('file');

        $this->assertCount(0, Storage::disk('public')->files('media-library'));
    }

    public function test_image_embedded_in_blog_content_cannot_be_deleted(): void
    {
        Storage::fake('public');
        $administrator = User::factory()->create(['role' => 'administrator']);
        Storage::disk('public')->put('media-library/article-image.jpg', 'image-bytes');

        BlogPost::create([
            'title' => 'Existing Blog Post',
            'category' => 'Sportas',
            'description' => '<img src="/storage/media-library/article-image.jpg" alt="">',
            'thumbnail' => 'media-library/thumbnail.jpg',
            'position' => 1,
        ]);

        $this->actingAs($administrator)
            ->delete(route('admin.media-library.destroy', 'article-image.jpg'))
            ->assertNotFound();

        Storage::disk('public')->assertExists('media-library/article-image.jpg');
    }

    public function test_editor_image_upload_requires_an_administrator(): void
    {
        Storage::fake('public');

        $png = fn () => UploadedFile::fake()->createWithContent('editor.png', base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII='
        ));

        $this->postJson(route('admin.blog.editor-images.store'), [
            'file' => $png(),
        ])->assertUnauthorized();

        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->postJson(route('admin.blog.editor-images.store'), [
            'file' => $png(),
        ])->assertForbidden();
    }

    public function test_legacy_blog_thumbnail_path_resolves_to_media_library_copy(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('media-library/existing.jpg', 'image-bytes');

        $blogPost = BlogPost::create([
            'title' => 'Existing Blog Post',
            'category' => 'Sportas',
            'description' => 'Existing blog post text.',
            'thumbnail' => 'portfolio-thumbnails/existing.jpg',
            'position' => 1,
        ]);

        $this->assertSame('media-library/existing.jpg', $blogPost->thumbnailPath());
        $this->assertSame('/storage/media-library/existing.jpg', $blogPost->thumbnailUrl());
    }
}


