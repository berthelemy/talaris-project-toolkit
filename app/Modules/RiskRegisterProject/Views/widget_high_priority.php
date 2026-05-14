<?php
/**
 * @var array<int, array{id: int, title: string, priority: string, owner_username: string}> $entries
 * @var int $entry_count
 * @var int $scope_id
 * @var list<array{id:int,username:string}> $owners
 * @var list<string> $status_options
 * @var list<string> $risk_scale_options
 */
?>
<div class="card h-100">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><?= esc(lang('Module.riskWidgetHighPriorityTitle')) ?></h5>
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
                        <th><?= lang('Module.raidColumnPriority') ?></th>
                        <th><?= lang('Module.raidColumnOwner') ?></th>
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
                            <td><span class="badge bg-warning text-dark"><?= esc((string) ($entry['priority'] ?? '')) ?></span></td>
                            <td><?= esc((string) ($entry['owner_username'] ?? '')) ?></td>
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
                    <div class="row g-2">
                        <div class="col-12">
                            <label class="form-label" for="risk-title"><?= lang('Module.raidColumnTitle') ?></label>
                            <input id="risk-title" class="form-control" name="title" type="text" maxlength="200" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="risk-description"><?= lang('Module.raidColumnDescription') ?></label>
                            <textarea id="risk-description" class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="risk-mitigation"><?= lang('Module.raidColumnMitigationActions') ?></label>
                            <textarea id="risk-mitigation" class="form-control" name="mitigation_actions" rows="3"></textarea>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="risk-owner"><?= lang('Module.raidColumnOwner') ?></label>
                            <select id="risk-owner" class="form-select" name="owner_user_id" required>
                                <?php foreach ($owners as $owner): ?>
                                    <option value="<?= (int) ($owner['id'] ?? 0) ?>"><?= esc((string) ($owner['username'] ?? '')) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="risk-status"><?= lang('Module.raidColumnStatus') ?></label>
                            <select id="risk-status" class="form-select" name="status" required>
                                <?php foreach ($status_options as $statusOption): ?>
                                    <option value="<?= esc($statusOption) ?>"><?= esc((string) lang('Module.raidStatus' . ucfirst($statusOption))) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="risk-impact"><?= lang('Module.raidColumnImpact') ?></label>
                            <select id="risk-impact" class="form-select" name="impact" required>
                                <?php foreach ($risk_scale_options as $option): ?>
                                    <option value="<?= esc($option) ?>"><?= esc((string) lang('Module.riskImpact' . ucfirst($option))) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="risk-likelihood"><?= lang('Module.raidColumnLikelihood') ?></label>
                            <select id="risk-likelihood" class="form-select" name="likelihood" required>
                                <?php foreach ($risk_scale_options as $option): ?>
                                    <option value="<?= esc($option) ?>"><?= esc((string) lang('Module.riskLikelihood' . ucfirst($option))) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="risk-target-date"><?= lang('Module.raidColumnTargetDate') ?></label>
                            <input id="risk-target-date" class="form-control" name="target_date" type="date">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="risk-review-date"><?= lang('Module.raidColumnReviewDate') ?></label>
                            <input id="risk-review-date" class="form-control" name="review_date" type="date">
                        </div>
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
