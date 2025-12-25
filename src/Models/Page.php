<?php

declare(strict_types=1);

namespace WikiApp\Models;

use PaigeJulianne\PicoORM;

class Page extends PicoORM
{
    // Override this constant in child classes to specify a different table name
    const TABLE_OVERRIDE = 'pages';

    // Override this constant in child classes to specify which connection to use
    const CONNECTION = 'default';

    /**
     * Finds a page by its slug.
     *
     * @param string $slug
     * @return Page|null
     */
    public static function findBySlug(string $slug): ?Page
    {
        return static::findOneBy('slug', $slug);
    }
}
