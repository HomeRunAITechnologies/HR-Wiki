<?php

declare(strict_types=1);

namespace WikiApp\Controllers;

use WikiApp\Lib\View;
use WikiApp\Lib\Session;
use WikiApp\Models\User;
use WikiApp\Lib\Utils;

class UserController
{
    /**
     * Display the user's profile editing form.
     */
    public function showProfileForm(): void
    {
        if (!Session::has('user_id')) {
            header('Location: ' . Utils::url('/login'));
            exit;
        }

        View::render('user/profile', [
            'title' => 'Edit Profile',
            'username' => Session::get('username'),
            'success_message' => Session::getFlash('success_message'),
            'errors' => Session::getFlash('errors'),
        ]);
    }

    /**
     * Handle updating the user's password.
     */
    public function updatePassword(): void
    {
        if (!Session::has('user_id')) {
            header('Location: ' . Utils::url('/login'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . Utils::url('/profile'));
            exit;
        }

        $userId = (int)Session::get('user_id');
        $user = new User($userId);

        if (!$user->id) {
            Session::flash('error_message', 'Could not find your user profile.');
            header('Location: ' . Utils::url('/profile'));
            exit;
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $newPasswordConfirm = $_POST['new_password_confirm'] ?? '';

        $errors = [];

        // Validate current password
        if (!User::verifyPassword($currentPassword, (string)$user->password_hash)) {
            $errors[] = 'Your current password is not correct.';
        }

        // Validate new password
        if (empty($newPassword) || strlen($newPassword) < 6) {
            $errors[] = 'New password must be at least 6 characters.';
        } elseif ($newPassword !== $newPasswordConfirm) {
            $errors[] = 'New passwords do not match.';
        }

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            header('Location: ' . Utils::url('/profile'));
            exit;
        }

        // Update password
        $user->password_hash = User::hashPassword($newPassword);
        $user->save();

        Session::flash('success_message', 'Your password has been updated successfully.');
        header('Location: ' . Utils::url('/profile'));
        exit;
    }
}
