<!doctype html>
<?php $locale = (string) service('request')->getLocale(); ?>
<?php
/**
 * @var array<string, mixed> $project
 * @var string $moduleRouteSegment
 * @var string $moduleTitleKey
 * @var string $moduleDescriptionKey
 * @var list<array<string, mixed>> $entries
 * @var list<array{id:int,username:string}> $owners
 * @var list<string> $statusOptions
 * @var list<string> $priorityOptions
 * @var list<string> $riskScaleOptions
 * @var array{q:string,status:string,owner_user_id:int,sort:string} $filters
 * @var bool $isReadOnly
 * @var bool $isRiskModule
 * @var bool $isAssumptionModule
 * @var bool $isDecisionModule
 * @var string $backUrl
 */
?>
<html lang="<?= esc($locale) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc((string) lang($moduleTitleKey)) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <?= view('layouts/theme_assets') ?>
</head>
<body class="bg-light">
<?= view('layouts/app_header', ['pageTitle' => (string) lang($moduleTitleKey), 'active' => 'projects']) ?>
<main class="container py-4">
    <div class="mb-3">
        <a class="btn btn-outline-secondary btn-sm" href="<?= site_url($backUrl) ?>"><?= esc(lang('Module.backToProject')) ?></a>
    </div>

    <?php if (session('error') !== null): ?>
        <div class="alert alert-danger" role="alert"><?= esc((string) session('error')) ?></div>
    <?php endif; ?>
    <?php if (session('success') !== null): ?>
        <div class="alert alert-success" role="alert"><?= esc((string) session('success')) ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h2 class="h5 mb-2"><?= esc((string) ($project['name'] ?? '')) ?> - <?= esc((string) lang($moduleTitleKey)) ?></h2>
            <p class="mb-0 text-muted"><?= esc((string) lang($moduleDescriptionKey)) ?></p>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="get" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label" for="q"><?= esc(lang('Module.raidFilterSearchLabel')) ?></label>
                    <input id="q" name="q" type="text" value="<?= esc((string) ($filters['q'] ?? '')) ?>" class="form-control" placeholder="<?= esc(lang('Module.raidFilterSearchPlaceholder')) ?>">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label" for="status"><?= esc(lang('Module.raidColumnStatus')) ?></label>
                    <select id="status" name="status" class="form-select">
                        <option value=""><?= esc(lang('Module.raidFilterAllStatuses')) ?></option>
                        <?php foreach ($statusOptions as $statusOption): ?>
                            <option value="<?= esc($statusOption) ?>" <?= (string) ($filters['status'] ?? '') === $statusOption ? 'selected' : '' ?>><?= esc((string) lang('Module.raidStatus' . ucfirst($statusOption))) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label" for="owner_user_id"><?= esc(lang('Module.raidColumnOwner')) ?></label>
                    <select id="owner_user_id" name="owner_user_id" class="form-select">
                        <option value="0"><?= esc(lang('Module.raidFilterAllOwners')) ?></option>
                        <?php foreach ($owners as $owner): ?>
                            <option value="<?= (int) ($owner['id'] ?? 0) ?>" <?= (int) ($filters['owner_user_id'] ?? 0) === (int) ($owner['id'] ?? 0) ? 'selected' : '' ?>><?= esc((string) ($owner['username'] ?? '')) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label" for="sort"><?= esc(lang('Module.raidFilterSortLabel')) ?></label>
                    <select id="sort" name="sort" class="form-select">
                        <option value="updated_desc" <?= (string) ($filters['sort'] ?? 'updated_desc') === 'updated_desc' ? 'selected' : '' ?>><?= esc(lang('Module.raidSortUpdatedDesc')) ?></option>
                        <option value="target_asc" <?= (string) ($filters['sort'] ?? '') === 'target_asc' ? 'selected' : '' ?>><?= esc(lang('Module.raidSortTargetAsc')) ?></option>
                        <option value="priority_desc" <?= (string) ($filters['sort'] ?? '') === 'priority_desc' ? 'selected' : '' ?>><?= esc(lang('Module.raidSortPriorityDesc')) ?></option>
                        <option value="status_asc" <?= (string) ($filters['sort'] ?? '') === 'status_asc' ? 'selected' : '' ?>><?= esc(lang('Module.raidSortStatusAsc')) ?></option>
                    </select>
                </div>
                <div class="col-12 col-md-1 d-grid">
                    <button class="btn btn-outline-secondary" type="submit"><?= esc(lang('Module.raidFilterApplyButton')) ?></button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($isReadOnly): ?>
        <div class="alert alert-info" role="status"><?= esc(lang('Module.readOnlyNotice')) ?></div>
    <?php endif; ?>

    <?php if (! $isReadOnly): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h3 class="h6 mb-3"><?= esc(lang('Module.raidCreateTitle')) ?></h3>
                <form method="post" action="<?= site_url('projects/' . (int) ($project['id'] ?? 0) . '/modules/' . $moduleRouteSegment) ?>" class="row g-2">
                    <?= csrf_field() ?>

                    <div class="col-12 col-md-4">
                        <label class="form-label" for="title"><?= esc($isDecisionModule ? lang('Module.decisionsDescriptionLabel') : lang('Module.raidColumnTitle')) ?></label>
                        <?php if ($isDecisionModule): ?>
                            <textarea id="title" name="description" rows="2" class="form-control" required></textarea>
                        <?php else: ?>
                            <input id="title" name="title" type="text" class="form-control" maxlength="200" required>
                        <?php endif; ?>
                    </div>

                    <div class="col-12 col-md-4 <?= $isDecisionModule ? 'd-none' : '' ?>">
                        <label class="form-label" for="owner_user_id_create"><?= esc(lang('Module.raidColumnOwner')) ?></label>
                        <select id="owner_user_id_create" name="owner_user_id" class="form-select" <?= $isDecisionModule ? '' : 'required' ?>>
                            <?php foreach ($owners as $owner): ?>
                                <option value="<?= (int) ($owner['id'] ?? 0) ?>"><?= esc((string) ($owner['username'] ?? '')) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-6 col-md-2 <?= $isDecisionModule ? 'd-none' : '' ?>">
                        <label class="form-label" for="status_create"><?= esc(lang('Module.raidColumnStatus')) ?></label>
                        <select id="status_create" name="status" class="form-select" <?= $isDecisionModule ? '' : 'required' ?>>
                            <?php foreach ($statusOptions as $statusOption): ?>
                                <option value="<?= esc($statusOption) ?>"><?= esc((string) lang('Module.raidStatus' . ucfirst($statusOption))) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-6 col-md-2 <?= $isDecisionModule ? 'd-none' : '' ?>">
                        <label class="form-label" for="priority_create"><?= esc(lang('Module.raidColumnPriority')) ?></label>
                        <?php if ($isRiskModule): ?>
                            <input id="priority_create" name="priority" type="text" class="form-control" value="<?= esc((string) lang('Module.raidPriorityMedium')) ?>" readonly>
                        <?php else: ?>
                            <select id="priority_create" name="priority" class="form-select" required>
                                <?php foreach ($priorityOptions as $priorityOption): ?>
                                    <option value="<?= esc($priorityOption) ?>"><?= esc((string) lang('Module.raidPriority' . ucfirst($priorityOption))) ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>

                    <div class="col-12 <?= $isDecisionModule ? 'd-none' : '' ?>">
                        <label class="form-label" for="description"><?= esc(lang('Module.raidColumnDescription')) ?></label>
                        <textarea id="description" name="description" rows="2" class="form-control"></textarea>
                    </div>

                    <?php if ($isRiskModule): ?>
                        <div class="col-12">
                            <label class="form-label" for="mitigation_actions"><?= esc(lang('Module.raidColumnMitigationActions')) ?></label>
                            <textarea id="mitigation_actions" name="mitigation_actions" rows="2" class="form-control"></textarea>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="impact"><?= esc(lang('Module.raidColumnImpact')) ?></label>
                            <select id="impact" name="impact" class="form-select" required>
                                <?php foreach ($riskScaleOptions as $option): ?>
                                    <option value="<?= esc($option) ?>"><?= esc((string) lang('Module.riskImpact' . ucfirst($option))) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="likelihood"><?= esc(lang('Module.raidColumnLikelihood')) ?></label>
                            <select id="likelihood" name="likelihood" class="form-select" required>
                                <?php foreach ($riskScaleOptions as $option): ?>
                                    <option value="<?= esc($option) ?>"><?= esc((string) lang('Module.riskLikelihood' . ucfirst($option))) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <?php if ($isAssumptionModule): ?>
                        <div class="col-12">
                            <label class="form-label" for="impact_if_not_valid"><?= esc(lang('Module.raidColumnImpactIfNotValid')) ?></label>
                            <textarea id="impact_if_not_valid" name="impact_if_not_valid" rows="2" class="form-control"></textarea>
                        </div>
                    <?php endif; ?>

                    <div class="col-6 col-md-3">
                        <label class="form-label" for="target_date"><?= esc(lang('Module.raidColumnTargetDate')) ?></label>
                        <input id="target_date" name="target_date" type="date" class="form-control">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label" for="review_date"><?= esc(lang('Module.raidColumnReviewDate')) ?></label>
                        <input id="review_date" name="review_date" type="date" class="form-control">
                    </div>

                    <?php if ($isDecisionModule): ?>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="decision_date"><?= esc(lang('Module.decisionsDateLabel')) ?></label>
                            <input id="decision_date" name="decision_date" type="date" class="form-control" required>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="made_by_user_id"><?= esc(lang('Module.decisionsMadeByLabel')) ?></label>
                            <select id="made_by_user_id" name="made_by_user_id" class="form-select" required>
                                <?php foreach ($owners as $owner): ?>
                                    <option value="<?= (int) ($owner['id'] ?? 0) ?>"><?= esc((string) ($owner['username'] ?? '')) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="col-12 col-md-6 d-grid">
                        <button class="btn btn-primary" type="submit"><?= esc(lang('Module.raidCreateButton')) ?></button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="p-3 border-bottom">
                <h3 class="h6 mb-0"><?= esc(lang('Module.entriesTitle')) ?></h3>
            </div>
            <?php if (empty($entries)): ?>
                <p class="text-muted p-4 mb-0"><?= esc(lang('Module.entriesNone')) ?></p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle js-datatable">
                        <thead class="table-light">
                        <tr>
                            <th><?= esc(lang('Module.raidColumnTitle')) ?></th>
                            <th><?= esc(lang('Module.raidColumnOwner')) ?></th>
                            <th><?= esc(lang('Module.raidColumnStatus')) ?></th>
                            <th><?= esc(lang('Module.raidColumnPriority')) ?></th>
                            <th class="d-none d-md-table-cell"><?= esc(lang('Module.raidColumnTargetDate')) ?></th>
                            <th class="d-none d-md-table-cell"><?= esc(lang('Module.raidColumnReviewDate')) ?></th>
                            <th class="d-none d-lg-table-cell"><?= esc(lang('Module.raidColumnUpdatedAt')) ?></th>
                            <?php if (! $isReadOnly): ?>
                                <th><?= esc(lang('Module.columnActions')) ?></th>
                            <?php endif; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($entries as $entry): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= esc((string) ($entry['title'] ?? '')) ?></div>
                                    <?php if ((string) ($entry['description'] ?? '') !== ''): ?>
                                        <div class="text-muted small"><?= esc((string) ($entry['description'] ?? '')) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc((string) ($entry['owner_username'] ?? '')) ?></td>
                                <td><?= esc((string) lang('Module.raidStatus' . ucfirst((string) ($entry['status'] ?? 'open')))) ?></td>
                                <td><?= esc((string) lang('Module.raidPriority' . ucfirst((string) ($entry['priority'] ?? 'medium')))) ?></td>
                                <td class="d-none d-md-table-cell"><?= esc((string) ($entry['target_date'] ?? '')) ?></td>
                                <td class="d-none d-md-table-cell"><?= esc((string) ($entry['review_date'] ?? '')) ?></td>
                                <td class="d-none d-lg-table-cell"><?= esc((string) ($entry['updated_at'] ?? '')) ?></td>
                                <?php if (! $isReadOnly): ?>
                                    <td>
                                        <details>
                                            <summary class="btn btn-sm btn-outline-secondary mb-2"><?= esc(lang('Module.raidEditButton')) ?></summary>
                                            <form method="post" action="<?= site_url('projects/' . (int) ($project['id'] ?? 0) . '/modules/' . $moduleRouteSegment . '/' . (int) ($entry['id'] ?? 0) . '/update') ?>" class="d-grid gap-2">
                                                <?= csrf_field() ?>
                                                <input name="title" type="text" class="form-control form-control-sm" maxlength="200" value="<?= esc((string) ($entry['title'] ?? '')) ?>">
                                                <textarea name="description" rows="2" class="form-control form-control-sm"><?= esc((string) ($entry['description'] ?? '')) ?></textarea>
                                                <button class="btn btn-sm btn-primary" type="submit"><?= esc(lang('Module.raidUpdateButton')) ?></button>
                                            </form>
                                            <?php if ((string) ($entry['status'] ?? '') !== 'closed' && ! $isDecisionModule): ?>
                                                <form method="post" action="<?= site_url('projects/' . (int) ($project['id'] ?? 0) . '/modules/' . $moduleRouteSegment . '/' . (int) ($entry['id'] ?? 0) . '/close') ?>" class="mt-2">
                                                    <?= csrf_field() ?>
                                                    <button class="btn btn-sm btn-outline-danger" type="submit"><?= esc(lang('Module.raidCloseButton')) ?></button>
                                                </form>
                                            <?php endif; ?>
                                        </details>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>
<?= view('layouts/datatable_assets') ?>
</body>
</html>
