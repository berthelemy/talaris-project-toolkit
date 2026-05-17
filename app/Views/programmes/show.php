<?php

/**
 * View template for programmes: show.
 */
$pageTitle = (string) lang('Domain.programmeDetailsTitle');
$active = 'programmes';
?>
<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
    <?php $canOpenHelloModule = (bool) ($canOpenHelloModule ?? false); ?>
    <?php $widgets = (string) ($widgets ?? ''); ?>
    <?php $canManageWidgetLayout = (bool) ($canManageWidgetLayout ?? false); ?>
    <?php if (session('error') !== null): ?>
        <div class="alert alert-danger" role="alert"><?= esc((string) session('error')) ?></div>
    <?php endif; ?>
    <?php if (session('success') !== null): ?>
        <div class="alert alert-success" role="alert"><?= esc((string) session('success')) ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h2 class="h5 mb-3"><?= esc((string) ($programme['name'] ?? '')) ?></h2>
            <div class="mb-2">
                <span class="text-muted"><?= esc(lang('Domain.programmeDescription')) ?>:</span>
                <div><?= esc((string) ($programme['description'] ?? '')) ?></div>
            </div>
            <div class="mb-2">
                <span class="text-muted"><?= esc(lang('Domain.programmeStatusCalculated')) ?>:</span>
                <span class="badge text-bg-secondary"><?= esc(lang('Domain.projectStatus_' . (string) ($programmeStatus ?? 'not_started'))) ?></span>
            </div>
            <div class="text-muted small">
                <?= esc(lang('Domain.programmeCreatedAt')) ?>: <?= esc((string) ($programme['created_at'] ?? '')) ?>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <h3 class="h6 mb-1"><?= esc(lang('Module.programmeHelloWorldTitle')) ?></h3>
                <p class="mb-0 text-muted"><?= esc(lang('Module.programmeHelloWorldDescription')) ?></p>
            </div>
            <?php if ($canOpenHelloModule): ?>
                <a class="btn btn-primary" href="<?= site_url('programmes/' . (int) ($programme['id'] ?? 0) . '/modules/hello-world') ?>"><?= esc(lang('Module.openProgrammeModuleButton')) ?></a>
            <?php else: ?>
                <span class="badge text-bg-secondary align-self-start align-self-md-center"><?= esc(lang('Module.statusDisabled')) ?></span>
            <?php endif; ?>
        </div>
    </div>

    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-sm-end mb-2">
        <?php if ($canManageWidgetLayout): ?>
            <a class="btn btn-outline-secondary btn-sm" href="<?= site_url('programmes/' . (int) ($programme['id'] ?? 0) . '/widgets/layout') ?>"><?= esc(lang('Module.programmeLayoutManageWidgets')) ?></a>
        <?php endif; ?>
    </div>

    <?php if ($widgets !== ''): ?>
        <div class="mb-4">
            <?= $widgets ?>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h3 class="h6 mb-3"><?= esc(lang('Domain.linkedProjectsTitle')) ?></h3>
            <?php if (empty($linkedProjects)): ?>
                <p class="text-muted mb-0"><?= esc(lang('Domain.noLinkedProjects')) ?></p>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($linkedProjects as $linkedProject): ?>
                        <?php $projectStatus = (string) ($linkedProject['status'] ?? 'not_started'); ?>
                        <div class="col-12 col-lg-6">
                            <div class="card h-100 border">
                                <a href="<?= site_url('projects/' . (int) ($linkedProject['id'] ?? 0)) ?>" class="stretched-link" aria-label="<?= esc((string) ($linkedProject['name'] ?? '')) ?>"></a>
                                <div class="card-body position-relative">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <h4 class="h6 mb-0"><?= esc((string) ($linkedProject['name'] ?? '')) ?></h4>
                                        <span class="badge text-bg-secondary"><?= esc(lang('Domain.projectStatus_' . $projectStatus)) ?></span>
                                    </div>
                                    <p class="text-muted mb-0"><?= esc((string) ($linkedProject['description'] ?? '')) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?= $this->endSection() ?>
