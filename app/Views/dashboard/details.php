<?php
/** @var string $scopeType */
/** @var int $scopeId */
/** @var string $scopeName */
/** @var array<string, string> $moduleOptions */
/** @var array{module:string,status:string,priority:string,q:string} $filters */
/** @var list<array{id:int,module_slug:string,module_name:string,title:string,owner_username:string,status:string,priority:string,updated_at:string,source_url:string}> $records */

$pageTitle = (string) lang($scopeType === 'project' ? 'Module.projectDashboardDetailsTitle' : 'Module.programmeDashboardDetailsTitle');
$active = $scopeType === 'project' ? 'projects' : 'programmes';
$backUrl = $scopeType === 'project' ? 'projects/' . $scopeId : 'programmes/' . $scopeId;
$showStatusPriority = $scopeType === 'project';
?>
<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
    <div class="mb-3">
        <a class="btn btn-outline-secondary btn-sm" href="<?= site_url($backUrl) ?>"><?= esc($scopeType === 'project' ? lang('Module.backToProject') : lang('Module.backToProgramme')) ?></a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h2 class="h5 mb-1"><?= esc($pageTitle) ?></h2>
            <p class="text-muted mb-0"><?= esc(lang($scopeType === 'project' ? 'Module.projectDashboardDetailsSubtitle' : 'Module.programmeDashboardDetailsSubtitle', [$scopeName])) ?></p>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="get" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label" for="module"><?= esc(lang('Module.dashboardFilterModule')) ?></label>
                    <select id="module" name="module" class="form-select">
                        <option value=""><?= esc(lang('Module.dashboardFilterAllModules')) ?></option>
                        <?php foreach ($moduleOptions as $slug => $name): ?>
                            <option value="<?= esc($slug) ?>" <?= $filters['module'] === $slug ? 'selected' : '' ?>><?= esc($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($showStatusPriority): ?>
                    <div class="col-6 col-md-2">
                        <label class="form-label" for="status"><?= esc(lang('Module.dashboardFilterStatus')) ?></label>
                        <select id="status" name="status" class="form-select">
                            <option value=""><?= esc(lang('Module.dashboardFilterAllStatuses')) ?></option>
                            <?php foreach (['open', 'in_review', 'closed'] as $statusOption): ?>
                                <option value="<?= esc($statusOption) ?>" <?= $filters['status'] === $statusOption ? 'selected' : '' ?>><?= esc(lang('Module.raidStatus' . ucfirst($statusOption))) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label" for="priority"><?= esc(lang('Module.dashboardFilterPriority')) ?></label>
                        <select id="priority" name="priority" class="form-select">
                            <option value=""><?= esc(lang('Module.dashboardFilterAllPriorities')) ?></option>
                            <?php foreach (['low', 'medium', 'high', 'critical'] as $priorityOption): ?>
                                <option value="<?= esc($priorityOption) ?>" <?= $filters['priority'] === $priorityOption ? 'selected' : '' ?>><?= esc(lang('Module.raidPriority' . ucfirst($priorityOption))) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
                <div class="col-12 col-md-<?= $showStatusPriority ? '3' : '6' ?>">
                    <label class="form-label" for="q"><?= esc(lang('Module.raidFilterSearchLabel')) ?></label>
                    <input id="q" name="q" type="text" class="form-control" value="<?= esc((string) ($filters['q'] ?? '')) ?>" placeholder="<?= esc(lang('Module.raidFilterSearchPlaceholder')) ?>">
                </div>
                <div class="col-12 col-md-1 d-grid">
                    <button class="btn btn-outline-secondary" type="submit"><?= esc(lang('Module.dashboardFilterApply')) ?></button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="p-3 border-bottom">
                <h3 class="h6 mb-0"><?= esc(lang('Module.entriesTitle')) ?></h3>
            </div>

            <?php if ($records === []): ?>
                <p class="text-muted p-4 mb-0"><?= esc(lang('Module.dashboardNoRecords')) ?></p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 js-datatable">
                        <thead class="table-light">
                            <tr>
                                <th><?= esc(lang('Module.dashboardColumnModule')) ?></th>
                                <th><?= esc(lang('Module.dashboardColumnRecord')) ?></th>
                                <?php if ($showStatusPriority): ?>
                                    <th><?= esc(lang('Module.dashboardColumnOwner')) ?></th>
                                    <th><?= esc(lang('Module.dashboardColumnStatus')) ?></th>
                                    <th><?= esc(lang('Module.dashboardColumnPriority')) ?></th>
                                <?php endif; ?>
                                <th><?= esc(lang('Module.dashboardColumnUpdated')) ?></th>
                                <th><?= esc(lang('Module.dashboardColumnSource')) ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $record): ?>
                                <tr>
                                    <td><?= esc((string) ($record['module_name'] ?? '')) ?></td>
                                    <td><?= esc((string) ($record['title'] ?? '')) ?></td>
                                    <?php if ($showStatusPriority): ?>
                                        <td><?= esc((string) ($record['owner_username'] ?? '')) ?></td>
                                        <td><?= esc((string) lang('Module.raidStatus' . ucfirst((string) ($record['status'] ?? 'open')))) ?></td>
                                        <td><?= esc((string) lang('Module.raidPriority' . ucfirst((string) ($record['priority'] ?? 'medium')))) ?></td>
                                    <?php endif; ?>
                                    <td><?= esc((string) ($record['updated_at'] ?? '')) ?></td>
                                    <td>
                                        <?php if ((string) ($record['source_url'] ?? '') !== ''): ?>
                                            <a class="btn btn-outline-primary btn-sm" href="<?= esc((string) $record['source_url']) ?>"><?= esc(lang('Module.dashboardSourceOpenRecord')) ?></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?= $this->endSection() ?>

<?= $this->section('postMain') ?>
<?= view('layouts/datatable_assets') ?>
<?= $this->endSection() ?>
