<?php

namespace App\Services\WordPressMigration;

use RuntimeException;
use XMLReader;

class WordPressXmlAnalyzer
{
    public function analyze(string $path): array
    {
        $reader = new XMLReader;
        if (! $reader->open($path, null, LIBXML_NONET | LIBXML_COMPACT | LIBXML_PARSEHUGE)) throw new RuntimeException('Nepavyko atidaryti XML failo.');
        $result = ['export_version' => null, 'site_url' => null, 'site_name' => null, 'authors' => [], 'posts' => [], 'pages' => [], 'categories' => [], 'tags' => [], 'attachments' => [], 'featured_image_references' => [], 'inline_image_urls' => [], 'internal_links' => [], 'external_links' => [], 'post_statuses' => [], 'custom_meta_keys' => [], 'comment_count' => 0];
        $slugs = [];
        try {
            while ($reader->read()) {
                if ($reader->nodeType !== XMLReader::ELEMENT) continue;
                $name = $reader->name;
                if ($name === 'wp:wxr_version') $result['export_version'] = $reader->readString();
                elseif ($name === 'title' && $result['site_name'] === null) $result['site_name'] = $reader->readString();
                elseif ($name === 'wp:base_site_url') $result['site_url'] = rtrim($reader->readString(), '/');
                elseif ($name === 'wp:author') $result['authors'][] = $this->element($reader);
                elseif ($name === 'wp:category') $result['categories'][] = $this->element($reader);
                elseif ($name === 'wp:tag') $result['tags'][] = $this->element($reader);
                elseif ($name === 'item') {
                    $item = $this->parseItem($reader, $result['site_url']);
                    $result['post_statuses'][$item['status']] = ($result['post_statuses'][$item['status']] ?? 0) + 1;
                    $result['custom_meta_keys'] = array_merge($result['custom_meta_keys'], $item['meta_keys']);
                    $result['inline_image_urls'] = array_merge($result['inline_image_urls'], $item['images']);
                    $result['internal_links'] = array_merge($result['internal_links'], $item['internal_links']);
                    $result['external_links'] = array_merge($result['external_links'], $item['external_links']);
                    if ($item['type'] === 'attachment') $result['attachments'][] = $item;
                    elseif ($item['type'] === 'post') { $result['posts'][] = $item; $slugs[] = $item['slug']; }
                    elseif ($item['type'] === 'page') $result['pages'][] = $item;
                    $result['comment_count'] += $item['comment_count'];
                    if ($item['featured_image_id']) $result['featured_image_references'][] = $item['featured_image_id'];
                }
            }
        } finally { $reader->close(); }
        $result['custom_meta_keys'] = array_values(array_unique($result['custom_meta_keys']));
        foreach (['inline_image_urls', 'internal_links', 'external_links'] as $key) $result[$key] = array_values(array_unique($result[$key]));
        $counts = array_count_values(array_filter($slugs));
        $result['duplicate_slugs'] = array_keys(array_filter($counts, fn ($count) => $count > 1));
        $result['summary'] = ['post_count' => count($result['posts']), 'page_count' => count($result['pages']), 'comment_count' => $result['comment_count'], 'published_count' => count(array_filter($result['posts'], fn ($p) => $p['status'] === 'publish')), 'draft_count' => count(array_filter($result['posts'], fn ($p) => $p['status'] === 'draft')), 'attachment_count' => count($result['attachments'])];
        return $result;
    }

    private function element(XMLReader $reader): array
    {
        $xml = @simplexml_load_string($reader->readOuterXml());
        return $xml ? json_decode(json_encode($xml), true) : [];
    }

    private function parseItem(XMLReader $reader, ?string $siteUrl): array
    {
        $xml = @simplexml_load_string($reader->readOuterXml(), null, LIBXML_NONET | LIBXML_PARSEHUGE);
        if (! $xml) throw new RuntimeException('XML įrašo nepavyko perskaityti.');
        $wp = $xml->children('wp', true); $content = $xml->children('content', true);
        $body = (string) $content->encoded; preg_match_all('/(?:src|href)=["\']([^"\']+)["\']/i', $body, $matches);
        $images = []; $internal = []; $external = [];
        foreach ($matches[1] ?? [] as $url) {
            if (preg_match('/\.(?:jpe?g|png|gif|webp|avif)(?:\?|$)/i', $url)) $images[] = $url;
            $host = parse_url(html_entity_decode($url), PHP_URL_HOST);
            if ($host && $siteUrl && $host === parse_url($siteUrl, PHP_URL_HOST)) $internal[] = $url; elseif ($host) $external[] = $url;
        }
        $metaKeys = []; $featured = null;
        foreach ($wp->postmeta as $meta) { $key = (string) $meta->meta_key; $metaKeys[] = $key; if ($key === '_thumbnail_id') $featured = (string) $meta->meta_value; }
        return ['id' => (string) $wp->post_id, 'title' => (string) $xml->title, 'slug' => (string) $wp->post_name, 'type' => (string) $wp->post_type, 'status' => (string) $wp->status, 'publication_date' => (string) $wp->post_date, 'attachment_url' => (string) $wp->attachment_url, 'featured_image_id' => $featured, 'comment_count' => count($wp->comment), 'meta_keys' => $metaKeys, 'images' => $images, 'internal_links' => $internal, 'external_links' => $external];
    }
}
