<?php

declare(strict_types=1);

namespace WikiApp\Lib;

class Session
{
    /**
     * Start the session if it hasn't been started yet.
     * This method should be called at the very beginning of any request that uses sessions.
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Get a session variable.
     *
     * @param string $key The key of the session variable.
     * @param mixed $default The default value to return if the key does not exist.
     * @return mixed The value of the session variable, or the default value.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set a session variable.
     *
     * @param string $key The key of the session variable.
     * @param mixed $value The value to set.
     */
    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Check if a session variable exists.
     *
     * @param string $key The key of the session variable.
     * @return bool True if the variable exists, false otherwise.
     */
    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    /**
     * Remove a session variable.
     *
     * @param string $key The key of the session variable to remove.
     */
    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    /**
     * Set a flash message. Flash messages are session variables that are
     * only available for the next request and are then automatically deleted.
     *
     * @param string $key The key for the flash message.
     * @param mixed $value The message content.
     */
    public static function flash(string $key, mixed $value): void
    {
        self::set('flash_' . $key, $value);
    }

    /**
     * Get and clear a flash message.
     *
     * @param string $key The key for the flash message.
     * @param mixed $default The default value to return if the flash message does not exist.
     * @return mixed The flash message content, or the default value.
     */
    public static function getFlash(string $key, mixed $default = null): mixed
    {
        self::start();
        $flashKey = 'flash_' . $key;
        if (isset($_SESSION[$flashKey])) {
            $value = $_SESSION[$flashKey];
            unset($_SESSION[$flashKey]); // Clear after reading
            return $value;
        }
        return $default;
    }

    /**
     * Destroy the entire session.
     */
    public static function destroy(): void
    {
        self::start();
        session_unset();     // Unset all of the session variables.
        session_destroy();   // Destroy the session.

        // Also clear cookies related to session
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
    }
}
