<?php
use WikiApp\Lib\Session;
use WikiApp\Lib\Csrf;
use WikiApp\Models\Setting;
use WikiApp\Models\Theme;
use WikiApp\Models\User;
use WikiApp\Lib\Utils;

$siteName = Setting::get('site_name', 'HR-Wiki');
$defaultThemeSlug = Setting::get('default_theme', 'cosmo');

// Get current theme
$currentThemeSlug = $defaultThemeSlug; // Use admin-configured default
if (Session::has('user_id')) {
    $currentUser = new User((int)Session::get('user_id'));
    if ($currentUser->getId() !== '-1' && !empty($currentUser->theme_slug)) {
        $currentThemeSlug = (string)$currentUser->theme_slug;
    }
} elseif (Session::has('theme')) {
    $currentThemeSlug = Session::get('theme');
}

$currentTheme = Theme::findBySlug($currentThemeSlug);
if (!$currentTheme || !(bool)$currentTheme->is_active) {
    // Fall back to default theme, then to cosmo if default is also invalid
    $currentTheme = Theme::findBySlug($defaultThemeSlug);
    if (!$currentTheme || !(bool)$currentTheme->is_active) {
        $currentTheme = Theme::findBySlug('cosmo');
        $currentThemeSlug = 'cosmo';
    } else {
        $currentThemeSlug = $defaultThemeSlug;
    }
}

$themeUrl = $currentTheme ? $currentTheme->getCssUrl() : 'https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/cosmo/bootstrap.min.css';
$isDarkTheme = $currentTheme ? $currentTheme->isDark() : false;

// Get all active themes for the selector
$availableThemes = Theme::getActiveThemes();
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="<?= $isDarkTheme ? 'dark' : 'light' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? $siteName) ?></title>
    <!-- Bootstrap CSS - Dynamic Theme -->
    <link id="theme-stylesheet" href="<?= htmlspecialchars($themeUrl) ?>" rel="stylesheet">
    <style>
        body { padding-top: 56px; }
        .page-content img { max-width: 100%; height: auto; }
        .theme-preview { width: 20px; height: 20px; border-radius: 3px; display: inline-block; margin-right: 8px; vertical-align: middle; }
        .theme-dropdown { max-height: 400px; overflow-y: auto; }
        .theme-item.active { background-color: var(--bs-primary); color: white; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <?php
            $siteLogoUrl = Setting::get('site_logo_url');
            ?>
            <a class="navbar-brand" href="<?= Utils::url('/') ?>">
                <?php if ($siteLogoUrl): ?>
                    <img src="<?= htmlspecialchars($siteLogoUrl) ?>" alt="<?= htmlspecialchars($siteName) ?> Logo" style="height: 30px; width: auto;">
                <?php else: ?>
                    <?= htmlspecialchars($siteName) ?>
                <?php endif; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="<?= Utils::url('/') ?>">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= Utils::url('/categories') ?>">Categories</a></li>
                    <?php if (Session::has('user_id')): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= Utils::url('/create') ?>">New Page</a></li>
                    <?php endif; ?>
                </ul>
                <form class="d-flex" action="<?= Utils::url('/search') ?>" method="GET">
                    <input class="form-control me-2" type="search" name="q" placeholder="Search" aria-label="Search" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                    <button class="btn btn-outline-success" type="submit">Search</button>
                </form>
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <!-- Theme Selector -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="themeDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Change Theme">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8 5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3zm4 3a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3zM5.5 7a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm.5 6a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z"/>
                                <path d="M16 8c0 3.15-1.866 2.585-3.567 2.07C11.42 9.763 10.465 9.473 10 10c-.603.683-.475 1.819-.351 2.92C9.826 14.495 9.996 16 8 16a8 8 0 1 1 8-8zm-8 7c.611 0 .654-.171.655-.176.078-.146.124-.464.07-1.119-.014-.168-.037-.37-.061-.591-.052-.464-.112-1.005-.118-1.462-.01-.707.083-1.61.704-2.314.369-.417.845-.578 1.272-.618.404-.038.812.026 1.16.104.343.077.702.186 1.025.284l.028.008c.346.105.658.199.953.266.653.148.904.083.991.024C14.717 9.38 15 9.161 15 8a7 7 0 1 0-7 7z"/>
                            </svg>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end theme-dropdown" aria-labelledby="themeDropdown">
                            <li><h6 class="dropdown-header">Light Themes</h6></li>
                            <?php foreach ($availableThemes as $theme): ?>
                                <?php if (!(bool)$theme->is_dark): ?>
                                    <li>
                                        <a class="dropdown-item theme-item <?= ((string)$theme->slug === $currentThemeSlug) ? 'active' : '' ?>"
                                           href="#" data-theme="<?= htmlspecialchars((string)$theme->slug) ?>"
                                           data-css="<?= htmlspecialchars($theme->getCssUrl()) ?>"
                                           data-dark="0">
                                            <?= htmlspecialchars((string)$theme->name) ?>
                                            <?php if ((string)$theme->type === 'custom'): ?>
                                                <span class="badge bg-info">Custom</span>
                                            <?php endif; ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header">Dark Themes</h6></li>
                            <?php foreach ($availableThemes as $theme): ?>
                                <?php if ((bool)$theme->is_dark): ?>
                                    <li>
                                        <a class="dropdown-item theme-item <?= ((string)$theme->slug === $currentThemeSlug) ? 'active' : '' ?>"
                                           href="#" data-theme="<?= htmlspecialchars((string)$theme->slug) ?>"
                                           data-css="<?= htmlspecialchars($theme->getCssUrl()) ?>"
                                           data-dark="1">
                                            <?= htmlspecialchars((string)$theme->name) ?>
                                            <?php if ((string)$theme->type === 'custom'): ?>
                                                <span class="badge bg-info">Custom</span>
                                            <?php endif; ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </li>

                    <?php if (Session::has('user_id')): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?= htmlspecialchars(Session::get('username')) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="<?= Utils::url('/profile') ?>">Profile</a></li>
                                <?php if (Session::get('user_role') === 'admin'): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Admin</h6></li>
                                    <li><a class="dropdown-item" href="<?= Utils::url('/settings') ?>">Settings</a></li>
                                    <li><a class="dropdown-item" href="<?= Utils::url('/admin/themes') ?>">Themes</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= Utils::url('/logout') ?>">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="<?= Utils::url('/login') ?>">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= Utils::url('/register') ?>">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container mt-4">
        <?php
        // Display flash messages
        if ($success = Session::getFlash('success_message')) {
            echo '<div class="alert alert-success">' . htmlspecialchars($success) . '</div>';
        }
        if ($error = Session::getFlash('error_message')) {
            echo '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>';
        }
        // Content will be injected here by the View::render method
        echo $content ?? ''; 
        ?>
    </main>

    <footer class="container text-center mt-5 mb-3">
        <p class="text-muted">Copyright (c) 2025 <?= htmlspecialchars($siteName) ?>. Released under the MIT License.</p>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Theme Switcher -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const themeItems = document.querySelectorAll('.theme-item');
        const themeStylesheet = document.getElementById('theme-stylesheet');
        const csrfToken = '<?= Csrf::generateToken() ?>';

        themeItems.forEach(function(item) {
            item.addEventListener('click', function(e) {
                e.preventDefault();

                const themeName = this.dataset.theme;
                const cssUrl = this.dataset.css;
                const isDark = this.dataset.dark === '1';

                // Update stylesheet
                themeStylesheet.href = cssUrl;

                // Update dark mode attribute
                document.documentElement.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');

                // Update active state in dropdown
                themeItems.forEach(function(ti) {
                    ti.classList.remove('active');
                });
                this.classList.add('active');

                // Save preference via AJAX
                fetch('<?= Utils::url('/api/set-theme') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: '<?= Csrf::TOKEN_NAME ?>=' + encodeURIComponent(csrfToken) + '&theme=' + encodeURIComponent(themeName),
                    credentials: 'same-origin'
                }).then(function(response) {
                    return response.json();
                }).then(function(data) {
                    if (!data.success) {
                        console.error('Failed to save theme preference');
                    }
                }).catch(function(error) {
                    console.error('Error saving theme:', error);
                });
            });
        });
    });
    </script>
</body>
</html>
