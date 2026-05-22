<?php

namespace App\Http\Controllers;

use App\Models\PortfolioPost;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    private const LOCALES = [
        'lt' => '',
        'en' => 'en.',
        'ru' => 'ru.',
    ];

    private const STATIC_ROUTES = [
        'home',
        'about-me',
        'web-solutions',
        'mobile-apps',
        'graphics',
        'contact',
    ];

    public function __invoke(): Response
    {
        $urls = collect();
        $staticLastmod = now()->toDateString();

        foreach (self::STATIC_ROUTES as $routeName) {
            $urls = $urls->merge($this->localizedEntries($routeName, [], $staticLastmod));
        }

        PortfolioPost::query()
            ->orderBy('position')
            ->orderByDesc('created_at')
            ->get()
            ->each(function (PortfolioPost $portfolioPost) use (&$urls): void {
                $urls = $urls->merge($this->localizedEntries(
                    'graphics.show',
                    ['portfolioPost' => $portfolioPost],
                    $portfolioPost->updated_at?->toDateString() ?? $portfolioPost->created_at?->toDateString() ?? now()->toDateString()
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
     * @param  array<int, array{loc: string, lastmod: string, alternates: array<int, array{hreflang: string, href: string}>}>  $urls
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
        $xml->writeAttribute('xmlns:xhtml', 'http://www.w3.org/1999/xhtml');

        foreach ($urls as $url) {
            $xml->startElement('url');
            $xml->writeElement('loc', $url['loc']);
            $xml->writeElement('lastmod', $url['lastmod']);

            foreach ($url['alternates'] as $alternate) {
                $xml->startElement('xhtml:link');
                $xml->writeAttribute('rel', 'alternate');
                $xml->writeAttribute('hreflang', $alternate['hreflang']);
                $xml->writeAttribute('href', $alternate['href']);
                $xml->endElement();
            }

            $xml->endElement();
        }

        $xml->endElement();
        $xml->endDocument();

        return $xml->outputMemory();
    }

    /**
     * @param  array<string, mixed>  $parameters
     * @return array<int, array{loc: string, lastmod: string, alternates: array<int, array{hreflang: string, href: string}>}>
     */
    private function localizedEntries(string $routeName, array $parameters, string $lastmod): array
    {
        $alternates = collect(self::LOCALES)
            ->map(fn (string $prefix, string $locale) => [
                'hreflang' => $locale,
                'href' => $this->routeUrl($prefix.$routeName, $parameters),
            ])
            ->values()
            ->all();

        $alternates[] = [
            'hreflang' => 'x-default',
            'href' => $this->routeUrl($routeName, $parameters),
        ];

        return collect(self::LOCALES)
            ->map(fn (string $prefix) => [
                'loc' => $this->routeUrl($prefix.$routeName, $parameters),
                'lastmod' => $lastmod,
                'alternates' => $alternates,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    private function routeUrl(string $routeName, array $parameters): string
    {
        $url = route($routeName, $parameters);

        return preg_match('#^https?://[^/]+$#', $url) ? $url.'/' : $url;
    }
}
