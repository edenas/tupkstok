<?php

namespace Tests\Unit;

use App\Services\BlogContentSanitizer;
use App\Services\WordPressMigration\WordPressImportSourceReader;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class WordPressImportSourceReaderTest extends TestCase
{
    public function test_it_reads_complete_post_data_without_executing_sql(): void
    {
        $path = storage_path('framework/testing/'.Str::uuid().'.xml');
        File::put($path, <<<'XML'
<?xml version="1.0" encoding="UTF-8"?><rss xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:wp="http://wordpress.org/export/1.2/" xmlns:dc="http://purl.org/dc/elements/1.1/"><channel><item><title>Žalias ąžuolas</title><link>https://example.test/zalios</link><dc:creator>Rūta</dc:creator><content:encoded><![CDATA[<p>Turinys</p>]]></content:encoded><wp:post_id>7</wp:post_id><wp:post_name>žalias-ąžuolas</wp:post_name><wp:status>publish</wp:status><wp:post_type>post</wp:post_type><category>Sveikata</category><wp:postmeta><wp:meta_key>_thumbnail_id</wp:meta_key><wp:meta_value>8</wp:meta_value></wp:postmeta></item></channel></rss>
XML);
        try {
            $post = iterator_to_array(app(WordPressImportSourceReader::class)->posts($path))[0];
        } finally {
            File::delete($path);
        }
        $this->assertSame('Žalias ąžuolas', $post['title']);
        $this->assertSame('Rūta', $post['author']);
        $this->assertSame('8', $post['featured_image_id']);
        $this->assertSame(['Sveikata'], $post['categories']);
    }

    public function test_sanitizer_removes_dangerous_markup_and_preserves_alignment_and_underline(): void
    {
        $html = '<script>alert(1)</script><p><u>Tekstas</u><img class="article-image-left wp-image-7" src="/storage/media-library/a.jpg" onerror="x"></p><a href="javascript:alert(1)">blogai</a><a href="/saugus">gerai</a>';
        $clean = app(BlogContentSanitizer::class)->sanitize($html);
        $this->assertStringNotContainsString('<script', $clean);
        $this->assertStringNotContainsString('javascript:', $clean);
        $this->assertStringNotContainsString('onerror', $clean);
        $this->assertStringContainsString('<u>Tekstas</u>', $clean);
        $this->assertStringContainsString('class="article-image-left"', $clean);
        $this->assertStringContainsString('href="/saugus"', $clean);
    }
}
