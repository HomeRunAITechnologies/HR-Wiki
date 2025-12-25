<?php

declare(strict_types=1);

namespace WikiApp\Controllers;

use WikiApp\Lib\View;
use WikiApp\Lib\Session;
use WikiApp\Lib\Utils;
use WikiApp\Lib\Csrf;
use WikiApp\Models\Theme;
use WikiApp\Models\User;
use WikiApp\Models\Setting;

class ThemeController
{
    /**
     * Set user's theme preference (AJAX).
     */
    public function setTheme(): void
    {
        header('Content-Type: application/json');

        $slug = $_POST['theme'] ?? '';
        $theme = Theme::findBySlug($slug);

        if (!$theme || !(bool)$theme->is_active) {
            echo json_encode(['success' => false, 'error' => 'Invalid theme']);
            return;
        }

        // Store in session for guests
        Session::set('theme', $slug);

        // If logged in, save to user record
        if (Session::has('user_id')) {
            $userId = (int)Session::get('user_id');
            $user = new User($userId);
            if ($user->getId() !== '-1') {
                $user->theme_slug = $slug;
                $user->save();
            }
        }

        echo json_encode([
            'success' => true,
            'css_url' => $theme->getCssUrl(),
            'is_dark' => $theme->isDark()
        ]);
    }

    /**
     * Serve custom theme CSS.
     */
    public function serveCss(array $vars): void
    {
        $slug = $vars['slug'] ?? '';
        $theme = Theme::findBySlug($slug);

        if (!$theme || (string)$theme->type !== 'custom' || empty($theme->css_content)) {
            http_response_code(404);
            echo '/* Theme not found */';
            return;
        }

        header('Content-Type: text/css');
        header('Cache-Control: public, max-age=86400');
        echo $theme->css_content;
    }

    /**
     * Admin: List all themes.
     */
    public function adminIndex(): void
    {
        if (!$this->isAdmin()) {
            $this->redirectToLogin();
            return;
        }

        $themes = Theme::getAllObjects('sort_order');
        $defaultThemeSlug = Setting::get('default_theme', 'cosmo');

        View::render('admin/themes', [
            'title' => 'Manage Themes',
            'themes' => $themes,
            'defaultThemeSlug' => $defaultThemeSlug,
            'isLoggedIn' => true,
        ]);
    }

    /**
     * Admin: Download theme template.
     */
    public function downloadTemplate(): void
    {
        if (!$this->isAdmin()) {
            $this->redirectToLogin();
            return;
        }

        header('Content-Type: text/css');
        header('Content-Disposition: attachment; filename="custom-theme-template.css"');
        echo Theme::getTemplateCSS();
    }

    /**
     * Admin: Upload custom theme.
     */
    public function uploadTheme(): void
    {
        if (!$this->isAdmin()) {
            $this->redirectToLogin();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . Utils::url('/admin/themes'));
            exit;
        }

        if (!Csrf::validateToken($_POST[Csrf::TOKEN_NAME] ?? '')) {
            Session::flash('error_message', 'Invalid request.');
            header('Location: ' . Utils::url('/admin/themes'));
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $isDark = isset($_POST['is_dark']) ? 1 : 0;

        if (empty($name)) {
            Session::flash('error_message', 'Theme name is required.');
            header('Location: ' . Utils::url('/admin/themes'));
            exit;
        }

        // Generate slug
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
        $slug = trim($slug, '-');

        // Check if slug exists
        if (Theme::findBySlug($slug)) {
            $slug .= '-' . uniqid();
        }

        // Handle file upload
        $cssContent = '';
        if (isset($_FILES['css_file']) && $_FILES['css_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['css_file'];

            // Validate file type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, ['text/css', 'text/plain', 'application/octet-stream'])) {
                Session::flash('error_message', 'Invalid file type. Please upload a CSS file.');
                header('Location: ' . Utils::url('/admin/themes'));
                exit;
            }

            // Read CSS content
            $cssContent = file_get_contents($file['tmp_name']);

            // Basic sanitization - remove potential script injections
            $cssContent = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $cssContent);
            $cssContent = preg_replace('/javascript:/i', '', $cssContent);
            $cssContent = preg_replace('/expression\s*\(/i', '', $cssContent);
        } elseif (!empty($_POST['css_content'])) {
            $cssContent = $_POST['css_content'];
        }

        if (empty($cssContent)) {
            Session::flash('error_message', 'Please upload a CSS file or paste CSS content.');
            header('Location: ' . Utils::url('/admin/themes'));
            exit;
        }

        // Get max sort order
        $maxOrder = Theme::_fetch("SELECT MAX(sort_order) as max_order FROM themes", []);
        $sortOrder = ((int)($maxOrder['max_order'] ?? 0)) + 1;

        // Create theme
        $theme = new Theme();
        $theme->name = $name;
        $theme->slug = $slug;
        $theme->type = 'custom';
        $theme->css_content = $cssContent;
        $theme->is_dark = $isDark;
        $theme->is_active = 1;
        $theme->sort_order = $sortOrder;
        $theme->save();

        Session::flash('success_message', 'Theme "' . htmlspecialchars($name) . '" created successfully!');
        header('Location: ' . Utils::url('/admin/themes'));
        exit;
    }

    /**
     * Admin: Toggle theme active status.
     */
    public function toggleTheme(): void
    {
        if (!$this->isAdmin()) {
            $this->redirectToLogin();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . Utils::url('/admin/themes'));
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $theme = new Theme($id);

        if ($theme->getId() !== '-1') {
            $theme->is_active = (bool)$theme->is_active ? 0 : 1;
            $theme->save();
            Session::flash('success_message', 'Theme status updated.');
        }

        header('Location: ' . Utils::url('/admin/themes'));
        exit;
    }

    /**
     * Admin: Delete custom theme.
     */
    public function deleteTheme(): void
    {
        if (!$this->isAdmin()) {
            $this->redirectToLogin();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . Utils::url('/admin/themes'));
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $theme = new Theme($id);

        if ($theme->getId() !== '-1' && (string)$theme->type === 'custom') {
            Theme::_doQuery("DELETE FROM themes WHERE id = ?", [$id]);
            Session::flash('success_message', 'Theme deleted.');
        } else {
            Session::flash('error_message', 'Cannot delete built-in themes.');
        }

        header('Location: ' . Utils::url('/admin/themes'));
        exit;
    }

    /**
     * Admin: Set a theme as the default for new users and guests.
     */
    public function setDefaultTheme(): void
    {
        if (!$this->isAdmin()) {
            $this->redirectToLogin();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . Utils::url('/admin/themes'));
            exit;
        }

        $slug = $_POST['slug'] ?? '';
        $theme = Theme::findBySlug($slug);

        if ($theme && (bool)$theme->is_active) {
            Setting::set('default_theme', $slug);
            Session::flash('success_message', '"' . htmlspecialchars((string)$theme->name) . '" is now the default theme.');
        } else {
            Session::flash('error_message', 'Invalid or inactive theme.');
        }

        header('Location: ' . Utils::url('/admin/themes'));
        exit;
    }

    /**
     * Check if current user is admin.
     */
    private function isAdmin(): bool
    {
        return Session::has('user_id') && Session::get('user_role') === 'admin';
    }

    /**
     * Redirect to login page.
     */
    private function redirectToLogin(): void
    {
        Session::flash('error_message', 'Admin access required.');
        header('Location: ' . Utils::url('/login'));
        exit;
    }
}
