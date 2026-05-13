<?php
/** @var array<string, mixed> $project */
$pageTitle = (string) lang('Domain.projectEditTitle');
$active = 'projects';
$mainClass = 'modal-edit-page';
?>
<?= $this->extend('layouts/base') ?>

<?= $this->section('extraHead') ?>
    <style>
        .modal-edit-page {
            min-height: calc(100vh - 82px);
            isolation: isolate;
        }

        .modal-edit-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(17, 24, 39, 0.35);
            backdrop-filter: blur(8px);
            z-index: 1;
        }

        .modal-edit-shell {
            position: relative;
            z-index: 2;
            padding-top: 2rem;
            padding-bottom: 2rem;
        }

        .modal-edit-card {
            border-radius: 1rem;
            box-shadow: 0 1rem 2.5rem rgba(15, 23, 42, 0.2);
        }
    </style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="modal-edit-backdrop" aria-hidden="true"></div>
    <div class="container modal-edit-shell">
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

    <div class="card border-0 modal-edit-card">
        <div class="card-body p-4 p-lg-5">
            <div class="row g-4">
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="post" action="<?= site_url('projects/' . esc((string) $project['id'])) ?>" novalidate>
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="name" class="form-label"><?= esc(lang('Domain.projectName')) ?> <span class="text-danger">*</span></label>
                            <input id="name" name="name" type="text" class="form-control" required maxlength="150"
                                   value="<?= esc((string) old('name', (string) ($project['name'] ?? ''))) ?>">
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label"><?= esc(lang('Domain.projectDescription')) ?></label>
                            <textarea id="description" name="description" class="form-control" rows="5" maxlength="5000"><?= esc((string) old('description', (string) ($project['description'] ?? ''))) ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label"><?= esc(lang('Domain.projectStatus')) ?></label>
                            <?php $statusValue = (string) old('status', (string) ($project['status'] ?? 'not_started')); ?>
                            <select id="status" name="status" class="form-select">
                                <?php foreach (['not_started', 'in_progress', 'on_track', 'at_risk', 'blocked', 'on_hold', 'completed', 'cancelled'] as $statusOption): ?>
                                    <option value="<?= esc($statusOption) ?>" <?= $statusValue === $statusOption ? 'selected' : '' ?>>
                                        <?= esc(lang('Domain.projectStatus_' . $statusOption)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><?= esc(lang('Domain.projectSaveButton')) ?></button>
                            <a href="<?= site_url('projects') ?>" class="btn btn-outline-secondary"><?= esc(lang('Domain.cancelButton')) ?></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="h6 mb-3"><?= esc(lang('Domain.projectLinkSectionTitle')) ?></h2>
                    <?php $linkedProgrammeIdsList = (array) ($linkedProgrammeIds ?? []); ?>
                    <?php $linkedProgrammes = array_values(array_filter(
                        (array) ($programmes ?? []),
                        static fn (array $programme): bool => in_array((int) ($programme['id'] ?? 0), $linkedProgrammeIdsList, true),
                    )); ?>

                    <h3 class="h6 text-muted mt-2 mb-2"><?= esc(lang('Domain.linkedProgrammesTitle')) ?></h3>
                    <?php if ($linkedProgrammes === []): ?>
                        <p class="text-muted small mb-3"><?= esc(lang('Domain.noLinkedProgrammes')) ?></p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush mb-3">
                            <?php foreach ($linkedProgrammes as $linkedProgramme): ?>
                                <?php $linkedProgrammeId = (int) ($linkedProgramme['id'] ?? 0); ?>
                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center gap-2">
                                    <span><?= esc((string) ($linkedProgramme['name'] ?? '')) ?></span>
                                    <form method="post" action="<?= site_url('programmes/' . $linkedProgrammeId . '/projects/' . (int) ($project['id'] ?? 0) . '/unlink') ?>">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-outline-danger btn-sm"><?= esc(lang('Domain.unlinkButton')) ?></button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <?php if (empty($programmes)): ?>
                        <p class="text-muted mb-0"><?= esc(lang('Domain.noProgrammesAvailable')) ?></p>
                    <?php else: ?>
                        <form method="post" action="#" id="link-programme-form" novalidate>
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label for="programme_id" class="form-label"><?= esc(lang('Domain.selectProgrammeLabel')) ?></label>
                                <select id="programme_id" name="programme_id" class="form-select" required>
                                    <option value=""><?= esc(lang('Domain.selectPlaceholder')) ?></option>
                                    <?php foreach ($programmes as $programme): ?>
                                        <?php $programmeId = (int) ($programme['id'] ?? 0); ?>
                                        <?php $isLinked = in_array($programmeId, (array) ($linkedProgrammeIds ?? []), true); ?>
                                        <option value="<?= $programmeId ?>" <?= $isLinked ? 'disabled' : '' ?>>
                                            <?= esc((string) ($programme['name'] ?? '')) ?><?= $isLinked ? ' (' . esc(lang('Domain.projectAlreadyLinked')) . ')' : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-outline-primary btn-sm"><?= esc(lang('Domain.linkToProgrammeButton')) ?></button>
                        </form>
                        <script>
                            (function () {
                                var form = document.getElementById('link-programme-form');
                                var select = document.getElementById('programme_id');
                                if (!form || !select) {
                                    return;
                                }

                                form.addEventListener('submit', function (event) {
                                    var programmeId = String(select.value || '').trim();
                                    if (programmeId === '') {
                                        event.preventDefault();
                                        return;
                                    }

                                    form.action = '<?= site_url('programmes') ?>/' + encodeURIComponent(programmeId) + '/projects/<?= (int) ($project['id'] ?? 0) ?>/link';
                                });
                            })();
                        </script>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card border-0 shadow-sm border-danger">
                <div class="card-body">
                    <h2 class="h6 text-danger mb-3"><?= esc(lang('Domain.projectDeleteButton')) ?></h2>
                    <p class="text-muted small"><?= esc(lang('Domain.projectDeleteConfirm')) ?></p>
                    <form method="post" action="<?= site_url('projects/' . esc((string) $project['id']) . '/delete') ?>"
                          onsubmit="return confirm(<?= json_encode(lang('Domain.projectDeleteConfirm')) ?>)">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-danger btn-sm"><?= esc(lang('Domain.projectDeleteButton')) ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
        </div>
    </div>
    </div>
<?= $this->endSection() ?>
