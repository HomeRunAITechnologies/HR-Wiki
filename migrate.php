<?php

require 'vendor/autoload.php';

use PaigeJulianne\PicoORM;

// This is a simple, idempotent migration script.
// It checks for the existence of tables before trying to create them.

try {
    // Load .env file from the root directory
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    // Manually add the database connection using the .env variables
    // The first parameter is the connection name, 'default' in this case.
    PicoORM::addConnection(
        'default',
        "{$_ENV['DB_CONNECTION']}:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_DATABASE']};port={$_ENV['DB_PORT']}",
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD'],
        [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
    );

    echo "Successfully loaded configuration. Attempting to connect..." . PHP_EOL;

    // We need a class context to run queries with PicoORM.
    // This simple class provides the necessary context.
    class DBMigrator extends PicoORM {
        // Specify that this class should use the 'default' connection we just configured.
        const CONNECTION = 'default';

        // A helper method to execute raw SQL queries.
        public static function execute(string $sql) {
            // We provide a dummy table name ('_') because _doQuery requires one,
            // but it won't be used since our CREATE TABLE queries don't use the _DB_ placeholder.
            return self::_doQuery($sql, [], '_');
        }
    }

    // Test the connection by getting the PDO object
    DBMigrator::beginTransaction();
    DBMigrator::rollBack();
    echo "Successfully connected to the database." . PHP_EOL;


    // Table schemas
    $schemas = [
        'users' => "
            CREATE TABLE IF NOT EXISTS `users` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `username` VARCHAR(50) NOT NULL UNIQUE,
                `email` VARCHAR(100) NOT NULL UNIQUE,
                `password_hash` VARCHAR(255) NOT NULL,
                `role` ENUM('admin', 'editor') NOT NULL DEFAULT 'editor',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ",
        'settings' => "
            CREATE TABLE IF NOT EXISTS `settings` (
                `setting_key` VARCHAR(50) PRIMARY KEY,
                `setting_value` TEXT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ",
        'pages' => "
            CREATE TABLE IF NOT EXISTS `pages` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `slug` VARCHAR(255) NOT NULL UNIQUE,
                `title` VARCHAR(255) NOT NULL,
                `content` LONGTEXT NOT NULL,
                `format` ENUM('html', 'markdown') NOT NULL DEFAULT 'html',
                `author_id` INT NOT NULL,
                `visibility` ENUM('public', 'private') NOT NULL DEFAULT 'public',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ",
        'page_versions' => "
            CREATE TABLE IF NOT EXISTS `page_versions` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `page_id` INT NOT NULL,
                `version_number` INT NOT NULL,
                `title` VARCHAR(255) NOT NULL,
                `content` LONGTEXT NOT NULL,
                `format` ENUM('html', 'markdown') NOT NULL DEFAULT 'html',
                `author_id` INT NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`page_id`) REFERENCES `pages`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                UNIQUE KEY (`page_id`, `version_number`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ",
        'categories' => "
            CREATE TABLE IF NOT EXISTS `categories` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `slug` VARCHAR(100) NOT NULL UNIQUE,
                `name` VARCHAR(100) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ",
        'page_categories' => "
            CREATE TABLE IF NOT EXISTS `page_categories` (
                `page_id` INT NOT NULL,
                `category_id` INT NOT NULL,
                PRIMARY KEY (`page_id`, `category_id`),
                FOREIGN KEY (`page_id`) REFERENCES `pages`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        "
    ];

    // Execute schemas
    foreach ($schemas as $table => $sql) {
        echo "Creating table '{$table}'... ";
        DBMigrator::execute($sql);
        echo "Done." . PHP_EOL;
    }

    // --- Schema Updates ---
    echo "\nApplying schema updates..." . PHP_EOL;
    try {
        echo "Adding 'visibility' column to 'pages' table... ";
        DBMigrator::execute("ALTER TABLE `pages` ADD COLUMN `visibility` ENUM('public', 'private') NOT NULL DEFAULT 'public' AFTER `author_id`;");
        echo "Done." . PHP_EOL;
    } catch (PDOException $e) {
        // Ignore "Duplicate column name" error (code 1060), which means the column already exists.
        if ($e->errorInfo[1] == 1060) {
            echo "Column already exists." . PHP_EOL;
        } else {
            throw $e; // Re-throw other errors
        }
    }

    echo "\nMigration complete. All tables are ready." . PHP_EOL;

} catch (\PDOException $e) {
    die("Database Error: " . $e->getMessage() . PHP_EOL . "Please check your .env file and ensure the database server is running and the database '{$_ENV['DB_DATABASE']}' exists." . PHP_EOL);
} catch (\Dotenv\Exception\InvalidPathException $e) {
    die("ERROR: Could not find .env file. Please copy .env.example to .env and fill in your database details." . PHP_EOL);
} catch (Exception $e) {
    die("An error occurred: " . $e->getMessage() . PHP_EOL);
}