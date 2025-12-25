<?php
/** @var string $title */
/** @var array $categories */
/** @var string|null $success_message */
/** @var string|null $error_message */
use WikiApp\Lib\Session;
use WikiApp\Lib\Utils;
?>

<div class="category-list">
    <h1><?= htmlspecialchars($title) ?></h1>

    <?php if (Session::get('user_role') === 'admin'): ?>
        <p><a href="<?= Utils::url('/categories/create') ?>" class="btn btn-primary">Create New Category</a></p>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <?php if (!empty($categories)): ?>
        <div class="list-group">
            <?php foreach ($categories as $category): ?>
                <a href="<?= Utils::url('/category/' . htmlspecialchars((string)$category->slug)) ?>" class="list-group-item list-group-item-action">
                    <?= htmlspecialchars((string)$category->name) ?>
                    <?php if (Session::get('user_role') === 'admin'): ?>
                        <!-- ToDo: Add Edit and Delete buttons here later -->
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No categories found.</p>
    <?php endif; ?>
</div>
