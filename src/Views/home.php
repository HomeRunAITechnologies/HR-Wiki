<?php
/** @var string $title */
/** @var array $allPages */
/** @var bool $isLoggedIn */
/** @var object|null $homePage */
/** @var string $homeContent */
use WikiApp\Models\Setting;
use WikiApp\Lib\Utils;

$siteName = Setting::get('site_name', 'HR-Wiki');
?>
<div class="row">
    <!-- Table of Contents - Left Sidebar (25%) -->
    <div class="col-lg-3 col-md-4">
        <div class="card sticky-top" style="top: 80px;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Table of Contents</strong>
                <?php if ($isLoggedIn): ?>
                    <a href="<?= Utils::url('/create') ?>" class="btn btn-success btn-sm" title="New Page">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2Z"/>
                        </svg>
                    </a>
                <?php endif; ?>
            </div>
            <div class="list-group list-group-flush" style="max-height: calc(100vh - 180px); overflow-y: auto;">
                <?php if (!empty($allPages)): ?>
                    <?php foreach ($allPages as $page): ?>
                        <?php if ((string)$page->visibility === 'public' || $isLoggedIn): ?>
                            <a href="<?= Utils::url('/' . htmlspecialchars((string)$page->slug)) ?>"
                               class="list-group-item list-group-item-action <?= ((string)$page->slug === 'home') ? 'active' : '' ?>">
                                <?= htmlspecialchars((string)$page->title) ?>
                                <?php if ((string)$page->visibility === 'private'): ?>
                                    <span class="badge bg-secondary float-end">Private</span>
                                <?php endif; ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="list-group-item text-muted">No pages yet</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Content Area (75%) -->
    <div class="col-lg-9 col-md-8">
        <!-- Action Buttons -->
        <div class="float-end btn-group mb-3">
            <?php if (isset($homePage) && $homePage): ?>
                <a href="<?= Utils::url('/history/home') ?>" class="btn btn-outline-secondary btn-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-clock-history" viewBox="0 0 16 16">
                        <path d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022l-.074.997zm2.004.45a7.003 7.003 0 0 0-.985-.299l.219-.976c.383.086.76.2 1.126.342l-.36.933zm1.37.71a7.01 7.01 0 0 0-.439-.27l.493-.87a8.025 8.025 0 0 1 .979.654l-.615.789a6.996 6.996 0 0 0-.418-.302zm1.834 1.79a6.99 6.99 0 0 0-.653-.796l.724-.69c.27.285.52.59.747.91l-.818.576zm.744 1.352a7.08 7.08 0 0 0-.214-.468l.893-.45a7.976 7.976 0 0 1 .45 1.088l-.95.313a7.023 7.023 0 0 0-.179-.483zm.53 2.507a6.991 6.991 0 0 0-.1-1.025l.985-.17c.067.386.106.778.116 1.17l-1 .025zm-.131 1.538c.033-.17.06-.339.081-.51l.993.123a7.957 7.957 0 0 1-.23 1.155l-.964-.267c.046-.165.086-.332.12-.501zm-.952 2.379c.184-.29.346-.594.486-.908l.914.405c-.16.36-.345.706-.555 1.038l-.845-.535zm-.964 1.205c.122-.122.239-.248.35-.378l.758.653a8.073 8.073 0 0 1-.401.432l-.707-.707z"/>
                        <path d="M8 1a7 7 0 1 0 4.95 11.95l.707.707A8.001 8.001 0 1 1 8 0v1z"/>
                        <path d="M7.5 3a.5.5 0 0 1 .5.5v5.21l3.248 1.856a.5.5 0 0 1-.496.868l-3.5-2A.5.5 0 0 1 7 9V3.5a.5.5 0 0 1 .5-.5z"/>
                    </svg>
                    History
                </a>
            <?php endif; ?>
            <?php if ($isLoggedIn): ?>
                <?php if (isset($homePage) && $homePage): ?>
                    <a href="<?= Utils::url('/edit/home') ?>" class="btn btn-primary btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
                            <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/>
                        </svg>
                        Edit
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Page Content -->
        <?php if (!empty($homeContent)): ?>
            <div class="page-content">
                <?= $homeContent ?>
            </div>
        <?php else: ?>
            <div class="p-5 mb-4 bg-light rounded-3">
                <div class="container-fluid py-5">
                    <h1 class="display-5 fw-bold">Welcome to <?= htmlspecialchars($siteName) ?></h1>
                    <p class="col-md-10 fs-4">This is the central hub for all documentation and knowledge. Use the table of contents on the left to navigate, or use the search bar above.</p>
                    <?php if ($isLoggedIn && (!isset($homePage) || !$homePage)): ?>
                        <a href="<?= Utils::url('/create/home') ?>" class="btn btn-primary btn-lg">Create Home Page</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>


