<?php

declare(strict_types=1);

namespace WikiApp\Lib;

class View
{
    private static string $viewsPath = __DIR__ . '/../../src/Views/';

    /**
     * Render a view file within the main application layout.
     *
     * @param string $view The view file name (e.g., 'auth/login' maps to src/Views/auth/login.php).
     * @param array $data Data to pass to the view.
     */
    public static function render(string $view, array $data = []): void
    {
        $file = self::$viewsPath . $view . '.php';

        if (!file_exists($file)) {
            // ToDo: Handle view not found error more gracefully.
            die("View file not found: " . htmlspecialchars($file));
        }

        // Extract the data array into individual variables for the view
        extract($data);

        // Start output buffering for the specific view content
        ob_start();
        include $file;
        $viewContent = ob_get_clean();

        // Pass the view content to the layout
        $content = $viewContent;
        $title = $data['title'] ?? 'WikiApp'; // Set a default title

        // Include the layout file, which will use $content and $title
        include self::$viewsPath . 'layout.php';
    }
}
