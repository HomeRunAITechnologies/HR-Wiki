<?php

declare(strict_types=1);

namespace WikiApp\Models;

use PaigeJulianne\PicoORM;

class Theme extends PicoORM
{
    const TABLE_OVERRIDE = 'themes';
    const CONNECTION = 'default';

    /**
     * Get all active themes.
     *
     * @return array<Theme>
     */
    public static function getActiveThemes(): array
    {
        return self::getAllObjects('sort_order', ['is_active' => 1]);
    }

    /**
     * Get theme by slug.
     *
     * @param string $slug
     * @return Theme|null
     */
    public static function findBySlug(string $slug): ?Theme
    {
        return self::findOneBy('slug', $slug);
    }

    /**
     * Get the CSS URL for this theme.
     *
     * @return string
     */
    public function getCssUrl(): string
    {
        if ((string)$this->type === 'custom' && !empty($this->css_content)) {
            // For custom themes with inline CSS, we'll serve via a route
            return \WikiApp\Lib\Utils::url('/theme-css/' . $this->slug);
        }
        return (string)$this->css_url;
    }

    /**
     * Check if this is a dark theme.
     *
     * @return bool
     */
    public function isDark(): bool
    {
        return (bool)$this->is_dark;
    }

    /**
     * Get custom themes only.
     *
     * @return array<Theme>
     */
    public static function getCustomThemes(): array
    {
        return self::getAllObjects('sort_order', ['type' => 'custom', 'is_active' => 1]);
    }

    /**
     * Generate a template CSS file for customization.
     *
     * @return string
     */
    public static function getTemplateCSS(): string
    {
        return <<<'CSS'
/*
 * Custom Theme Template
 *
 * This is a Bootstrap 5 theme template. You can customize the colors,
 * fonts, and other styles below. Upload this file to create a new theme.
 *
 * Theme Name: My Custom Theme
 * Theme Type: light (or dark)
 */

:root {
    /* Primary Colors */
    --bs-primary: #0d6efd;
    --bs-primary-rgb: 13, 110, 253;

    /* Secondary Colors */
    --bs-secondary: #6c757d;
    --bs-secondary-rgb: 108, 117, 125;

    /* Semantic Colors */
    --bs-success: #198754;
    --bs-info: #0dcaf0;
    --bs-warning: #ffc107;
    --bs-danger: #dc3545;

    /* Light/Dark */
    --bs-light: #f8f9fa;
    --bs-dark: #212529;

    /* Body */
    --bs-body-bg: #ffffff;
    --bs-body-color: #212529;

    /* Links */
    --bs-link-color: #0d6efd;
    --bs-link-hover-color: #0a58ca;

    /* Borders */
    --bs-border-color: #dee2e6;
    --bs-border-radius: 0.375rem;

    /* Fonts */
    --bs-font-sans-serif: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    --bs-body-font-family: var(--bs-font-sans-serif);
    --bs-body-font-size: 1rem;
    --bs-body-font-weight: 400;
    --bs-body-line-height: 1.5;
}

/* Navbar Customization */
.navbar {
    /* background-color: var(--bs-primary) !important; */
}

.navbar-brand {
    /* font-weight: 700; */
}

/* Card Customization */
.card {
    /* border-radius: var(--bs-border-radius); */
    /* box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); */
}

/* Button Customization */
.btn-primary {
    /* --bs-btn-bg: var(--bs-primary); */
    /* --bs-btn-border-color: var(--bs-primary); */
}

/* Add your custom styles below */

CSS;
    }
}
