<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Wiki</title>
    <link rel="stylesheet" href="/css/style.css"> <!-- Will create later -->
</head>
<body>
    <?php
use WikiApp\Lib\Utils;
?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h1 class="card-title text-center">Login to HR-Wiki</h1>

                <?php if (!empty($errors) && is_array($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($success_message) ?>
                    </div>
                <?php endif; ?>

                <form action="<?= Utils::url('/login') ?>" method="POST">
                    <?= \WikiApp\Lib\Csrf::field() ?>
                    <div class="mb-3">
                        <label for="username_or_email" class="form-label">Username or Email:</label>
                        <input type="text" id="username_or_email" name="username_or_email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password:</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>
            </div>
        </div>
        <p class="text-center mt-3">Don't have an account? <a href="<?= Utils::url('/register') ?>">Register here</a>.</p>
    </div>
</div>
</body>
</html>
