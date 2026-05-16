<?php
/**
 * @var array{low:int,medium:int,high:int,critical:int} $overview_counts
 * @var int $scope_id
 */
?>
<div class="card h-100">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><?= esc(lang('Module.riskWidgetOverviewTitle')) ?></h5>
        <div class="btn-group btn-group-sm" role="group">
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#riskOverviewModalAdd"><?= lang('Module.addNew') ?></button>
            <a class="btn btn-outline-primary" href="<?= site_url('projects/' . $scope_id . '/modules/risk-register') ?>"><?= lang('Module.openModule') ?></a>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-2">
            <?php foreach (['critical', 'high', 'medium', 'low'] as $priority): ?>
                <div class="col-6">
                    <div class="border rounded p-2 h-100">
                        <div class="text-muted small"><?= esc(lang('Module.raidPriority' . ucfirst($priority))) ?></div>
                        <div class="h5 mb-0"><?= esc((string) ((int) ($overview_counts[$priority] ?? 0))) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="riskOverviewModalAdd" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= lang('Module.addNewRisk') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="<?= site_url('projects/' . $scope_id . '/modules/risk-register') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="risk-overview-title"><?= lang('Module.raidColumnTitle') ?></label>
                        <input id="risk-overview-title" class="form-control" name="title" type="text" maxlength="200" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="risk-overview-description"><?= lang('Module.raidColumnDescription') ?></label>
                        <textarea id="risk-overview-description" class="form-control" name="description" rows="2"></textarea>
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
