<?php
/**
 * @var array<int, array<string, mixed>> $entries
 * @var int $entry_count
 * @var int $scope_id
 */
?>
<div class="card mb-4">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><?= lang('Module.decisionsRegisterTitle') ?></h5>
        <a class="btn btn-sm btn-outline-primary" href="<?= site_url('projects/' . $scope_id . '/modules/decisions-register') ?>"><?= lang('Module.openModule') ?></a>
    </div>
    <div class="card-body">
        <form method="post" action="<?= site_url('projects/' . $scope_id . '/modules/decisions-register') ?>" class="row g-2 mb-3">
            <?= csrf_field() ?>
            <div class="col-12">
                <label class="form-label" for="decision-description-<?= $scope_id ?>"><?= lang('Module.decisionsDescriptionLabel') ?></label>
                <textarea id="decision-description-<?= $scope_id ?>" class="form-control form-control-sm" name="description" rows="2" required></textarea>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label" for="decision-date-<?= $scope_id ?>"><?= lang('Module.decisionsDateLabel') ?></label>
                <input id="decision-date-<?= $scope_id ?>" class="form-control form-control-sm" type="date" name="decision_date" required>
            </div>
            <div class="col-12 col-md-6 d-grid align-self-end">
                <button class="btn btn-sm btn-primary" type="submit"><?= lang('Module.raidCreateButton') ?></button>
            </div>
        </form>

        <?php if (empty($entries)): ?>
            <p class="text-muted mb-0"><?= lang('Module.entriesNone') ?></p>
        <?php else: ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($entries as $entry): ?>
                    <li class="list-group-item px-0">
                        <div class="small text-muted"><?= esc((string) ($entry['decision_date'] ?? '')) ?><?php if ((string) ($entry['made_by_username'] ?? '') !== ''): ?> - <?= esc((string) $entry['made_by_username']) ?><?php endif; ?></div>
                        <div><?= esc((string) ($entry['description'] ?? '')) ?></div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>
