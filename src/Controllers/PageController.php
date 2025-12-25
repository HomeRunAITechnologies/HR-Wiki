<?php

declare(strict_types=1);

namespace WikiApp\Controllers;

use WikiApp\Lib\View;
use WikiApp\Lib\Session;
use WikiApp\Lib\Utils; // Import the Utils class
use WikiApp\Models\Page;
use WikiApp\Models\User;
use WikiApp\Models\PageVersion; // For creating versions
use WikiApp\Models\Category; // For fetching all categories
use WikiApp\Models\PageCategory; // For managing page categories
use Parsedown; // Import Parsedown
use HTMLPurifier; // Import HTMLPurifier
use HTMLPurifier_Config; // Import HTMLPurifier_Config

class PageController
{
    /**
     * Display a wiki page or the home page.
     *
     * @param array $vars Route variables (e.g., ['slug' => 'page-name'])
     */
    public function show(array $vars): void
    {
        $requestedSlug = $vars['slug'] ?? 'home'; // Default to 'home' page
        $slug = Utils::slugify($requestedSlug);
        $isLoggedIn = Session::has('user_id');

        // Special case for the homepage - show page list with optional editable content
        if ($slug === 'home') {
            $allPages = Page::getAllObjects('id', [], 'AND', true);
            $homePage = Page::findBySlug('home');

            // If home page exists, process its content
            $homeContent = '';
            if ($homePage) {
                $parsedown = new Parsedown();
                $config = HTMLPurifier_Config::createDefault();
                $purifier = new HTMLPurifier($config);
                $homeContent = (string)$homePage->content;
                if ((string)$homePage->format === 'markdown') {
                    $homeContent = $parsedown->text($homeContent);
                }
                $homeContent = $purifier->purify($homeContent);
            }

            View::render('home', [
                'title' => 'Welcome to the Wiki',
                'allPages' => $allPages,
                'isLoggedIn' => $isLoggedIn,
                'homePage' => $homePage,
                'homeContent' => $homeContent,
            ]);
            exit;
        }

        $page = Page::findBySlug($slug);

        if ($page) {
            // Enforce visibility rule
            if ((string)$page->visibility === 'private' && !$isLoggedIn) {
                http_response_code(404);
                View::render('errors/404', [
                    'title' => 'Page Not Found',
                    'message' => 'The page you are looking for does not exist or you do not have permission to view it.',
                    'isLoggedIn' => false,
                ]);
                exit;
            }
            
            $content = (string)$page->content;
            $format = (string)$page->format;

            // Initialize Parsedown for Markdown rendering
            $parsedown = new Parsedown();

            // Initialize HTMLPurifier for content sanitization
            $config = HTMLPurifier_Config::createDefault();
            $purifier = new HTMLPurifier($config);

            // Convert Markdown to HTML if necessary
            if ($format === 'markdown') {
                $content = $parsedown->text($content);
            }

            // Sanitize the content (whether original HTML or from Markdown)
            $safeContent = $purifier->purify($content);

            View::render('page/show', [
                'title' => (string)$page->title,
                'content' => $safeContent,
                'slug' => $slug,
                'isLoggedIn' => $isLoggedIn,
            ]);
        } else {
            if ($isLoggedIn) {
                View::render('page/create_prompt', [
                    'title' => 'Create New Page: ' . ucwords(str_replace('-', ' ', $slug)),
                    'slug' => $slug,
                    'isLoggedIn' => $isLoggedIn,
                ]);
            } else {
                http_response_code(404);
                View::render('errors/404', [
                    'title' => 'Page Not Found',
                    'message' => 'The page you are looking for does not exist. Login to create it.',
                    'isLoggedIn' => $isLoggedIn,
                ]);
            }
        }
    }

    /**
     * Display the form to create a new page.
     *
     * @param array $vars Optional route variables, e.g., 'slug' to pre-fill.
     */
    public function createForm(array $vars = []): void
    {
        if (!Session::has('user_id')) {
            Session::flash('error_message', 'You must be logged in to create a page.');
            header('Location: ' . Utils::url('/login'));
            exit;
        }

        $slug = $vars['slug'] ?? '';
        $title = ucwords(str_replace('-', ' ', $slug));
        $allCategories = Category::getAllObjects(); // Fetch all categories

        View::render('page/edit_create', [
            'title' => 'Create New Page' . ($title ? ': ' . htmlspecialchars($title) : ''),
            'pageTitle' => $title,
            'slug' => $slug,
            'content' => '',
            'isNew' => true,
            'errors' => Session::getFlash('errors'),
            'allCategories' => $allCategories,
            'assignedCategories' => [], // No categories assigned for a new page
        ]);
    }

    /**
     * Store a new page in the database.
     */
    public function store(): void
    {
        if (!Session::has('user_id')) {
            Session::flash('error_message', 'You must be logged in to create a page.');
            header('Location: ' . Utils::url('/login'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . Utils::url('/create'));
            exit;
        }

        $pageTitle = trim($_POST['title'] ?? '');
        $content = $_POST['content'] ?? '';
        $format = $_POST['format'] ?? 'html';
        $visibility = $_POST['visibility'] ?? 'public';
        $authorId = (int)Session::get('user_id');

        $errors = [];

        if (empty($pageTitle)) {
            $errors[] = 'Page title is required.';
        }

        $slug = Utils::slugify($pageTitle);
        if (Page::findBySlug($slug)) {
            $errors[] = 'A page with this title already exists. Please choose a different title.';
        }

        if (!in_array($format, ['html', 'markdown'])) {
            $errors[] = 'Invalid content format.';
        }

        if (!in_array($visibility, ['public', 'private'])) {
            $errors[] = 'Invalid visibility setting.';
        }

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            header('Location: ' . Utils::url('/create/' . urlencode($slug)));
            exit;
        }

        $page = new Page();
        $page->title = $pageTitle;
        $page->slug = $slug;
        $page->content = $content;
        $page->format = $format;
        $page->visibility = $visibility;
        $page->author_id = $authorId;
        $page->save();

        // Create initial page version
        $pageVersion = new PageVersion();
        $pageVersion->page_id = (int)$page->getId();
        $pageVersion->version_number = PageVersion::getNextVersionNumber((int)$page->getId());
        $pageVersion->title = $pageTitle;
        $pageVersion->content = $content;
        $pageVersion->format = $format;
        $pageVersion->author_id = $authorId;
        $pageVersion->save();

        // Handle categories
        $selectedCategories = $_POST['categories'] ?? [];
        if (is_array($selectedCategories)) {
            foreach ($selectedCategories as $categoryId) {
                PageCategory::link((int)$page->getId(), (int)$categoryId);
            }
        }

        Session::flash('success_message', 'Page "' . htmlspecialchars($pageTitle) . '" created successfully!');
        header('Location: ' . Utils::url('/' . urlencode($slug)));
        exit;
    }

    /**
     * Display the form to edit an existing page.
     *
     * @param array $vars Route variables including the page slug.
     */
    public function editForm(array $vars): void
    {
        if (!Session::has('user_id')) {
            Session::flash('error_message', 'You must be logged in to edit a page.');
            header('Location: ' . Utils::url('/login'));
            exit;
        }

        $slug = Utils::slugify($vars['slug']);
        $page = Page::findBySlug($slug);
        $allCategories = Category::getAllObjects(); // Fetch all categories
        $assignedCategories = [];

        if ($page) {
            $assignedCategoriesObjects = PageCategory::getCategoriesForPage((int)$page->getId());
            foreach ($assignedCategoriesObjects as $cat) {
                $assignedCategories[] = (int)$cat->id; // Get just the IDs
            }
        } else {
            http_response_code(404);
            View::render('errors/404', [
                'title' => 'Page Not Found',
                'message' => 'The page you are trying to edit does not exist.',
                'isLoggedIn' => Session::has('user_id'),
            ]);
            exit;
        }

        View::render('page/edit_create', [
            'title' => 'Edit Page: ' . htmlspecialchars((string)$page->title),
            'pageTitle' => (string)$page->title,
            'slug' => $slug,
            'content' => (string)$page->content,
            'format' => (string)$page->format,
            'visibility' => (string)$page->visibility,
            'isNew' => false,
            'errors' => Session::getFlash('errors'),
            'allCategories' => $allCategories,
            'assignedCategories' => $assignedCategories,
        ]);
    }

    /**
     * Update an existing page in the database.
     *
     * @param array $vars Route variables including the page slug.
     */
    public function update(array $vars): void
    {
        if (!Session::has('user_id')) {
            Session::flash('error_message', 'You must be logged in to update a page.');
            header('Location: ' . Utils::url('/login'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . Utils::url('/edit/' . urlencode($vars['slug'])));
            exit;
        }

        $slug = Utils::slugify($vars['slug']);
        $page = Page::findBySlug($slug);

        if (!$page) {
            http_response_code(404);
            View::render('errors/404', [
                'title' => 'Page Not Found',
                'message' => 'The page you are trying to update does not exist.',
                'isLoggedIn' => Session::has('user_id'),
            ]);
            exit;
        }

        $pageTitle = trim($_POST['title'] ?? '');
        $content = $_POST['content'] ?? '';
        $format = $_POST['format'] ?? 'html';
        $visibility = $_POST['visibility'] ?? 'public';
        $authorId = (int)Session::get('user_id');

        $errors = [];

        if (empty($pageTitle)) {
            $errors[] = 'Page title is required.';
        }

        $newSlug = Utils::slugify($pageTitle);
        if ($newSlug !== (string)$page->slug && Page::findBySlug($newSlug)) {
            $errors[] = 'A page with the new title already exists. Please choose a different title.';
        }

        if (!in_array($format, ['html', 'markdown'])) {
            $errors[] = 'Invalid content format.';
        }

        if (!in_array($visibility, ['public', 'private'])) {
            $errors[] = 'Invalid visibility setting.';
        }

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old_input', $_POST);
            header('Location: ' . Utils::url('/edit/' . urlencode($slug)));
            exit;
        }

        $page->title = $pageTitle;
        $page->slug = $newSlug;
        $page->content = $content;
        $page->format = $format;
        $page->visibility = $visibility;
        $page->save();

        // Create new page version
        $pageVersion = new PageVersion();
        $pageVersion->page_id = (int)$page->getId();
        $pageVersion->version_number = PageVersion::getNextVersionNumber((int)$page->getId());
        $pageVersion->title = $pageTitle;
        $pageVersion->content = $content;
        $pageVersion->format = $format;
        $pageVersion->author_id = $authorId; // The user who made the update
        $pageVersion->save();

        // Handle categories: clear existing and re-add selected ones
        // First, get all current categories for the page to unlink them
        $currentCategoryLinks = PageCategory::_fetchAll("SELECT category_id FROM _DB_ WHERE page_id = ?", [(int)$page->getId()]);
        foreach ($currentCategoryLinks as $link) {
            PageCategory::unlink((int)$page->getId(), (int)$link['category_id']);
        }

        $selectedCategories = $_POST['categories'] ?? [];
        if (is_array($selectedCategories)) {
            foreach ($selectedCategories as $categoryId) {
                PageCategory::link((int)$page->getId(), (int)$categoryId);
            }
        }

        Session::flash('success_message', 'Page "' . htmlspecialchars($pageTitle) . '" updated successfully!');
        header('Location: ' . Utils::url('/' . urlencode($newSlug)));
        exit;
    }

    /**
     * Display the version history for a page.
     *
     * @param array $vars Route variables including the page slug.
     */
    public function history(array $vars): void
    {
        $slug = Utils::slugify($vars['slug']);
        $page = Page::findBySlug($slug);
        $isLoggedIn = Session::has('user_id');

        if (!$page) {
            http_response_code(404);
            View::render('errors/404', [
                'title' => 'Page Not Found',
                'message' => 'The page you are looking for does not exist.',
                'isLoggedIn' => $isLoggedIn,
            ]);
            exit;
        }

        // Enforce visibility rule
        if ((string)$page->visibility === 'private' && !$isLoggedIn) {
            http_response_code(404);
            View::render('errors/404', [
                'title' => 'Page Not Found',
                'message' => 'The page you are looking for does not exist or you do not have permission to view it.',
                'isLoggedIn' => false,
            ]);
            exit;
        }

        $versions = PageVersion::getVersionsForPage((int)$page->getId());

        // Get author names for each version
        $versionData = [];
        foreach ($versions as $version) {
            $author = new User((int)$version->author_id);
            $versionData[] = [
                'version' => $version,
                'author_name' => ($author->getId() !== '-1') ? (string)$author->username : 'Unknown',
            ];
        }

        View::render('page/history', [
            'title' => 'History: ' . (string)$page->title,
            'page' => $page,
            'slug' => $slug,
            'versions' => $versionData,
            'isLoggedIn' => $isLoggedIn,
        ]);
    }

    /**
     * Display a diff between two versions.
     *
     * @param array $vars Route variables including page slug and version numbers.
     */
    public function diff(array $vars): void
    {
        $slug = Utils::slugify($vars['slug']);
        $oldVersion = (int)($vars['old'] ?? 0);
        $newVersion = (int)($vars['new'] ?? 0);
        $page = Page::findBySlug($slug);
        $isLoggedIn = Session::has('user_id');

        if (!$page) {
            http_response_code(404);
            View::render('errors/404', [
                'title' => 'Page Not Found',
                'message' => 'The page you are looking for does not exist.',
                'isLoggedIn' => $isLoggedIn,
            ]);
            exit;
        }

        // Enforce visibility rule
        if ((string)$page->visibility === 'private' && !$isLoggedIn) {
            http_response_code(404);
            View::render('errors/404', [
                'title' => 'Page Not Found',
                'message' => 'The page you are looking for does not exist or you do not have permission to view it.',
                'isLoggedIn' => false,
            ]);
            exit;
        }

        $pageId = (int)$page->getId();
        $oldVersionObj = PageVersion::getVersion($pageId, $oldVersion);
        $newVersionObj = PageVersion::getVersion($pageId, $newVersion);

        if (!$oldVersionObj || !$newVersionObj) {
            http_response_code(404);
            View::render('errors/404', [
                'title' => 'Version Not Found',
                'message' => 'One or both of the requested versions do not exist.',
                'isLoggedIn' => $isLoggedIn,
            ]);
            exit;
        }

        // Get author names
        $oldAuthor = new User((int)$oldVersionObj->author_id);
        $newAuthor = new User((int)$newVersionObj->author_id);

        View::render('page/diff', [
            'title' => 'Diff: ' . (string)$page->title,
            'page' => $page,
            'slug' => $slug,
            'oldVersion' => $oldVersionObj,
            'newVersion' => $newVersionObj,
            'oldAuthorName' => ($oldAuthor->getId() !== '-1') ? (string)$oldAuthor->username : 'Unknown',
            'newAuthorName' => ($newAuthor->getId() !== '-1') ? (string)$newAuthor->username : 'Unknown',
            'isLoggedIn' => $isLoggedIn,
        ]);
    }
}
