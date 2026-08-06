<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    private const STATIC_ROUTES = [
        'home',
        'blog',
        'contact',
    ];

    public function __invoke(): Response
    {
        $urls = collect();
        $staticLastmod = now()->toDateString();

        foreach (self::STATIC_ROUTES as $routeName) {
            $urls->push($this->entry($routeName, [], $staticLastmod));
        }

        BlogPost::query()
            ->orderBy('position')
            ->orderByDesc('created_at')
            ->get()
            ->each(function (BlogPost $blogPost) use ($urls): void {
                $urls->push($this->entry(
                    'blog.show',
                    ['blogPost' => $blogPost],
                    $blogPost->updated_at?->toDateString() ?? $blogPost->created_at?->toDateString() ?? now()->toDateString()
                ));
            });

        $xml = $this->buildXml($urls->all());

        return new Response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'X-Sitemap-Controller' => 'yes',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * @param array<int, array{loc: string, lastmod: string}> $urls
     */
    private function buildXml(array $urls): string
    {
        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->setIndentString('    ');
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('urlset');
        $xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        foreach ($urls as $url) {
            $xml->startElement('url');
            $xml->writeElement('loc', $url['loc']);
            $xml->writeElement('lastmod', $url['lastmod']);
            $xml->endElement();
        }

        $xml->endElement();
        $xml->endDocument();

        return $xml->outputMemory();
    }

    /**
     * @param array<string, mixed> $parameters
     * @return array{loc: string, lastmod: string}
     */
    private function entry(string $routeName, array $parameters, string $lastmod): array
    {
        return [
            'loc' => $this->routeUrl($routeName, $parameters),
            'lastmod' => $lastmod,
        ];
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function routeUrl(string $routeName, array $parameters): string
    {
        $url = route($routeName, $parameters);

        return preg_match('#^https?://[^/]+$#', $url) ? $url.'/' : $url;
    }
}
