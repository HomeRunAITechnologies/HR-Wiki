<?php
/** @var string $title */
/** @var string $slug */
/** @var bool $isLoggedIn */
use WikiApp\Lib\Utils;
?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card text-center">
            <div class="card-body">
                <h1 class="card-title"><?= htmlspecialchars($title) ?></h1>
                <p class="lead">The page "<strong><?= htmlspecialchars(ucwords(str_replace('-', ' ', $slug))) ?></strong>" does not exist yet.</p>

                <?php if ($isLoggedIn): ?>
                    <p>Would you like to create it now?</p>
                    <a href="<?= Utils::url('/create/' . htmlspecialchars($slug)) ?>" class="btn btn-primary">Create Page</a>
                <?php else: ?>
                    <p>Please <a href="<?= Utils::url('/login') ?>">log in</a> to create this page.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
