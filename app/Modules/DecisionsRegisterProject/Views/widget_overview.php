<?php
/**
 * @var array{draft:int,proposed:int,approved:int,implemented:int,rejected:int,superseded:int,closed:int} $overview_counts
 * @var int $scope_id
 */
?>
<div class="card mb-4">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><?= esc(lang('Module.decisionsWidgetOverviewTitle')) ?></h5>
        <div class="btn-group btn-group-sm" role="group">
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#decisionOverviewModalAdd"><?= esc(lang('Module.addNew')) ?></button>
            <a class="btn btn-outline-primary" href="<?= site_url('projects/' . $scope_id . '/modules/decisions-register') ?>"><?= esc(lang('Module.openModule')) ?></a>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-2 text-center">
            <?php foreach ($overview_counts as $status => $count): ?>
                <div class="col-6 col-md-4">
                    <div class="border rounded p-2 h-100">
                        <div class="small text-muted"><?= esc(lang('Module.raidStatus' . ucfirst($status))) ?></div>
                        <div class="h5 mb-0"><?= (int) $count ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="decisionOverviewModalAdd" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= esc(lang('Module.addNewDecision')) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="<?= site_url('projects/' . $scope_id . '/modules/decisions-register') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="decision-overview-description"><?= esc(lang('Module.decisionsDescriptionLabel')) ?></label>
                        <textarea id="decision-overview-description" class="form-control" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="decision-overview-date"><?= esc(lang('Module.decisionsDateLabel')) ?></label>
                        <input id="decision-overview-date" class="form-control" type="date" name="decision_date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= esc(lang('Domain.cancelButton')) ?></button>
                    <button type="submit" class="btn btn-primary"><?= esc(lang('Module.raidCreateButton')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
