<?php /** @var string $title */ /** @var string $message */ /** @var bool $isLoggedIn */ ?>

<div class="error-page">
    <h1><?= htmlspecialchars($title) ?></h1>
    <p><?= htmlspecialchars($message) ?></p>
    <p><a href="/">Go to homepage</a></p>
    <?php if (!$isLoggedIn): ?>
        <p>Or <a href="/login">login</a> to explore more.</p>
    <?php endif; ?>
</div>
