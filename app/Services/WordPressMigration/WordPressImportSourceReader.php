<?php

namespace App\Services\WordPressMigration;

use Generator;
use RuntimeException;
use XMLReader;

class WordPressImportSourceReader
{
    public function posts(string $path): Generator
    {
        $reader = new XMLReader;
        if (! $reader->open($path, null, LIBXML_NONET | LIBXML_COMPACT | LIBXML_PARSEHUGE)) {
            throw new RuntimeException('Nepavyko atidaryti WordPress XML failo.');
        }

        try {
            while ($reader->read()) {
                if ($reader->nodeType !== XMLReader::ELEMENT || $reader->name !== 'item') {
                    continue;
                }
                $xml = @simplexml_load_string($reader->readOuterXml(), null, LIBXML_NONET | LIBXML_PARSEHUGE);
                if (! $xml) {
                    throw new RuntimeException('XML įrašo nepavyko saugiai perskaityti.');
                }
                $wp = $xml->children('wp', true);
                $content = $xml->children('content', true);
                $dc = $xml->children('dc', true);
                $meta = [];
                foreach ($wp->postmeta as $entry) {
                    $meta[(string) $entry->meta_key] = (string) $entry->meta_value;
                }
                $categories = [];
                foreach ($xml->category as $category) {
                    $categories[] = trim((string) $category);
                }

                yield [
                    'id' => (string) $wp->post_id,
                    'type' => (string) $wp->post_type,
                    'status' => (string) $wp->status,
                    'title' => trim((string) $xml->title),
                    'slug' => trim((string) $wp->post_name),
                    'url' => trim((string) $xml->link),
                    'author' => trim((string) $dc->creator),
                    'categories' => array_values(array_filter(array_unique($categories))),
                    'content' => (string) $content->encoded,
                    'featured_image_id' => $meta['_thumbnail_id'] ?? null,
                    'attachment_url' => trim((string) $wp->attachment_url),
                    'meta' => $meta,
                    'comment_count' => count($wp->comment),
                ];
            }
        } finally {
            $reader->close();
        }
    }
}
