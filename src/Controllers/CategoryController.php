<?php

declare(strict_types=1);

namespace WikiApp\Controllers;

use WikiApp\Lib\View;
use WikiApp\Lib\Session;
use WikiApp\Models\Category;
use WikiApp\Lib\Utils; // Import the Utils class

class CategoryController
{
    /**
     * Display a list of all categories.
     */
    public function index(): void
    {
        $categories = Category::getAllObjects(); // Fetches all categories
        
        View::render('category/index', [
            'title' => 'Categories',
            'categories' => $categories,
            'success_message' => Session::getFlash('success_message'),
            'error_message' => Session::getFlash('error_message'),
        ]);
    }

    /**
     * Display the form to create a new category.
     */
    public function createForm(): void
    {
        if (!Session::has('user_id') || Session::get('user_role') !== 'admin') {
            Session::flash('error_message', 'You do not have permission to create categories.');
            header('Location: ' . Utils::url('/login')); // Or redirect to categories index
            exit;
        }

        View::render('category/create_edit', [
            'title' => 'Create New Category',
            'categoryName' => '',
            'isNew' => true,
            'errors' => Session::getFlash('errors'),
        ]);
    }

    /**
     * Store a new category in the database.
     */
    public function store(): void
    {
        if (!Session::has('user_id') || Session::get('user_role') !== 'admin') {
            Session::flash('error_message', 'You do not have permission to create categories.');
            header('Location: ' . Utils::url('/login'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . Utils::url('/categories/create'));
            exit;
        }

        $categoryName = trim($_POST['name'] ?? '');
        $errors = [];

        if (empty($categoryName)) {
            $errors[] = 'Category name is required.';
        }

        $slug = Utils::slugify($categoryName);
        if (Category::findBySlug($slug)) {
            $errors[] = 'A category with this name already exists.';
        }

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old_input', $_POST);
            header('Location: ' . Utils::url('/categories/create'));
            exit;
        }

        $category = new Category();
        $category->name = $categoryName;
        $category->slug = $slug;
        $category->save();

        Session::flash('success_message', 'Category "' . htmlspecialchars($categoryName) . '" created successfully!');
        header('Location: ' . Utils::url('/categories'));
        exit;
    }


}
