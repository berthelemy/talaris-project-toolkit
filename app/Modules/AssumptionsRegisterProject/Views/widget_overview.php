<?php
/**
 * @var array{low:int,medium:int,high:int} $overview_counts
 * @var int $scope_id
 */
?>
<div class="card h-100">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><?= esc(lang('Module.assumptionsWidgetOverviewTitle')) ?></h5>
        <div class="btn-group btn-group-sm" role="group">
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#assumptionOverviewModalAdd"><?= lang('Module.addNew') ?></button>
            <a class="btn btn-outline-primary" href="<?= site_url('projects/' . $scope_id . '/modules/assumptions-register') ?>"><?= lang('Module.openModule') ?></a>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-2">
            <?php foreach (['high', 'medium', 'low'] as $level): ?>
                <div class="col-4">
                    <div class="border rounded p-2 h-100">
                        <div class="text-muted small"><?= esc(lang('Module.impactLevel' . ucfirst($level))) ?></div>
                        <div class="h5 mb-0"><?= esc((string) ((int) ($overview_counts[$level] ?? 0))) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="assumptionOverviewModalAdd" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= lang('Module.addNewAssumption') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="<?= site_url('projects/' . $scope_id . '/modules/assumptions-register') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="assumption-overview-title"><?= lang('Module.assumptionsColumnDescription') ?></label>
                        <input id="assumption-overview-title" class="form-control" name="title" type="text" maxlength="200" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= lang('Domain.cancelButton') ?></button>
                    <button type="submit" class="btn btn-primary"><?= lang('Module.raidCreateButton') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
