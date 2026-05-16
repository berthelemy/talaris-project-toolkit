<?php
/**
 * @var list<array<string,mixed>> $entries
 * @var int $entry_count
 * @var int $scope_id
 */
?>
<div class="card mb-4">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><?= esc(lang('Module.decisionsWidgetPendingTitle')) ?></h5>
        <div class="btn-group btn-group-sm" role="group">
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#decisionPendingModalAdd"><?= esc(lang('Module.addNew')) ?></button>
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
                        <div class="small text-muted"><?= esc((string) ($entry['target_date'] ?? '')) ?></div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="decisionPendingModalAdd" tabindex="-1">
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
                        <label class="form-label" for="decision-pending-description"><?= esc(lang('Module.decisionsDescriptionLabel')) ?></label>
                        <textarea id="decision-pending-description" class="form-control" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="decision-pending-date"><?= esc(lang('Module.decisionsDateLabel')) ?></label>
                        <input id="decision-pending-date" class="form-control" type="date" name="decision_date" required>
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
