<?php

// Front Controller

// Strict typing for better code quality
declare(strict_types=1);

// Autoload Composer dependencies
require_once __DIR__ . '/vendor/autoload.php';

// Start session management
WikiApp\Lib\Session::start();

use WikiApp\Lib\Csrf;
use PaigeJulianne\PicoORM; // Import PicoORM
// --- Configuration Loading ---
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    // Configure the database connection for PicoORM
    PicoORM::addConnection(
        'default',
        "{$_ENV['DB_CONNECTION']}:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_DATABASE']};port={$_ENV['DB_PORT']}",
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD'],
        [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
    );

} catch (\Dotenv\Exception\InvalidPathException $e) {
    // .env file not found, proceed with default settings or environment variables
}

// --- Error Reporting ---
// In development, show all errors. In production, log them.
if (($_ENV['APP_ENV'] ?? 'production') === 'development') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
    // ToDo: Add a real logger (e.g., Monolog)
}

// --- Routing ---
$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    // --- Web Routes (most specific first) ---
    $r->addRoute('GET', '/', 'WikiApp\Controllers\PageController@show');

    // --- Page Management Routes ---
    $r->addRoute('GET', '/create', 'WikiApp\Controllers\PageController@createForm');
    $r->addRoute('GET', '/create/{slug:.+}', 'WikiApp\Controllers\PageController@createForm'); // Pre-fill slug
    $r->addRoute('POST', '/store', 'WikiApp\Controllers\PageController@store');
    $r->addRoute('GET', '/edit/{slug:.+}', 'WikiApp\Controllers\PageController@editForm');
    $r->addRoute('POST', '/update/{slug:.+}', 'WikiApp\Controllers\PageController@update');

    // --- Page History Routes ---
    $r->addRoute('GET', '/history/{slug:.+}', 'WikiApp\Controllers\PageController@history');
    $r->addRoute('GET', '/diff/{slug:.+}/{old:\d+}/{new:\d+}', 'WikiApp\Controllers\PageController@diff');

    // --- Search Routes ---
    $r->addRoute('GET', '/search', 'WikiApp\Controllers\SearchController@index');

    // --- API Routes ---
    $r->addRoute('GET', '/api/pages/suggest', 'WikiApp\Controllers\PageController@suggest');
    $r->addRoute('POST', '/api/upload', 'WikiApp\Controllers\UploadController@upload');
    $r->addRoute('POST', '/api/set-theme', 'WikiApp\Controllers\ThemeController@setTheme');

    // --- Theme Routes ---
    $r->addRoute('GET', '/theme-css/{slug}', 'WikiApp\Controllers\ThemeController@serveCss');
    $r->addRoute('GET', '/admin/themes', 'WikiApp\Controllers\ThemeController@adminIndex');
    $r->addRoute('GET', '/admin/themes/download-template', 'WikiApp\Controllers\ThemeController@downloadTemplate');
    $r->addRoute('POST', '/admin/themes/upload', 'WikiApp\Controllers\ThemeController@uploadTheme');
    $r->addRoute('POST', '/admin/themes/toggle', 'WikiApp\Controllers\ThemeController@toggleTheme');
    $r->addRoute('POST', '/admin/themes/delete', 'WikiApp\Controllers\ThemeController@deleteTheme');
    $r->addRoute('POST', '/admin/themes/set-default', 'WikiApp\Controllers\ThemeController@setDefaultTheme');

    // --- Category Management Routes ---
    $r->addRoute('GET', '/categories', 'WikiApp\Controllers\CategoryController@index');
    $r->addRoute('GET', '/categories/create', 'WikiApp\Controllers\CategoryController@createForm');
    $r->addRoute('POST', '/categories', 'WikiApp\Controllers\CategoryController@store');

    // --- Settings Routes (Admin Only) ---
    $r->addRoute('GET', '/settings', 'WikiApp\Controllers\SettingsController@showSettingsForm');
    $r->addRoute('POST', '/settings', 'WikiApp\Controllers\SettingsController@updateSettings');

    // --- Authentication Routes ---
    $r->addRoute('GET', '/register', 'WikiApp\Controllers\AuthController@showRegisterForm');
    $r->addRoute('POST', '/register', 'WikiApp\Controllers\AuthController@register');
    $r->addRoute('GET', '/login', 'WikiApp\Controllers\AuthController@showLoginForm');
    $r->addRoute('POST', '/login', 'WikiApp\Controllers\AuthController@login');
    $r->addRoute('GET', '/logout', 'WikiApp\Controllers\AuthController@logout');
    $r->addRoute('GET', '/profile', 'WikiApp\Controllers\UserController@showProfileForm');
    $r->addRoute('POST', '/profile/update-password', 'WikiApp\Controllers\UserController@updatePassword');

    // --- Catch-all for pages (must be last GET route) ---
    $r->addRoute('GET', '/{slug:.+}', 'WikiApp\Controllers\PageController@show');
});

// --- Dispatching The Request ---
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// CSRF Protection for POST requests
if ($httpMethod === 'POST') {
    $submittedToken = $_POST[Csrf::TOKEN_NAME] ?? '';
    if (!Csrf::validateToken($submittedToken)) {
        http_response_code(403);
        die('403 Forbidden - Invalid CSRF Token');
    }
}

// Get the base path from APP_URL for subdirectory installations
$baseUrlPath = parse_url($_ENV['APP_URL'], PHP_URL_PATH);
if ($baseUrlPath === null) {
    $baseUrlPath = ''; // Default to empty if no path in APP_URL
}

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

// Remove the base path from the URI
if ($baseUrlPath !== '' && str_starts_with($uri, $baseUrlPath)) {
    $uri = substr($uri, strlen($baseUrlPath));
}

// Ensure URI starts with a '/'
if ($uri === '' || $uri[0] !== '/') {
    $uri = '/' . $uri;
}

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ToDo: Create a proper 404 view
        http_response_code(404);
        echo '404 Not Found';
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ToDo: Create a proper 405 view
        http_response_code(405);
        echo '405 Method Not Allowed';
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        
        // Simple string handler 'Controller@method'
        if (is_string($handler) && strpos($handler, '@') !== false) {
            list($controller, $method) = explode('@', $handler);
            
            // ToDo: Add a dependency injection container
            $instance = new $controller(); 
            $instance->$method($vars);
        } else {
            // ToDo: Handle other types of handlers if necessary
            call_user_func_array($handler, $vars);
        }
        break;
}
