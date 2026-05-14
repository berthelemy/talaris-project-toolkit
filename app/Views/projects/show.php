<?php
$pageTitle = (string) lang('Domain.projectDetailsTitle');
$active = 'projects';
?>
<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
    <?php $canOpenHelloModule = (bool) ($canOpenHelloModule ?? false); ?>
    <?php $widgets = (string) ($widgets ?? ''); ?>
    <?php $canEditProject = (bool) ($canEditProject ?? false); ?>
    <?php if (session('error') !== null): ?>
        <div class="alert alert-danger" role="alert"><?= esc((string) session('error')) ?></div>
    <?php endif; ?>
    <?php if (session('success') !== null): ?>
        <div class="alert alert-success" role="alert"><?= esc((string) session('success')) ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-end mb-2">
        <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#project-side-panel" aria-expanded="true" aria-controls="project-side-panel">
            <?= esc(lang('Domain.projectModulesLabel')) ?>
        </button>
    </div>

    <div class="row g-3">
        <aside class="col-12 col-lg-2 collapse show" id="project-side-panel">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h6 mb-3"><?= esc((string) ($project['name'] ?? '')) ?></h2>
                    <nav aria-label="<?= esc(lang('Domain.projectModulesLabel')) ?>">
                        <ul class="nav nav-pills flex-column gap-1">
                            <li class="nav-item"><a class="nav-link active" href="<?= site_url('projects/' . (int) ($project['id'] ?? 0)) ?>"><?= esc(lang('Domain.overviewLabel')) ?></a></li>
                            <?php if ((bool) ($canManageWidgetLayout ?? false)): ?>
                                <li class="nav-item"><a class="nav-link" href="<?= site_url('projects/' . (int) ($project['id'] ?? 0) . '/widgets/layout') ?>"><?= esc(lang('Module.projectLayoutManageWidgets')) ?></a></li>
                            <?php endif; ?>
                            <?php foreach ((array) ($projectModules ?? []) as $module): ?>
                                <li class="nav-item"><a class="nav-link" href="<?= esc((string) ($module['url'] ?? '#')) ?>"><?= esc((string) ($module['name'] ?? '')) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </aside>

        <section class="col-12 col-lg-10">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <h2 class="h5 mb-0"><?= esc((string) ($project['name'] ?? '')) ?></h2>
                        <div class="d-flex flex-column align-items-end gap-2">
                            <span class="badge text-bg-secondary"><?= esc(lang('Domain.projectStatus_' . (string) ($project['status'] ?? 'not_started'))) ?></span>
                            <?php if ($canEditProject): ?>
                                <a class="btn btn-outline-primary btn-sm" href="<?= site_url('projects/' . (int) ($project['id'] ?? 0) . '/edit') ?>"><?= esc(lang('Domain.projectEditTitle')) ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="mb-2">
                        <span class="text-muted"><?= esc(lang('Domain.projectDescription')) ?>:</span>
                        <div><?= esc((string) ($project['description'] ?? '')) ?></div>
                    </div>
                    <div class="text-muted small">
                        <?= esc(lang('Domain.projectCreatedAt')) ?>: <?= esc((string) ($project['created_at'] ?? '')) ?>
                    </div>
                </div>
            </div>

            <?php if ($widgets !== ''): ?>
                <div class="mb-4">
                    <div class="row g-3">
                        <?= $widgets ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="p-3 border-bottom">
                        <h3 class="h6 mb-0"><?= esc(lang('Domain.linkedProgrammesTitle')) ?></h3>
                    </div>
                    <?php if (empty($linkedProgrammes)): ?>
                        <p class="text-muted p-4 mb-0"><?= esc(lang('Domain.noLinkedProgrammes')) ?></p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 js-datatable">
                                <thead class="table-light">
                                    <tr>
                                        <th><?= esc(lang('Domain.programmeName')) ?></th>
                                        <th class="d-none d-md-table-cell"><?= esc(lang('Domain.programmeDescription')) ?></th>
                                        <th class="d-none d-sm-table-cell"><?= esc(lang('Domain.programmeCreatedAt')) ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($linkedProgrammes as $linkedProgramme): ?>
                                        <tr>
                                            <td><a href="<?= site_url('programmes/' . (int) ($linkedProgramme['id'] ?? 0)) ?>"><?= esc((string) ($linkedProgramme['name'] ?? '')) ?></a></td>
                                            <td class="d-none d-md-table-cell text-muted"><?= esc((string) ($linkedProgramme['description'] ?? '')) ?></td>
                                            <td class="d-none d-sm-table-cell text-muted"><?= esc((string) ($linkedProgramme['created_at'] ?? '')) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
<?= $this->endSection() ?>

<?= $this->section('postMain') ?>
<?= view('layouts/datatable_assets') ?>
<?= $this->endSection() ?>
