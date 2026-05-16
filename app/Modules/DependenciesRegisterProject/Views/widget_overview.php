<?php
/**
 * @var array{open:int,in_progress:int,at_risk:int,blocked:int,fulfilled:int,cancelled:int,closed:int} $status_counts
 * @var array{low:int,medium:int,high:int} $impact_counts
 */
?>
<div class="card mb-4">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><?= esc(lang('Module.dependenciesWidgetOverviewTitle')) ?></h5>
        <div class="btn-group btn-group-sm" role="group">
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#dependencyModalAdd"><?= esc(lang('Module.addNew')) ?></button>
            <a class="btn btn-outline-primary" href="<?= site_url('projects/' . $scope_id . '/modules/dependencies-register') ?>"><?= esc(lang('Module.openModule')) ?></a>
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
        <h6 class="small text-muted text-uppercase mb-2"><?= esc(lang('Module.raidColumnImpactLevel')) ?></h6>
        <div class="row g-2 text-center">
            <?php foreach ($impact_counts as $impact => $count): ?>
                <div class="col-4">
                    <div class="border rounded p-2 h-100">
                        <div class="small text-muted"><?= esc(lang('Module.impactLevel' . ucfirst($impact))) ?></div>
                        <div class="h5 mb-0"><?= (int) $count ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/_add_modal.php'; ?>
