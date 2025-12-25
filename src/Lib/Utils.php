<?php

declare(strict_types=1);

namespace WikiApp\Lib;

class Utils
{
    /**
     * Convert a string to a URL-friendly slug.
     *
     * @param string $text
     * @return string
     */
    public static function slugify(string $text): string
    {
        // Replace non-alphanumeric characters with hyphens
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        // Remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        // Trim hyphens from start and end
        $text = trim($text, '-');
        // Convert to lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'empty'; // Fallback for completely empty slugs
        }

        return $text;
    }

    /**
     * Generate a full URL for a given path, respecting the base path from APP_URL.
     *
     * @param string $path The relative path from the app root (e.g., '/login').
     * @return string The full, correct path.
     */
    public static function url(string $path): string
    {
        $basePath = parse_url($_ENV['APP_URL'] ?? '', PHP_URL_PATH);
        if ($basePath === null || $basePath === false) {
            $basePath = '';
        }

        // Ensure the path starts with a slash
        if (empty($path) || $path[0] !== '/') {
            $path = '/' . $path;
        }

        return rtrim($basePath, '/') . $path;
    }
}
