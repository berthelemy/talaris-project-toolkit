<?php
/**
 * @var array<int, array{id: int, title: string, created_at: string}> $entries
 * @var int $entry_count
 * @var int $scope_id
 */
?>
<div class="card mb-4">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><?= lang('Module.riskRegisterTitle') ?></h5>
        <a class="btn btn-sm btn-outline-primary" href="<?= site_url('projects/' . $scope_id . '/modules/risk-register') ?>"><?= lang('Module.openModule') ?></a>
    </div>
    <div class="card-body">
        <form method="post" action="<?= site_url('projects/' . $scope_id . '/modules/risk-register') ?>" class="row g-2 mb-3">
            <?= csrf_field() ?>
            <div class="col-12">
                <label class="form-label" for="risk-title-<?= $scope_id ?>"><?= lang('Module.raidColumnTitle') ?></label>
                <input id="risk-title-<?= $scope_id ?>" class="form-control form-control-sm" name="title" type="text" maxlength="200" required>
            </div>
            <div class="col-12">
                <label class="form-label" for="risk-mitigation-<?= $scope_id ?>"><?= lang('Module.raidColumnMitigationActions') ?></label>
                <textarea id="risk-mitigation-<?= $scope_id ?>" class="form-control form-control-sm" name="mitigation_actions" rows="2"></textarea>
            </div>
            <div class="col-6">
                <label class="form-label" for="risk-impact-<?= $scope_id ?>"><?= lang('Module.raidColumnImpact') ?></label>
                <select id="risk-impact-<?= $scope_id ?>" class="form-select form-select-sm" name="impact" required>
                    <option value="low"><?= lang('Module.riskImpactLow') ?></option>
                    <option value="medium" selected><?= lang('Module.riskImpactMedium') ?></option>
                    <option value="high"><?= lang('Module.riskImpactHigh') ?></option>
                </select>
            </div>
            <div class="col-6">
                <label class="form-label" for="risk-likelihood-<?= $scope_id ?>"><?= lang('Module.raidColumnLikelihood') ?></label>
                <select id="risk-likelihood-<?= $scope_id ?>" class="form-select form-select-sm" name="likelihood" required>
                    <option value="low"><?= lang('Module.riskLikelihoodLow') ?></option>
                    <option value="medium" selected><?= lang('Module.riskLikelihoodMedium') ?></option>
                    <option value="high"><?= lang('Module.riskLikelihoodHigh') ?></option>
                </select>
            </div>
            <div class="col-12 d-grid">
                <button class="btn btn-sm btn-primary" type="submit"><?= lang('Module.raidCreateButton') ?></button>
            </div>
        </form>

        <?php if (empty($entries)): ?>
            <p class="text-muted mb-0"><?= lang('Module.entriesNone') ?></p>
        <?php else: ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($entries as $entry): ?>
                    <li class="list-group-item px-0"><?= esc((string) ($entry['title'] ?? '')) ?></li>
                <?php endforeach; ?>
            </ul>
            <?php if ($entry_count >= 5): ?>
                <a class="btn btn-sm btn-outline-primary mt-3" href="<?= site_url('projects/' . $scope_id . '/modules/risk-register') ?>\"><?= lang('Module.viewAll') ?></a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
