<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicStorageRouteTest extends TestCase
{
    public function test_it_serves_existing_public_disk_files(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('portfolio-thumbnails/example.jpg', 'image-bytes');

        $response = $this->get('/storage/portfolio-thumbnails/example.jpg');

        $response
            ->assertOk()
            ->assertHeader('content-type', 'image/jpeg');
        $this->assertSame('image-bytes', $response->streamedContent());
    }

    public function test_it_returns_not_found_for_missing_public_disk_files(): void
    {
        Storage::fake('public');

        $this->get('/storage/portfolio-thumbnails/missing.jpg')->assertNotFound();
    }

    public function test_it_rejects_directory_traversal_paths(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('private.txt', 'secret');

        $this->get('/storage/portfolio-thumbnails/../private.txt')->assertNotFound();
    }
}
