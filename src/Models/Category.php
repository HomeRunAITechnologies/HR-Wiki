<?php

declare(strict_types=1);

namespace WikiApp\Models;

use PaigeJulianne\PicoORM;

class Category extends PicoORM
{
    // Override this constant in child classes to specify a different table name
    const TABLE_OVERRIDE = 'categories';

    // Override this constant in child classes to specify which connection to use
    const CONNECTION = 'default';

    /**
     * Finds a category by its slug.
     *
     * @param string $slug
     * @return Category|null
     */
    public static function findBySlug(string $slug): ?Category
    {
        return static::findOneBy('slug', $slug);
    }

    /**
     * Finds a category by its name.
     *
     * @param string $name
     * @return Category|null
     */
    public static function findByName(string $name): ?Category
    {
        return static::findOneBy('name', $name);
    }
}
