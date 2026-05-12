<?php
/**
 * @var array<int, array{id: int, title: string, created_at: string, impact: string, likelihood: string, priority: string, status: string, owner_username: string}> $entries
 * @var int $entry_count
 * @var int $scope_id
 */
?>
<div class="card mb-4">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><?= lang('Module.riskRegisterTitle') ?></h5>
        <div class="btn-group btn-group-sm" role="group">
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#riskModalAdd"><?= lang('Module.addNew') ?></button>
            <a class="btn btn-outline-primary" href="<?= site_url('projects/' . $scope_id . '/modules/risk-register') ?>"><?= lang('Module.openModule') ?></a>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($entries)): ?>
            <p class="text-muted mb-0"><?= lang('Module.entriesNone') ?></p>
        <?php else: ?>
            <table class="table table-sm table-hover mb-0 js-datatable">
                <thead class="table-light">
                    <tr>
                        <th><?= lang('Module.raidColumnTitle') ?></th>
                        <th><?= lang('Module.raidColumnImpact') ?></th>
                        <th><?= lang('Module.raidColumnLikelihood') ?></th>
                        <th><?= lang('Module.raidColumnPriority') ?></th>
                        <th><?= lang('Module.raidColumnOwner') ?></th>
                        <th><?= lang('Module.raidColumnStatus') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entries as $entry): ?>
                        <tr>
                            <td>
                                <a href="<?= site_url('projects/' . $scope_id . '/modules/risk-register') ?>#entry-<?= (int) ($entry['id'] ?? 0) ?>">
                                    <?= esc((string) ($entry['title'] ?? '')) ?>
                                </a>
                            </td>
                            <td><?= esc((string) ($entry['impact'] ?? '')) ?></td>
                            <td><?= esc((string) ($entry['likelihood'] ?? '')) ?></td>
                            <td><span class="badge bg-warning text-dark"><?= esc((string) ($entry['priority'] ?? '')) ?></span></td>
                            <td><?= esc((string) ($entry['owner_username'] ?? '')) ?></td>
                            <td><?= esc((string) ($entry['status'] ?? '')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if ($entry_count >= 5): ?>
                <a class="btn btn-sm btn-outline-primary mt-3" href="<?= site_url('projects/' . $scope_id . '/modules/risk-register') ?>"><?= lang('Module.viewAll') ?></a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal for adding new Risk entry -->
<div class="modal fade" id="riskModalAdd" tabindex="-1">
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
                        <label class="form-label" for="risk-title"><?= lang('Module.raidColumnTitle') ?></label>
                        <input id="risk-title" class="form-control" name="title" type="text" maxlength="200" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="risk-impact"><?= lang('Module.raidColumnImpact') ?></label>
                        <select id="risk-impact" class="form-select" name="impact" required>
                            <option value="low"><?= lang('Module.riskImpactLow') ?></option>
                            <option value="medium" selected><?= lang('Module.riskImpactMedium') ?></option>
                            <option value="high"><?= lang('Module.riskImpactHigh') ?></option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="risk-likelihood"><?= lang('Module.raidColumnLikelihood') ?></label>
                        <select id="risk-likelihood" class="form-select" name="likelihood" required>
                            <option value="low"><?= lang('Module.riskLikelihoodLow') ?></option>
                            <option value="medium" selected><?= lang('Module.riskLikelihoodMedium') ?></option>
                            <option value="high"><?= lang('Module.riskLikelihoodHigh') ?></option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="risk-mitigation"><?= lang('Module.raidColumnMitigationActions') ?></label>
                        <textarea id="risk-mitigation" class="form-control" name="mitigation_actions" rows="2"></textarea>
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

