<?php

declare(strict_types=1);

namespace WikiApp\Controllers;

use WikiApp\Lib\View;
use WikiApp\Lib\Session;
use WikiApp\Models\Setting;
use WikiApp\Models\Theme;
use WikiApp\Lib\Utils;

class SettingsController
{
    /**
     * Display the settings form.
     */
    public function showSettingsForm(): void
    {
        if (!Session::has('user_id') || Session::get('user_role') !== 'admin') {
            Session::flash('error_message', 'You do not have permission to access settings.');
            header('Location: ' . Utils::url('/login')); // Or redirect to home
            exit;
        }

        $siteName = Setting::get('site_name', 'HR-Wiki');
        $siteLogoUrl = Setting::get('site_logo_url', '');
        $defaultTheme = Setting::get('default_theme', 'cosmo');
        $availableThemes = Theme::getActiveThemes();

        View::render('settings/index', [
            'title' => 'Site Settings',
            'siteName' => $siteName,
            'siteLogoUrl' => $siteLogoUrl,
            'defaultTheme' => $defaultTheme,
            'availableThemes' => $availableThemes,
            'success_message' => Session::getFlash('success_message'),
            'errors' => Session::getFlash('errors'),
        ]);
    }

    /**
     * Update site settings.
     */
    public function updateSettings(): void
    {
        if (!Session::has('user_id') || Session::get('user_role') !== 'admin') {
            Session::flash('error_message', 'You do not have permission to update settings.');
            header('Location: ' . Utils::url('/login'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . Utils::url('/settings'));
            exit;
        }

        $siteName = trim($_POST['site_name'] ?? '');
        $siteLogoUrl = trim($_POST['site_logo_url'] ?? '');
        $defaultTheme = trim($_POST['default_theme'] ?? 'cosmo');

        $errors = [];

        if (empty($siteName)) {
            $errors[] = 'Site name cannot be empty.';
        }

        // Validate default theme exists and is active
        $theme = Theme::findBySlug($defaultTheme);
        if (!$theme || !(bool)$theme->is_active) {
            $errors[] = 'Invalid theme selected.';
        }

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old_input', $_POST);
            header('Location: ' . Utils::url('/settings'));
            exit;
        }

        Setting::set('site_name', $siteName);
        Setting::set('site_logo_url', $siteLogoUrl);
        Setting::set('default_theme', $defaultTheme);

        Session::flash('success_message', 'Site settings updated successfully!');
        header('Location: ' . Utils::url('/settings'));
        exit;
    }
}
