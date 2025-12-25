<?php
/** @var string $title */
/** @var object $page */
/** @var string $slug */
/** @var array $versions */
/** @var bool $isLoggedIn */
use WikiApp\Lib\Utils;
?>
<div class="page-history">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= htmlspecialchars($title) ?></h1>
        <a href="<?= Utils::url('/' . htmlspecialchars($slug)) ?>" class="btn btn-outline-secondary">
            Back to Page
        </a>
    </div>

    <?php if (empty($versions)): ?>
        <div class="alert alert-info">No version history available for this page.</div>
    <?php else: ?>
        <form id="diffForm" action="" method="get">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Version History (<?= count($versions) ?> versions)</span>
                    <button type="button" class="btn btn-primary btn-sm" id="compareBtn" disabled>
                        Compare Selected Versions
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">Old</th>
                                <th style="width: 50px;">New</th>
                                <th>Version</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($versions as $index => $data):
                                $version = $data['version'];
                                $authorName = $data['author_name'];
                            ?>
                                <tr>
                                    <td>
                                        <input type="radio" name="oldVersion" value="<?= (int)$version->version_number ?>"
                                               class="form-check-input version-radio"
                                               <?= $index === 1 ? 'checked' : '' ?>>
                                    </td>
                                    <td>
                                        <input type="radio" name="newVersion" value="<?= (int)$version->version_number ?>"
                                               class="form-check-input version-radio"
                                               <?= $index === 0 ? 'checked' : '' ?>>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">v<?= (int)$version->version_number ?></span>
                                        <?php if ($index === 0): ?>
                                            <span class="badge bg-success">Current</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars((string)$version->title) ?></td>
                                    <td><?= htmlspecialchars($authorName) ?></td>
                                    <td>
                                        <small class="text-muted">
                                            <?= date('M j, Y g:i A', strtotime((string)$version->created_at)) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($index > 0): ?>
                                            <a href="<?= Utils::url('/diff/' . htmlspecialchars($slug) . '/' . ((int)$version->version_number - 1) . '/' . (int)$version->version_number) ?>"
                                               class="btn btn-sm btn-outline-primary">
                                                View Changes
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const radios = document.querySelectorAll('.version-radio');
    const compareBtn = document.getElementById('compareBtn');
    const slug = '<?= htmlspecialchars($slug) ?>';

    function updateCompareButton() {
        const oldVersion = document.querySelector('input[name="oldVersion"]:checked');
        const newVersion = document.querySelector('input[name="newVersion"]:checked');

        if (oldVersion && newVersion && oldVersion.value !== newVersion.value) {
            compareBtn.disabled = false;
        } else {
            compareBtn.disabled = true;
        }
    }

    radios.forEach(function(radio) {
        radio.addEventListener('change', updateCompareButton);
    });

    compareBtn.addEventListener('click', function() {
        const oldVersion = document.querySelector('input[name="oldVersion"]:checked').value;
        const newVersion = document.querySelector('input[name="newVersion"]:checked').value;

        // Ensure old < new for consistency
        const [v1, v2] = [parseInt(oldVersion), parseInt(newVersion)].sort((a, b) => a - b);

        window.location.href = '<?= Utils::url('/diff/') ?>' + slug + '/' + v1 + '/' + v2;
    });

    updateCompareButton();
});
</script>
