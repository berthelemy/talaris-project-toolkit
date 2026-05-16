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
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#assumptionModalAdd"><?= lang('Module.addNew') ?></button>
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

<?php include __DIR__ . '/_add_modal.php'; ?>
