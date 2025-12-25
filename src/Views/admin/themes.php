<?php
/** @var string $title */
/** @var array $themes */
/** @var string $defaultThemeSlug */
/** @var bool $isLoggedIn */
use WikiApp\Lib\Utils;
use WikiApp\Lib\Csrf;
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-palette"></i> <?= htmlspecialchars($title) ?></h1>
    <div class="btn-group">
        <a href="<?= Utils::url('/admin/themes/download-template') ?>" class="btn btn-outline-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
            </svg>
            Download Template
        </a>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadThemeModal">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V10.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"/>
            </svg>
            Upload Custom Theme
        </button>
    </div>
</div>

<div class="row">
    <!-- Built-in Themes -->
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <strong>Built-in Themes</strong>
                <span class="badge bg-secondary"><?= count(array_filter($themes, fn($t) => (string)$t->type === 'builtin')) ?></span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($themes as $theme): ?>
                            <?php if ((string)$theme->type === 'builtin'): ?>
                                <?php $isDefault = ((string)$theme->slug === $defaultThemeSlug); ?>
                                <tr<?= $isDefault ? ' class="table-primary"' : '' ?>>
                                    <td>
                                        <strong><?= htmlspecialchars((string)$theme->name) ?></strong>
                                        <?php if ((bool)$theme->is_dark): ?>
                                            <span class="badge bg-dark">Dark</span>
                                        <?php endif; ?>
                                        <?php if ($isDefault): ?>
                                            <span class="badge bg-warning text-dark">Default</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><code><?= htmlspecialchars((string)$theme->slug) ?></code></td>
                                    <td><span class="badge bg-info">Built-in</span></td>
                                    <td>
                                        <?php if ((bool)$theme->is_active): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if ((bool)$theme->is_active && !$isDefault): ?>
                                            <form method="post" action="<?= Utils::url('/admin/themes/set-default') ?>" class="d-inline">
                                                <?= Csrf::field() ?>
                                                <input type="hidden" name="slug" value="<?= htmlspecialchars((string)$theme->slug) ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-primary">Set Default</button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="post" action="<?= Utils::url('/admin/themes/toggle') ?>" class="d-inline">
                                            <?= Csrf::field() ?>
                                            <input type="hidden" name="id" value="<?= (int)$theme->getId() ?>">
                                            <button type="submit" class="btn btn-sm <?= (bool)$theme->is_active ? 'btn-outline-warning' : 'btn-outline-success' ?>"<?= $isDefault ? ' disabled title="Cannot disable default theme"' : '' ?>>
                                                <?= (bool)$theme->is_active ? 'Disable' : 'Enable' ?>
                                            </button>
                                        </form>
                                        <a href="<?= htmlspecialchars((string)$theme->css_url) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">View CSS</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Custom Themes -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <strong>Custom Themes</strong>
                <span class="badge bg-secondary"><?= count(array_filter($themes, fn($t) => (string)$t->type === 'custom')) ?></span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $customThemes = array_filter($themes, fn($t) => (string)$t->type === 'custom');
                        if (empty($customThemes)):
                        ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No custom themes yet. Download the template and upload your own!
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($customThemes as $theme): ?>
                                <?php $isDefault = ((string)$theme->slug === $defaultThemeSlug); ?>
                                <tr<?= $isDefault ? ' class="table-primary"' : '' ?>>
                                    <td>
                                        <strong><?= htmlspecialchars((string)$theme->name) ?></strong>
                                        <?php if ((bool)$theme->is_dark): ?>
                                            <span class="badge bg-dark">Dark</span>
                                        <?php endif; ?>
                                        <?php if ($isDefault): ?>
                                            <span class="badge bg-warning text-dark">Default</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><code><?= htmlspecialchars((string)$theme->slug) ?></code></td>
                                    <td><span class="badge bg-primary">Custom</span></td>
                                    <td>
                                        <?php if ((bool)$theme->is_active): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if ((bool)$theme->is_active && !$isDefault): ?>
                                            <form method="post" action="<?= Utils::url('/admin/themes/set-default') ?>" class="d-inline">
                                                <?= Csrf::field() ?>
                                                <input type="hidden" name="slug" value="<?= htmlspecialchars((string)$theme->slug) ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-primary">Set Default</button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="post" action="<?= Utils::url('/admin/themes/toggle') ?>" class="d-inline">
                                            <?= Csrf::field() ?>
                                            <input type="hidden" name="id" value="<?= (int)$theme->getId() ?>">
                                            <button type="submit" class="btn btn-sm <?= (bool)$theme->is_active ? 'btn-outline-warning' : 'btn-outline-success' ?>"<?= $isDefault ? ' disabled title="Cannot disable default theme"' : '' ?>>
                                                <?= (bool)$theme->is_active ? 'Disable' : 'Enable' ?>
                                            </button>
                                        </form>
                                        <form method="post" action="<?= Utils::url('/admin/themes/delete') ?>" class="d-inline" onsubmit="return confirm('Delete this theme?')">
                                            <?= Csrf::field() ?>
                                            <input type="hidden" name="id" value="<?= (int)$theme->getId() ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger"<?= $isDefault ? ' disabled title="Cannot delete default theme"' : '' ?>>Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Upload Theme Modal -->
<div class="modal fade" id="uploadThemeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="<?= Utils::url('/admin/themes/upload') ?>" enctype="multipart/form-data">
                <?= Csrf::field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Upload Custom Theme</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Theme Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required placeholder="My Custom Theme">
                    </div>
                    <div class="mb-3">
                        <label for="css_file" class="form-label">CSS File</label>
                        <input type="file" class="form-control" id="css_file" name="css_file" accept=".css,text/css">
                        <div class="form-text">Upload a .css file, or paste CSS content below.</div>
                    </div>
                    <div class="mb-3">
                        <label for="css_content" class="form-label">Or Paste CSS Content</label>
                        <textarea class="form-control font-monospace" id="css_content" name="css_content" rows="10" placeholder="/* Paste your CSS here */"></textarea>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="is_dark" name="is_dark">
                        <label class="form-check-label" for="is_dark">This is a dark theme</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload Theme</button>
                </div>
            </form>
        </div>
    </div>
</div>
