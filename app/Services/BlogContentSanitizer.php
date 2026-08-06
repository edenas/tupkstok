<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;

class BlogContentSanitizer
{
    /** @var array<string, list<string>> */
    private const ALLOWED_ATTRIBUTES = [
        'a' => ['href', 'title', 'target', 'rel'],
        'figure' => ['class'],
        'img' => ['src', 'alt', 'title', 'width', 'height', 'class'],
    ];

    private const ALLOWED_TAGS = [
        'a',
        'b',
        'blockquote',
        'br',
        'em',
        'figcaption',
        'figure',
        'h1',
        'h2',
        'h3',
        'i',
        'img',
        'li',
        'ol',
        'p',
        'strong',
        'u',
        'ul',
    ];

    private const TEXT_ALIGN_TAGS = [
        'h1',
        'h2',
        'h3',
        'li',
        'p',
    ];

    public function sanitize(?string $html): ?string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return null;
        }

        $source = new DOMDocument();
        $source->encoding = 'UTF-8';

        libxml_use_internal_errors(true);
        $source->loadHTML('<?xml encoding="UTF-8"><div>'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $output = new DOMDocument('1.0', 'UTF-8');
        $wrapper = $output->createElement('div');
        $output->appendChild($wrapper);

        $sourceWrapper = $source->getElementsByTagName('div')->item(0);

        if (! $sourceWrapper) {
            return null;
        }

        foreach ($sourceWrapper->childNodes as $childNode) {
            $this->appendSanitizedNode($childNode, $wrapper, $output);
        }

        $sanitized = $this->innerHtml($wrapper);

        return trim($sanitized) !== '' ? trim($sanitized) : null;
    }

    private function appendSanitizedNode(DOMNode $node, DOMNode $target, DOMDocument $output): void
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            $target->appendChild($output->createTextNode($node->nodeValue ?? ''));

            return;
        }

        if ($node->nodeType !== XML_ELEMENT_NODE || ! $node instanceof DOMElement) {
            return;
        }

        $tagName = strtolower($node->tagName);

        if (! in_array($tagName, self::ALLOWED_TAGS, true)) {
            foreach ($node->childNodes as $childNode) {
                $this->appendSanitizedNode($childNode, $target, $output);
            }

            return;
        }

        $element = $output->createElement($tagName);
        $this->copySafeAttributes($node, $element, $tagName);

        if ($tagName !== 'br' && $tagName !== 'img') {
            foreach ($node->childNodes as $childNode) {
                $this->appendSanitizedNode($childNode, $element, $output);
            }
        }

        $target->appendChild($element);
    }

    private function copySafeAttributes(DOMElement $source, DOMElement $target, string $tagName): void
    {
        foreach (self::ALLOWED_ATTRIBUTES[$tagName] ?? [] as $attributeName) {
            if (! $source->hasAttribute($attributeName)) {
                continue;
            }

            $value = trim($source->getAttribute($attributeName));

            if ($attributeName === 'href' && ! $this->isSafeUrl($value, false)) {
                continue;
            }

            if ($attributeName === 'src' && ! $this->isSafeUrl($value, true)) {
                continue;
            }

            if ($attributeName === 'class') {
                $value = $this->safeClasses($tagName, $value);
            }

            if (in_array($attributeName, ['width', 'height'], true) && ! preg_match('/^\d{1,4}$/', $value)) {
                continue;
            }

            if ($value !== '') {
                $target->setAttribute($attributeName, $value);
            }
        }

        if ($tagName === 'a' && $target->hasAttribute('href')) {
            $target->setAttribute('target', '_blank');
            $target->setAttribute('rel', 'noopener noreferrer');
        }

        if (in_array($tagName, self::TEXT_ALIGN_TAGS, true) && $source->hasAttribute('style')) {
            $textAlign = $this->safeTextAlign($source->getAttribute('style'));

            if ($textAlign) {
                $target->setAttribute('style', 'text-align: '.$textAlign.';');
            }
        }
    }

    private function isSafeUrl(string $url, bool $allowStoragePath): bool
    {
        if ($allowStoragePath && str_starts_with($url, '/storage/')) {
            return true;
        }

        if (str_starts_with($url, '/') && ! str_starts_with(strtolower($url), '/wp-admin')) {
            return true;
        }

        return (bool) preg_match('/^(https?:|mailto:|tel:)/i', $url);
    }

    private function safeClasses(string $tagName, string $classes): string
    {
        $allowedClasses = $tagName === 'figure'
            ? ['image']
            : ['article-image-full', 'article-image-left', 'article-image-center', 'article-image-right'];
        $classes = preg_split('/\s+/', $classes) ?: [];

        return implode(' ', array_values(array_intersect($classes, $allowedClasses)));
    }

    private function safeTextAlign(string $style): ?string
    {
        if (! preg_match('/(?:^|;)\s*text-align\s*:\s*(left|center|right|justify)\s*(?:;|$)/i', $style, $matches)) {
            return null;
        }

        return strtolower($matches[1]);
    }

    private function innerHtml(DOMElement $element): string
    {
        $html = '';

        foreach ($element->childNodes as $childNode) {
            $html .= $element->ownerDocument?->saveHTML($childNode) ?: '';
        }

        return $html;
    }
}
