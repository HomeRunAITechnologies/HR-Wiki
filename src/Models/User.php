<?php

declare(strict_types=1);

namespace WikiApp\Models;

use PaigeJulianne\PicoORM;

class User extends PicoORM
{
    // Override this constant in child classes to specify a different table name
    const TABLE_OVERRIDE = 'users';

    // Override this constant in child classes to specify which connection to use
    const CONNECTION = 'default';

    /**
     * Finds a user by their username.
     *
     * @param string $username
     * @return User|null
     */
    public static function findByUsername(string $username): ?User
    {
        return static::findOneBy('username', $username);
    }

    /**
     * Finds a user by their email.
     *
     * @param string $email
     * @return User|null
     */
    public static function findByEmail(string $email): ?User
    {
        return static::findOneBy('email', $email);
    }

    /**
     * Hashes a password for storage.
     *
     * @param string $password
     * @return string
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verifies a password against a stored hash.
     *
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
