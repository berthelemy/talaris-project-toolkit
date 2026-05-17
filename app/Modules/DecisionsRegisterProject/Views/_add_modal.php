<?php

/**
 * File documentation for app/Modules/DecisionsRegisterProject/Views/_add_modal.php.
 */
if (defined('DECISIONS_WIDGET_ADD_MODAL_RENDERED')) {
    return;
}
define('DECISIONS_WIDGET_ADD_MODAL_RENDERED', true);
?>
<div class="modal fade" id="decisionModalAdd" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= esc(lang('Module.addNewDecision')) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="<?= site_url('projects/' . $scope_id . '/modules/decisions-register') ?>">
                <?= csrf_field() ?>
                <div class="modal-body module-modal-body-scroll">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="decision-title"><?= esc(lang('Module.raidColumnTitle')) ?></label>
                            <input id="decision-title" class="form-control" name="title" type="text" maxlength="200" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="decision-description"><?= esc(lang('Module.decisionsDescriptionLabel')) ?></label>
                            <textarea id="decision-description" class="form-control" name="description" rows="3" required></textarea>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="decision-date"><?= esc(lang('Module.decisionsDateLabel')) ?></label>
                            <input id="decision-date" class="form-control" type="date" name="decision_date" required>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="decision-category"><?= esc(lang('Module.decisionsCategoryLabel')) ?></label>
                            <select id="decision-category" class="form-select" name="decision_category">
                                <option value=""><?= esc(lang('Module.selectOption')) ?></option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="decision-rationale"><?= esc(lang('Module.decisionsRationaleLabel')) ?></label>
                            <textarea id="decision-rationale" class="form-control" name="decision_rationale" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="decision-alternatives"><?= esc(lang('Module.decisionsAlternativesLabel')) ?></label>
                            <textarea id="decision-alternatives" class="form-control" name="alternatives_considered" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="decision-chosen"><?= esc(lang('Module.decisionsChosenOptionLabel')) ?></label>
                            <textarea id="decision-chosen" class="form-control" name="chosen_option" rows="2"></textarea>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="decision-approver"><?= esc(lang('Module.decisionsApproverLabel')) ?></label>
                            <select id="decision-approver" class="form-select" name="approver_user_id">
                                <option value=""><?= esc(lang('Module.selectOption')) ?></option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="decision-status"><?= esc(lang('Module.raidColumnStatus')) ?></label>
                            <select id="decision-status" class="form-select" name="status">
                                <option value=""><?= esc(lang('Module.selectOption')) ?></option>
                                <option value="draft"><?= esc(lang('Module.raidStatusDraft')) ?></option>
                                <option value="proposed"><?= esc(lang('Module.raidStatusProposed')) ?></option>
                                <option value="approved"><?= esc(lang('Module.raidStatusApproved')) ?></option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="decision-implementation"><?= esc(lang('Module.decisionsImplementationActionsLabel')) ?></label>
                            <textarea id="decision-implementation" class="form-control" name="implementation_actions" rows="2"></textarea>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="decision-target-date"><?= esc(lang('Module.raidColumnTargetDate')) ?></label>
                            <input id="decision-target-date" class="form-control" type="date" name="target_implementation_date">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="decision-review-date"><?= esc(lang('Module.raidColumnReviewDate')) ?></label>
                            <input id="decision-review-date" class="form-control" type="date" name="review_date">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="decision-superseded"><?= esc(lang('Module.decisionsSupersededByLabel')) ?></label>
                            <input id="decision-superseded" class="form-control" type="text" name="superseded_by" placeholder="Entry ID">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="decision-lessons"><?= esc(lang('Module.assumptionsColumnLessonsLearned')) ?></label>
                            <textarea id="decision-lessons" class="form-control" name="lessons_learned" rows="2"></textarea>
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
