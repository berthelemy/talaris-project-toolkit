<?php

/**
 * DependenciesRegisterProject module view template: add modal.
 */
if (defined('DEPENDENCIES_WIDGET_ADD_MODAL_RENDERED')) {
    return;
}
define('DEPENDENCIES_WIDGET_ADD_MODAL_RENDERED', true);
?>
<div class="modal fade" id="dependencyModalAdd" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= esc(lang('Module.addNewDependency')) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="<?= site_url('projects/' . $scope_id . '/modules/dependencies-register') ?>">
                <?= csrf_field() ?>
                <div class="modal-body module-modal-body-scroll">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="dependency-title"><?= esc(lang('Module.raidColumnTitle')) ?></label>
                            <input id="dependency-title" class="form-control" name="title" type="text" maxlength="200" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="dependency-description"><?= esc(lang('Module.raidColumnDescription')) ?></label>
                            <textarea id="dependency-description" class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="dependency-type"><?= esc(lang('Module.dependenciesTypeLabel')) ?></label>
                            <select id="dependency-type" class="form-select" name="dependency_type">
                                <option value=""><?= esc(lang('Module.selectOption')) ?></option>
                                <option value="internal"><?= esc(lang('Module.dependenciesTypeInternal')) ?></option>
                                <option value="external"><?= esc(lang('Module.dependenciesTypeExternal')) ?></option>
                                <option value="supplier"><?= esc(lang('Module.dependenciesTypeSupplier')) ?></option>
                                <option value="customer"><?= esc(lang('Module.dependenciesTypeCustomer')) ?></option>
                                <option value="technical"><?= esc(lang('Module.dependenciesTypeTechnical')) ?></option>
                                <option value="regulatory"><?= esc(lang('Module.dependenciesTypeRegulatory')) ?></option>
                                <option value="other"><?= esc(lang('Module.dependenciesTypeOther')) ?></option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="dependency-impact"><?= esc(lang('Module.raidColumnImpactLevel')) ?></label>
                            <select id="dependency-impact" class="form-select" name="impact_level">
                                <option value=""><?= esc(lang('Module.selectOption')) ?></option>
                                <option value="low"><?= esc(lang('Module.impactLevelLow')) ?></option>
                                <option value="medium"><?= esc(lang('Module.impactLevelMedium')) ?></option>
                                <option value="high"><?= esc(lang('Module.impactLevelHigh')) ?></option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="dependency-priority"><?= esc(lang('Module.raidColumnPriority')) ?></label>
                            <select id="dependency-priority" class="form-select" name="priority">
                                <option value=""><?= esc(lang('Module.selectOption')) ?></option>
                                <option value="critical"><?= esc(lang('Module.raidPriorityCritical')) ?></option>
                                <option value="high"><?= esc(lang('Module.raidPriorityHigh')) ?></option>
                                <option value="medium"><?= esc(lang('Module.raidPriorityMedium')) ?></option>
                                <option value="low"><?= esc(lang('Module.raidPriorityLow')) ?></option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="dependency-status"><?= esc(lang('Module.raidColumnStatus')) ?></label>
                            <select id="dependency-status" class="form-select" name="status">
                                <option value=""><?= esc(lang('Module.selectOption')) ?></option>
                                <option value="open"><?= esc(lang('Module.raidStatusOpen')) ?></option>
                                <option value="in_progress"><?= esc(lang('Module.raidStatusIn_progress')) ?></option>
                                <option value="at_risk"><?= esc(lang('Module.raidStatusAt_risk')) ?></option>
                                <option value="blocked"><?= esc(lang('Module.raidStatusBlocked')) ?></option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="dependency-work-package"><?= esc(lang('Module.dependenciesRelatedWorkLabel')) ?></label>
                            <input id="dependency-work-package" class="form-control" name="related_work_package" type="text" placeholder="Work package or milestone">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="dependency-depends-on"><?= esc(lang('Module.dependenciesDependsOnLabel')) ?></label>
                            <input id="dependency-depends-on" class="form-control" name="depends_on" type="text" placeholder="Team, supplier, project, or external party">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="dependency-owner"><?= esc(lang('Module.raidColumnOwner')) ?></label>
                            <select id="dependency-owner" class="form-select" name="owner_user_id">
                                <option value=""><?= esc(lang('Module.selectOption')) ?></option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="dependency-mitigation"><?= esc(lang('Module.raidColumnMitigationActions')) ?></label>
                            <textarea id="dependency-mitigation" class="form-control" name="mitigation_actions" rows="2"></textarea>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="dependency-target-date"><?= esc(lang('Module.raidColumnTargetDate')) ?></label>
                            <input id="dependency-target-date" class="form-control" type="date" name="target_date">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="dependency-review-date"><?= esc(lang('Module.raidColumnReviewDate')) ?></label>
                            <input id="dependency-review-date" class="form-control" type="date" name="review_date">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-check" for="dependency-escalation">
                                <input id="dependency-escalation" class="form-check-input" type="checkbox" name="escalation_required" value="1">
                                <span class="form-check-label"><?= esc(lang('Module.dependenciesEscalationRequiredLabel')) ?></span>
                            </label>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="dependency-lessons"><?= esc(lang('Module.assumptionsColumnLessonsLearned')) ?></label>
                            <textarea id="dependency-lessons" class="form-control" name="lessons_learned" rows="2"></textarea>
                        </div>
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
