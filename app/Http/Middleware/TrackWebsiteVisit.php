<?php

namespace App\Http\Middleware;

use App\Models\WebsiteVisit;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackWebsiteVisit
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Store public page visits after the response is rendered.
     */
    public function terminate(Request $request, Response $response): void
    {
        if (! $this->shouldTrack($request, $response)) {
            return;
        }

        try {
            WebsiteVisit::create([
                'path' => $request->getPathInfo(),
                'title' => $this->extractTitle((string) $response->getContent()),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'visited_at' => now(),
            ]);
        } catch (QueryException $exception) {
            report($exception);
        }
    }

    private function shouldTrack(Request $request, Response $response): bool
    {
        if (! $request->isMethod('GET') || $request->user()) {
            return false;
        }

        if (! $response->isSuccessful() || ! str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
            return false;
        }

        if ($request->is('admin*') || $request->is('login')) {
            return false;
        }

        return ! $this->isAssetPath($request->path());
    }

    private function isAssetPath(string $path): bool
    {
        if (preg_match('/\.(?:css|js|map|jpg|jpeg|png|gif|webp|svg|ico|woff|woff2|ttf|eot|mp4|webm|pdf)$/i', $path)) {
            return true;
        }

        return str_starts_with($path, 'build/')
            || str_starts_with($path, 'images/')
            || str_starts_with($path, 'storage/')
            || in_array($path, ['favicon.ico', 'robots.txt', 'sitemap.xml'], true);
    }

    private function extractTitle(string $html): ?string
    {
        if (! preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches)) {
            return null;
        }

        $title = trim(html_entity_decode(strip_tags($matches[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        return $title !== '' ? $title : null;
    }
}
