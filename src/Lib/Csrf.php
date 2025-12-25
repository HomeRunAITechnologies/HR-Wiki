<?php

declare(strict_types=1);

namespace WikiApp\Lib;

class Csrf
{
    public const TOKEN_NAME = 'csrf_token';

    /**
     * Generate and store a CSRF token in the session.
     *
     * @return string The generated CSRF token.
     */
    public static function generateToken(): string
    {
        Session::start();
        if (!Session::has(self::TOKEN_NAME)) {
            $token = bin2hex(random_bytes(32)); // 32 bytes = 64 hex chars
            Session::set(self::TOKEN_NAME, $token);
        }
        return (string)Session::get(self::TOKEN_NAME);
    }

    /**
     * Get the current CSRF token from the session.
     *
     * @return string|null The CSRF token, or null if not set.
     */
    public static function getToken(): ?string
    {
        Session::start();
        return Session::get(self::TOKEN_NAME);
    }

    /**
     * Validate a submitted CSRF token against the one in the session.
     *
     * @param string $submittedToken The token received from the request.
     * @return bool True if tokens match, false otherwise.
     */
    public static function validateToken(string $submittedToken): bool
    {
        Session::start();
        $sessionToken = Session::get(self::TOKEN_NAME);

        // Invalidate token after use to prevent replay attacks (optional but good practice)
        // Session::remove(self::TOKEN_NAME);

        return $sessionToken !== null && hash_equals($sessionToken, $submittedToken);
    }

    /**
     * Get the HTML for a hidden CSRF input field.
     *
     * @return string
     */
    public static function field(): string
    {
        return '<input type="hidden" name="' . self::TOKEN_NAME . '" value="' . self::generateToken() . '">';
    }
}
