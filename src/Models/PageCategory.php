<?php

declare(strict_types=1);

namespace WikiApp\Models;

use PaigeJulianne\PicoORM;

class PageCategory extends PicoORM
{
    // Override this constant in child classes to specify a different table name
    const TABLE_OVERRIDE = 'page_categories';

    // Override this constant in child classes to specify which connection to use
    const CONNECTION = 'default';

    // Since this is a pivot table, it usually doesn't have a single 'id' column.
    // PicoORM's constructor expects an ID. We can override this by
    // setting an empty ID column or always passing false to the constructor
    // if we treat it as an intermediary record rather than a standalone entity.
    // For now, we'll primarily use static methods for inserts/deletes.

    /**
     * Link a page to a category.
     *
     * @param int $pageId
     * @param int $categoryId
     * @return bool True on success, false if already linked or on error.
     */
    public static function link(int $pageId, int $categoryId): bool
    {
        // Check if link already exists
        $existing = self::_fetch(
            "SELECT * FROM _DB_ WHERE page_id = ? AND category_id = ?",
            [$pageId, $categoryId]
        );

        if (!empty($existing)) {
            return false; // Already linked
        }

        self::_doQuery(
            "INSERT INTO _DB_ (page_id, category_id) VALUES (?, ?)",
            [$pageId, $categoryId]
        );
        return true;
    }

    /**
     * Unlink a page from a category.
     *
     * @param int $pageId
     * @param int $categoryId
     * @return bool True on success.
     */
    public static function unlink(int $pageId, int $categoryId): bool
    {
        self::_doQuery(
            "DELETE FROM _DB_ WHERE page_id = ? AND category_id = ?",
            [$pageId, $categoryId]
        );
        return true;
    }

    /**
     * Get all categories for a given page.
     *
     * @param int $pageId
     * @return array<Category>
     */
    public static function getCategoriesForPage(int $pageId): array
    {
        $categoryIds = self::_fetchAll(
            "SELECT category_id FROM _DB_ WHERE page_id = ?",
            [$pageId]
        );
        $ids = array_column($categoryIds, 'category_id');

        if (empty($ids)) {
            return [];
        }

        // Use IN clause to fetch all categories
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        return Category::getAllObjects('id', [['id', null, 'IN', $ids]], 'AND', true);
    }
}
