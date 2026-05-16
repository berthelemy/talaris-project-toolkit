<?php
/**
 * @var list<array<string,mixed>> $entries
 * @var int $entry_count
 * @var int $scope_id
 */
?>
<div class="card mb-4">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><?= esc(lang('Module.decisionsWidgetRecentKeyTitle')) ?></h5>
        <div class="btn-group btn-group-sm" role="group">
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#decisionModalAdd"><?= esc(lang('Module.addNew')) ?></button>
            <a class="btn btn-outline-primary" href="<?= site_url('projects/' . $scope_id . '/modules/decisions-register') ?>"><?= esc(lang('Module.openModule')) ?></a>
        </div>
    </div>
    <div class="card-body">
        <?php if ($entry_count === 0): ?>
            <p class="text-muted mb-0"><?= esc(lang('Module.entriesNone')) ?></p>
        <?php else: ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($entries as $entry): ?>
                    <li class="list-group-item px-0">
                        <a href="<?= site_url('projects/' . $scope_id . '/modules/decisions-register') ?>#entry-<?= (int) ($entry['id'] ?? 0) ?>">
                            <?= esc((string) ($entry['title'] ?? '')) ?>
                        </a>
                        <div class="small text-muted">
                            <?= esc((string) ($entry['decision_date'] ?? '')) ?>
                            · <?= esc(lang('Module.raidPriority' . ucfirst((string) ($entry['priority'] ?? 'medium')))) ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/_add_modal.php'; ?>
