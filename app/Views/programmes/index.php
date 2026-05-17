<?php

/**
 * File documentation for app/Views/programmes/index.php.
 */
$pageTitle = (string) lang('Domain.programmesTitle');
$active = 'programmes';
?>
<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
    <?php if (session('error') !== null): ?>
        <div class="alert alert-danger" role="alert"><?= esc((string) session('error')) ?></div>
    <?php endif; ?>
    <?php if (session('success') !== null): ?>
        <div class="alert alert-success" role="alert"><?= esc((string) session('success')) ?></div>
    <?php endif; ?>
    <?php if (session('errors') !== null): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0 ps-3">
                <?php foreach ((array) session('errors') as $err): ?>
                    <li><?= esc((string) $err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ((bool) ($canCreate ?? false)): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h2 class="h6 mb-3"><?= esc(lang('Domain.programmeCreateButton')) ?></h2>
            <form method="post" action="<?= site_url('programmes') ?>" novalidate>
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <input type="text" name="name" class="form-control" placeholder="<?= esc(lang('Domain.programmeName')) ?>" required maxlength="150" value="<?= esc((string) old('name', '')) ?>">
                    </div>
                    <div class="col-12 col-md-6">
                        <input type="text" name="description" class="form-control" placeholder="<?= esc(lang('Domain.programmeDescription')) ?>" maxlength="5000" value="<?= esc((string) old('description', '')) ?>">
                    </div>
                    <div class="col-12 col-md-2">
                        <button type="submit" class="btn btn-primary w-100"><?= esc(lang('Domain.programmeCreateButton')) ?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <?php if (empty($programmes)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 text-muted"><?= esc(lang('Domain.programmesNone')) ?></div>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($programmes as $programme): ?>
                <?php $status = (string) ($programme['calculated_status'] ?? 'not_started'); ?>
                <div class="col-12 col-lg-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <a href="<?= site_url('programmes/' . (int) ($programme['id'] ?? 0)) ?>" class="stretched-link" aria-label="<?= esc((string) ($programme['name'] ?? '')) ?>"></a>
                        <div class="card-body position-relative">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <h2 class="h5 mb-0"><?= esc((string) ($programme['name'] ?? '')) ?></h2>
                                <span class="badge text-bg-secondary"><?= esc(lang('Domain.projectStatus_' . $status)) ?></span>
                            </div>
                            <p class="text-muted mb-3"><?= esc((string) ($programme['description'] ?? '')) ?></p>
                            <div class="small text-muted d-flex justify-content-between align-items-center">
                                <span><?= esc(lang('Domain.programmeCreatedAt')) ?>: <?= esc((string) ($programme['created_at'] ?? '')) ?></span>
                                <a href="<?= site_url('programmes/' . (int) ($programme['id'] ?? 0) . '/edit') ?>" class="btn btn-outline-primary btn-sm position-relative app-z-index-2"><?= esc(lang('Domain.programmeEditTitle')) ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?= $this->endSection() ?>
