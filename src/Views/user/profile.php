<?php
/** @var string $title */
/** @var string $username */
/** @var string|null $success_message */
/** @var array $errors (optional) */

use WikiApp\Lib\Csrf;
use WikiApp\Lib\Utils;
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h1 class="card-title"><?= htmlspecialchars($title) ?></h1>
                <p>Welcome, <strong><?= htmlspecialchars($username) ?></strong>!</p>

                <hr>

                <h2>Change Password</h2>

                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
                <?php endif; ?>

                <?php if (!empty($errors) && is_array($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?= Utils::url('/profile/update-password') ?>" method="POST">
                    <?= Csrf::field() ?>
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password:</label>
                        <input type="password" id="current_password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password:</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password_confirm" class="form-label">Confirm New Password:</label>
                        <input type="password" id="new_password_confirm" name="new_password_confirm" class="form-control" required>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
