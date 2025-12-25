<?php
/** @var string $title */
/** @var string $query */
/** @var array $pageResults */
/** @var array $categoryResults */
use WikiApp\Lib\Utils;
?>
<div class="search-results">
    <h1><?= htmlspecialchars($title) ?></h1>

    <?php if (empty($query)): ?>
        <p>Please enter a search query.</p>
    <?php else: ?>
        <p>Showing results for "<strong><?= htmlspecialchars($query) ?></strong>"</p>

        <div class="row">
            <div class="col-md-6">
                <h2>Pages</h2>
                <?php if (!empty($pageResults)): ?>
                    <div class="list-group">
                        <?php foreach ($pageResults as $page): ?>
                            <a href="<?= Utils::url('/' . htmlspecialchars((string)$page->slug)) ?>" class="list-group-item list-group-item-action"><?= htmlspecialchars((string)$page->title) ?></a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No pages found matching your query.</p>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <h2>Categories</h2>
                <?php if (!empty($categoryResults)): ?>
                    <div class="list-group">
                        <?php foreach ($categoryResults as $category): ?>
                            <a href="<?= Utils::url('/category/' . htmlspecialchars((string)$category->slug)) ?>" class="list-group-item list-group-item-action"><?= htmlspecialchars((string)$category->name) ?></a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No categories found matching your query.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
