<?php

declare(strict_types=1);

namespace WikiApp\Models;

use PaigeJulianne\PicoORM;

class PageVersion extends PicoORM
{
    // Override this constant in child classes to specify a different table name
    const TABLE_OVERRIDE = 'page_versions';

    // Override this constant in child classes to specify which connection to use
    const CONNECTION = 'default';

    /**
     * Get the next version number for a given page.
     *
     * @param int $pageId
     * @return int
     */
    public static function getNextVersionNumber(int $pageId): int
    {
        $latestVersion = self::_fetch(
            "SELECT MAX(version_number) as max_version FROM _DB_ WHERE page_id = ?",
            [$pageId]
        );

        return (int)($latestVersion['max_version'] ?? 0) + 1;
    }

    /**
     * Get all versions for a page, ordered by version number descending.
     *
     * @param int $pageId
     * @return array<PageVersion>
     */
    public static function getVersionsForPage(int $pageId): array
    {
        $rows = self::_fetchAll(
            "SELECT id FROM _DB_ WHERE page_id = ? ORDER BY version_number DESC",
            [$pageId]
        );

        $versions = [];
        foreach ($rows as $row) {
            $versions[] = new self((int)$row['id']);
        }
        return $versions;
    }

    /**
     * Get a specific version by page ID and version number.
     *
     * @param int $pageId
     * @param int $versionNumber
     * @return PageVersion|null
     */
    public static function getVersion(int $pageId, int $versionNumber): ?PageVersion
    {
        $results = self::getAllObjects('id', [
            'page_id' => $pageId,
            'version_number' => $versionNumber
        ]);
        return $results[0] ?? null;
    }
}
