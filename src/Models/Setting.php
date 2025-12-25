<?php

declare(strict_types=1);

namespace WikiApp\Models;

use PaigeJulianne\PicoORM;

class Setting extends PicoORM
{
    // Override this constant in child classes to specify a different table name
    const TABLE_OVERRIDE = 'settings';

    // Override this constant in child classes to specify which connection to use
    const CONNECTION = 'default';

    // Settings table uses `setting_key` as primary key, not `id`.
    // We'll manage it via static methods.

    /**
     * Get a setting value by its key.
     *
     * @param string $key
     * @param mixed $default Default value if setting not found.
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = self::_fetch(
            "SELECT setting_value FROM _DB_ WHERE setting_key = ?",
            [$key]
        );

        return $setting['setting_value'] ?? $default;
    }

    /**
     * Set a setting value. Creates or updates the setting.
     *
     * @param string $key
     * @param mixed $value
     * @return bool True on success.
     */
    public static function set(string $key, mixed $value): bool
    {
        // Check if setting exists
        $existing = self::_fetch(
            "SELECT setting_key FROM _DB_ WHERE setting_key = ?",
            [$key]
        );

        if (!empty($existing)) {
            // Update existing
            self::_doQuery(
                "UPDATE _DB_ SET setting_value = ? WHERE setting_key = ?",
                [$value, $key]
            );
        } else {
            // Insert new
            self::_doQuery(
                "INSERT INTO _DB_ (setting_key, setting_value) VALUES (?, ?)",
                [$key, $value]
            );
        }
        return true;
    }
}
