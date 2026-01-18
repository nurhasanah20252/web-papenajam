<?php

namespace App\Services\JoomlaMigration;

use Illuminate\Support\Str;

class JoomlaDataCleaner
{
    /**
     * Common Joomla to Laravel URL mappings.
     */
    protected array $urlMappings = [];

    /**
     * Joomla URL patterns to replace.
     */
    protected array $urlPatterns = [
        '#/index\.php/(\w+)#' => '/$1',
        '#/component/content/article/(\d+)-([^\?]+)#' => '/news/$2',
        '#/component/weblinks/category/(\d+)-([^\?]+)#' => '/links/$2',
        '#/component/search\?searchword=(.+)#' => '/search?q=$1',
        '#/index\.php\?option=com_content&view=article&id=(\d+)#' => '/news/$1',
        '#/index\.php\?option=com_content&view=category&id=(\d+)#' => '/category/$1',
        '#/index\.php\?option=com_wrapper&view=wrapper&id=(\d+)#' => '/wrapper/$1',
    ];

    /**
     * Clean HTML content from Joomla.
     */
    public function cleanContent(string $content): array
    {
        // Remove Joomla-specific tags
        $content = preg_replace('/<jdoc:[^>]+>/', '', $content);
        $content = preg_replace('/{loadposition[^}]*}/', '', $content);
        $content = preg_replace('/{loadmodule[^}]*}/', '', $content);
        $content = preg_replace('/{widget[^}]*}/', '', $content);
        $content = preg_replace('/{youtube}[^}]+{\/youtube}/', '', $content);

        // Remove comments
        $content = preg_replace('/<!--[^>]*-->/', '', $content);

        // Fix relative URLs in images and links
        $content = $this->fixRelativeUrls($content);

        // Clean up excessive whitespace
        $content = preg_replace('/\s+/', ' ', $content);
        $content = preg_replace('/> </', '><', $content);
        $content = trim($content);

        // Extract metadata
        $meta = $this->extractMetadata($content);

        return [
            'content' => $content,
            'meta' => $meta,
        ];
    }

    /**
     * Convert Joomla links to Laravel routes.
     */
    public function convertLinks(string $content): string
    {
        foreach ($this->urlPatterns as $pattern => $replacement) {
            $content = preg_replace($pattern, $replacement, $content);
        }

        // Apply custom mappings
        foreach ($this->urlMappings as $oldUrl => $newUrl) {
            $content = str_replace($oldUrl, $newUrl, $content);
        }

        return $content;
    }

    /**
     * Process images from Joomla content.
     */
    public function processImages(array $images): array
    {
        return collect($images)->map(function ($image) {
            return [
                'src' => $this->processImagePath($image['src'] ?? ''),
                'alt' => $image['alt'] ?? '',
                'title' => $image['title'] ?? '',
            ];
        })->toArray();
    }

    /**
     * Process a single image path.
     */
    public function processImagePath(string $path): string
    {
        // Remove Joomla image directory prefix
        $path = preg_replace('#^/images/#', 'storage/', $path);
        $path = preg_replace('#^/images/#', 'storage/', $path);

        return $path;
    }

    /**
     * Add URL mapping.
     */
    public function addUrlMapping(string $oldUrl, string $newUrl): self
    {
        $this->urlMappings[$oldUrl] = $newUrl;

        return $this;
    }

    /**
     * Add URL pattern.
     */
    public function addUrlPattern(string $pattern, string $replacement): self
    {
        $this->urlPatterns[$pattern] = $replacement;

        return $this;
    }

    /**
     * Clean article intro text.
     */
    public function cleanIntrotext(string $introtext): string
    {
        // Strip tags but keep basic formatting
        $introtext = strip_tags($introtext, '<p><br><strong><em><ul><ol><li>');
        $introtext = trim($introtext);

        return $introtext;
    }

    /**
     * Extract metadata from content.
     */
    protected function extractMetadata(string $content): array
    {
        $meta = [];

        // Extract meta description from first paragraph if not present
        if (preg_match('/<p[^>]*>([^<]{50,200})<\/p>/i', $content, $matches)) {
            $meta['description'] = trim($matches[1]);
        }

        return $meta;
    }

    /**
     * Fix relative URLs in content.
     */
    protected function fixRelativeUrls(string $content): string
    {
        // Fix src attributes
        $content = preg_replace(
            '/(src|href)=["\']\/([^\"\']+)["\']/',
            '$1="/$2"',
            $content
        );

        return $content;
    }

    /**
     * Clean Joomla article metadata.
     */
    public function cleanMetadata(array $metadata): array
    {
        return [
            'created_by' => $metadata['created_by'] ?? null,
            'modified_by' => $metadata['modified_by'] ?? null,
            'hits' => $metadata['hits'] ?? 0,
            'robots' => $metadata['metadata']->robots ?? null,
            'author' => $metadata['metadata']->author ?? null,
            'rights' => $metadata['metadata']->rights ?? null,
        ];
    }

    /**
     * Sanitize filename.
     */
    public function sanitizeFilename(string $filename): string
    {
        // Get extension
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $basename = pathinfo($filename, PATHINFO_FILENAME);

        // Remove special characters
        $basename = preg_replace('/[^a-zA-Z0-9_-]/', '-', $basename);
        $basename = preg_replace('/-+/', '-', $basename);
        $basename = trim($basename, '-');

        // Add extension back
        return $basename.'.'.$extension;
    }

    /**
     * Process Joomla date format.
     */
    public function processDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($date)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }
}
