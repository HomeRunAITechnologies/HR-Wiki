<?php
/** @var string $title */
/** @var string $categoryName */
/** @var bool $isNew */
/** @var array $errors (optional) */
use WikiApp\Lib\Utils;

$actionUrl = $isNew ? Utils::url('/categories') : Utils::url('/categories/update/' . htmlspecialchars($slug ?? ''));
$submitButtonText = $isNew ? 'Create Category' : 'Update Category';
?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h1 class="card-title"><?= htmlspecialchars($title) ?></h1>

                <?php if (!empty($errors) && is_array($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?= $actionUrl ?>" method="POST">
                    <?= \WikiApp\Lib\Csrf::field() ?>
                    <div class="mb-3">
                        <label for="category_name" class="form-label">Category Name:</label>
                        <input type="text" id="category_name" name="name" class="form-control" value="<?= htmlspecialchars($categoryName) ?>" required>
                    </div>

                    <div>
                        <button type="submit" class="btn btn-primary"><?= $submitButtonText ?></button>
                        <a href="<?= Utils::url('/categories') ?>" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
