<?php

/**
 * RiskRegisterProject module view template: add modal.
 */
if (defined('RISK_WIDGET_ADD_MODAL_RENDERED')) {
    return;
}
define('RISK_WIDGET_ADD_MODAL_RENDERED', true);

$owners = $owners ?? [];
$statusOptions = $status_options ?? ['open', 'in_review', 'blocked', 'closed'];
$riskScaleOptions = $risk_scale_options ?? ['low', 'medium', 'high'];
?>
<div class="modal fade" id="riskModalAdd" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= lang('Module.addNewRisk') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="<?= site_url('projects/' . $scope_id . '/modules/risk-register') ?>">
                <?= csrf_field() ?>
                <div class="modal-body module-modal-body-scroll">
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
                            <select id="risk-owner" class="form-select" name="owner_user_id">
                                <option value=""><?= lang('Module.selectOption') ?></option>
                                <?php foreach ($owners as $owner): ?>
                                    <option value="<?= (int) ($owner['id'] ?? 0) ?>"><?= esc((string) ($owner['username'] ?? '')) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="risk-status"><?= lang('Module.raidColumnStatus') ?></label>
                            <select id="risk-status" class="form-select" name="status" required>
                                <?php foreach ($statusOptions as $statusOption): ?>
                                    <option value="<?= esc($statusOption) ?>"><?= esc((string) lang('Module.raidStatus' . ucfirst($statusOption))) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="risk-impact"><?= lang('Module.raidColumnImpact') ?></label>
                            <select id="risk-impact" class="form-select" name="impact" required>
                                <?php foreach ($riskScaleOptions as $option): ?>
                                    <option value="<?= esc($option) ?>"><?= esc((string) lang('Module.riskImpact' . ucfirst($option))) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="risk-likelihood"><?= lang('Module.raidColumnLikelihood') ?></label>
                            <select id="risk-likelihood" class="form-select" name="likelihood" required>
                                <?php foreach ($riskScaleOptions as $option): ?>
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
