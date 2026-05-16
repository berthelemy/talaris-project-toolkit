<?php
/**
 * @var array{open:int,in_review:int,blocked:int,resolved:int,closed:int} $status_counts
 * @var array{low:int,medium:int,high:int,critical:int} $priority_counts
 * @var int $scope_id
 */
?>
<div class="card mb-4">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><?= esc(lang('Module.issuesWidgetOverviewTitle')) ?></h5>
        <div class="btn-group btn-group-sm" role="group">
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#issueOverviewModalAdd"><?= esc(lang('Module.addNew')) ?></button>
            <a class="btn btn-outline-primary" href="<?= site_url('projects/' . $scope_id . '/modules/issue-tracker') ?>"><?= esc(lang('Module.openModule')) ?></a>
        </div>
    </div>
    <div class="card-body">
        <h6 class="small text-muted text-uppercase mb-2"><?= esc(lang('Module.raidColumnStatus')) ?></h6>
        <div class="row g-2 text-center mb-3">
            <?php foreach ($status_counts as $status => $count): ?>
                <div class="col-6 col-md-4">
                    <div class="border rounded p-2 h-100">
                        <div class="small text-muted"><?= esc(lang('Module.raidStatus' . ucfirst($status))) ?></div>
                        <div class="h5 mb-0"><?= (int) $count ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <h6 class="small text-muted text-uppercase mb-2"><?= esc(lang('Module.raidColumnPriority')) ?></h6>
        <div class="row g-2 text-center">
            <?php foreach ($priority_counts as $priority => $count): ?>
                <div class="col-6 col-md-3">
                    <div class="border rounded p-2 h-100">
                        <div class="small text-muted"><?= esc(lang('Module.raidPriority' . ucfirst($priority))) ?></div>
                        <div class="h5 mb-0"><?= (int) $count ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="issueOverviewModalAdd" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= esc(lang('Module.addNewIssue')) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="<?= site_url('projects/' . $scope_id . '/modules/issue-tracker') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="issue-overview-title"><?= esc(lang('Module.raidColumnTitle')) ?></label>
                        <input id="issue-overview-title" class="form-control" name="title" type="text" maxlength="200" required>
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
