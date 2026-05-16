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
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#decisionModalAdd"><?= esc(lang('Module.addNew')) ?></button>
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

<?php include __DIR__ . '/_add_modal.php'; ?>
