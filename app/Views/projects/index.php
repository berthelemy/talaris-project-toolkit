<?php
$pageTitle = (string) lang('Domain.projectsTitle');
$active = 'projects';
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
            <h2 class="h6 mb-3"><?= esc(lang('Domain.projectCreateButton')) ?></h2>
            <form method="post" action="<?= site_url('projects') ?>" novalidate>
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <input type="text" name="name" class="form-control" placeholder="<?= esc(lang('Domain.projectName')) ?>" required maxlength="150" value="<?= esc((string) old('name', '')) ?>">
                    </div>
                    <div class="col-12 col-md-6">
                        <input type="text" name="description" class="form-control" placeholder="<?= esc(lang('Domain.projectDescription')) ?>" maxlength="5000" value="<?= esc((string) old('description', '')) ?>">
                    </div>
                    <div class="col-12 col-md-2">
                        <select name="status" class="form-select" aria-label="<?= esc(lang('Domain.projectStatus')) ?>">
                            <?php foreach (['not_started', 'in_progress', 'on_track', 'at_risk', 'blocked', 'on_hold', 'completed', 'cancelled'] as $statusOption): ?>
                                <option value="<?= esc($statusOption) ?>" <?= old('status', 'not_started') === $statusOption ? 'selected' : '' ?>>
                                    <?= esc(lang('Domain.projectStatus_' . $statusOption)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-2">
                        <button type="submit" class="btn btn-primary w-100"><?= esc(lang('Domain.projectCreateButton')) ?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="get" action="<?= site_url('projects') ?>" class="row g-3 align-items-end">
                <div class="col-12 col-md-6">
                    <label for="programme_id" class="form-label mb-1"><?= esc(lang('Domain.projectsFilterProgrammeLabel')) ?></label>
                    <select id="programme_id" name="programme_id" class="form-select">
                        <option value="" <?= (string) ($selectedProgrammeId ?? '') === '' ? 'selected' : '' ?>><?= esc(lang('Domain.projectsFilterAllProgrammes')) ?></option>
                        <option value="none" <?= (string) ($selectedProgrammeId ?? '') === 'none' ? 'selected' : '' ?>><?= esc(lang('Domain.projectsFilterNoProgramme')) ?></option>
                        <?php foreach ((array) ($programmes ?? []) as $programme): ?>
                            <?php $programmeId = (string) ($programme['id'] ?? ''); ?>
                            <option value="<?= esc($programmeId) ?>" <?= (string) ($selectedProgrammeId ?? '') === $programmeId ? 'selected' : '' ?>>
                                <?= esc((string) ($programme['name'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-auto">
                    <button type="submit" class="btn btn-primary"><?= esc(lang('Module.raidFilterApplyButton')) ?></button>
                </div>
            </form>
        </div>
    </div>

    <?php if (empty($projects)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 text-muted"><?= esc(lang('Domain.projectsNone')) ?></div>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($projects as $project): ?>
                <?php $status = (string) ($project['status'] ?? 'not_started'); ?>
                <?php $projectId = (int) ($project['id'] ?? 0); ?>
                <?php $linkedProgrammes = (array) (($linkedProgrammesByProject ?? [])[$projectId] ?? []); ?>
                <div class="col-12 col-lg-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <a href="<?= site_url('projects/' . $projectId) ?>" class="stretched-link" aria-label="<?= esc((string) ($project['name'] ?? '')) ?>"></a>
                        <div class="card-body position-relative">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <h2 class="h5 mb-0"><?= esc((string) ($project['name'] ?? '')) ?></h2>
                                <span class="badge text-bg-secondary"><?= esc(lang('Domain.projectStatus_' . $status)) ?></span>
                            </div>
                            <p class="text-muted mb-2"><?= esc((string) ($project['description'] ?? '')) ?></p>
                            <p class="small text-muted mb-3">
                                <?php if ($linkedProgrammes === []): ?>
                                    <?= esc(lang('Domain.projectsFilterNoProgramme')) ?>
                                <?php else: ?>
                                    <?= esc(implode(', ', array_map(static fn (array $item): string => (string) ($item['name'] ?? ''), $linkedProgrammes))) ?>
                                <?php endif; ?>
                            </p>
                            <div class="small text-muted d-flex justify-content-between align-items-center">
                                <span><?= esc(lang('Domain.projectCreatedAt')) ?>: <?= esc((string) ($project['created_at'] ?? '')) ?></span>
                                <a href="<?= site_url('projects/' . $projectId . '/edit') ?>" class="btn btn-outline-primary btn-sm position-relative app-z-index-2"><?= esc(lang('Domain.projectEditTitle')) ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?= $this->endSection() ?>
