<?php

declare(strict_types=1);

namespace WikiApp\Controllers;

use WikiApp\Models\User;
use WikiApp\Models\Setting;
use WikiApp\Lib\View;
use WikiApp\Lib\Session;
use WikiApp\Lib\Utils;

class AuthController
{
    /**
     * Display the registration form.
     */
    public function showRegisterForm(): void
    {
        View::render('auth/register', ['errors' => Session::getFlash('errors')]);
    }

    /**
     * Handle user registration.
     */
    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . Utils::url('/register'));
            exit;
        }

        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        $errors = [];

        // Basic validation
        if (empty($username)) {
            $errors[] = 'Username is required.';
        } elseif (User::findByUsername($username)) {
            $errors[] = 'Username already taken.';
        }

        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format.';
        } elseif (User::findByEmail($email)) {
            $errors[] = 'Email already registered.';
        }

        if (empty($password) || strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        } elseif ($password !== $passwordConfirm) {
            $errors[] = 'Passwords do not match.';
        }

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            header('Location: ' . Utils::url('/register'));
            exit;
        }

        // Create user
        $user = new User();
        $user->username = $username;
        $user->email = $email;
        $user->password_hash = User::hashPassword($password);
        $user->role = 'editor'; // Default role
        $user->theme_slug = Setting::get('default_theme', 'cosmo'); // Apply default theme
        $user->save();

        // ToDo: Log user in after registration
        Session::flash('success_message', 'Registration successful! You can now log in.');
        header('Location: ' . Utils::url('/login'));
        exit;
    }

    /**
     * Display the login form.
     */
    public function showLoginForm(): void
    {
        View::render('auth/login', [
            'errors' => Session::getFlash('errors'),
            'success_message' => Session::getFlash('success_message')
        ]);
    }

    /**
     * Handle user login.
     */
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . Utils::url('/login'));
            exit;
        }

        $usernameOrEmail = $_POST['username_or_email'] ?? '';
        $password = $_POST['password'] ?? '';

        $errors = [];

        if (empty($usernameOrEmail) || empty($password)) {
            $errors[] = 'Both username/email and password are required.';
        }

        $user = null;
        if (filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL)) {
            $user = User::findByEmail($usernameOrEmail);
        } else {
            $user = User::findByUsername($usernameOrEmail);
        }

        if (!$user || !User::verifyPassword($password, (string)$user->password_hash)) {
            $errors[] = 'Invalid credentials.';
        }

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            header('Location: ' . Utils::url('/login'));
            exit;
        }

        // Login successful
        Session::start();
        Session::set('user_id', (int)$user->getId());
        Session::set('username', (string)$user->username);
        Session::set('user_role', (string)$user->role);
        
        Session::flash('success_message', 'You have been successfully logged in!');
        header('Location: ' . Utils::url('/')); // Redirect to home page or dashboard
        exit;
    }

    /**
     * Handle user logout.
     */
    public function logout(): void
    {
        Session::start();
        Session::destroy();
        header('Location: ' . Utils::url('/login'));
        exit;
    }
}
