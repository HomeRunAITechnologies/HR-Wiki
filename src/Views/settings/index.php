<?php
/** @var string $title */
/** @var string $siteName */
/** @var string $siteLogoUrl */
/** @var string $defaultTheme */
/** @var array $availableThemes */
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



                <form action="<?= Utils::url('/settings') ?>" method="POST">

                    <?= Csrf::field() ?>

                    <div class="mb-3">

                        <label for="site_name" class="form-label">Site Name:</label>

                        <input type="text" id="site_name" name="site_name" class="form-control" value="<?= htmlspecialchars($siteName) ?>" required>

                    </div>



                    <div class="mb-3">

                        <label for="site_logo_url" class="form-label">Site Logo URL:</label>

                        <input type="url" id="site_logo_url" name="site_logo_url" class="form-control" value="<?= htmlspecialchars($siteLogoUrl) ?>">

                        <div class="form-text">Optional: URL to your site's logo for the header.</div>

                    </div>

                    <div class="mb-3">
                        <label for="default_theme" class="form-label">Default Theme for New Users:</label>
                        <select id="default_theme" name="default_theme" class="form-select">
                            <optgroup label="Light Themes">
                                <?php foreach ($availableThemes as $theme): ?>
                                    <?php if (!(bool)$theme->is_dark): ?>
                                        <option value="<?= htmlspecialchars((string)$theme->slug) ?>" <?= ((string)$theme->slug === $defaultTheme) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars((string)$theme->name) ?>
                                            <?= ((string)$theme->type === 'custom') ? ' (Custom)' : '' ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </optgroup>
                            <optgroup label="Dark Themes">
                                <?php foreach ($availableThemes as $theme): ?>
                                    <?php if ((bool)$theme->is_dark): ?>
                                        <option value="<?= htmlspecialchars((string)$theme->slug) ?>" <?= ((string)$theme->slug === $defaultTheme) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars((string)$theme->name) ?>
                                            <?= ((string)$theme->type === 'custom') ? ' (Custom)' : '' ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                        <div class="form-text">This theme will be applied to new user registrations and guests.</div>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Settings</button>

                </form>

            </div>

        </div>

    </div>

</div>
