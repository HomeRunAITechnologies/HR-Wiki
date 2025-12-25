<?php
/** @var string $title */
/** @var object $page */
/** @var string $slug */
/** @var object $oldVersion */
/** @var object $newVersion */
/** @var string $oldAuthorName */
/** @var string $newAuthorName */
/** @var bool $isLoggedIn */
use WikiApp\Lib\Utils;
?>
<div class="page-diff">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= htmlspecialchars($title) ?></h1>
        <div class="btn-group">
            <a href="<?= Utils::url('/history/' . htmlspecialchars($slug)) ?>" class="btn btn-outline-secondary">
                Back to History
            </a>
            <a href="<?= Utils::url('/' . htmlspecialchars($slug)) ?>" class="btn btn-outline-secondary">
                View Page
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <strong>Version <?= (int)$oldVersion->version_number ?></strong>
                    <span class="float-end">
                        <?= htmlspecialchars($oldAuthorName) ?> &bull;
                        <?= date('M j, Y g:i A', strtotime((string)$oldVersion->created_at)) ?>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <strong>Version <?= (int)$newVersion->version_number ?></strong>
                    <span class="float-end">
                        <?= htmlspecialchars($newAuthorName) ?> &bull;
                        <?= date('M j, Y g:i A', strtotime((string)$newVersion->created_at)) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Title Diff -->
    <?php if ((string)$oldVersion->title !== (string)$newVersion->title): ?>
        <div class="card mb-3">
            <div class="card-header">
                <strong>Title Changed</strong>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <del class="text-danger"><?= htmlspecialchars((string)$oldVersion->title) ?></del>
                    </div>
                    <div class="col-md-6">
                        <ins class="text-success"><?= htmlspecialchars((string)$newVersion->title) ?></ins>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- View Mode Toggle -->
    <div class="mb-3">
        <div class="btn-group" role="group">
            <input type="radio" class="btn-check" name="diffView" id="unifiedView" checked>
            <label class="btn btn-outline-primary" for="unifiedView">Unified View</label>
            <input type="radio" class="btn-check" name="diffView" id="splitView">
            <label class="btn btn-outline-primary" for="splitView">Side by Side</label>
        </div>
    </div>

    <!-- Unified Diff View -->
    <div id="unifiedDiff" class="card">
        <div class="card-header">
            <strong>Content Changes</strong>
        </div>
        <div class="card-body p-0">
            <pre id="diffOutput" class="m-0 p-3" style="white-space: pre-wrap; word-wrap: break-word; background: #f8f9fa;"></pre>
        </div>
    </div>

    <!-- Split Diff View (hidden by default) -->
    <div id="splitDiff" class="row" style="display: none;">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <strong>Old Version</strong>
                </div>
                <div class="card-body p-0">
                    <pre id="oldContent" class="m-0 p-3" style="white-space: pre-wrap; word-wrap: break-word; background: #fff5f5;"></pre>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <strong>New Version</strong>
                </div>
                <div class="card-body p-0">
                    <pre id="newContent" class="m-0 p-3" style="white-space: pre-wrap; word-wrap: break-word; background: #f0fff0;"></pre>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.diff-line { display: block; padding: 2px 5px; font-family: monospace; }
.diff-added { background-color: #e6ffec; color: #1a7f37; }
.diff-removed { background-color: #ffebe9; color: #cf222e; }
.diff-unchanged { background-color: transparent; color: #57606a; }
.diff-header { background-color: #f0f0f0; color: #333; font-weight: bold; }
ins { text-decoration: none; background-color: #acf2bd; }
del { text-decoration: none; background-color: #fdb8c0; }
</style>

<!-- jsdiff library -->
<script src="https://cdn.jsdelivr.net/npm/diff@5.1.0/dist/diff.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const oldText = <?= json_encode((string)$oldVersion->content) ?>;
    const newText = <?= json_encode((string)$newVersion->content) ?>;

    // Generate unified diff
    const diff = Diff.diffLines(oldText, newText);
    const diffOutput = document.getElementById('diffOutput');
    const fragment = document.createDocumentFragment();

    diff.forEach(function(part) {
        const span = document.createElement('span');
        span.className = 'diff-line ';

        if (part.added) {
            span.className += 'diff-added';
            span.textContent = '+ ' + part.value;
        } else if (part.removed) {
            span.className += 'diff-removed';
            span.textContent = '- ' + part.value;
        } else {
            span.className += 'diff-unchanged';
            span.textContent = '  ' + part.value;
        }

        fragment.appendChild(span);
    });

    diffOutput.appendChild(fragment);

    // Side by side view content
    document.getElementById('oldContent').textContent = oldText;
    document.getElementById('newContent').textContent = newText;

    // Toggle view mode
    document.getElementById('unifiedView').addEventListener('change', function() {
        document.getElementById('unifiedDiff').style.display = 'block';
        document.getElementById('splitDiff').style.display = 'none';
    });

    document.getElementById('splitView').addEventListener('change', function() {
        document.getElementById('unifiedDiff').style.display = 'none';
        document.getElementById('splitDiff').style.display = 'flex';
    });
});
</script>
