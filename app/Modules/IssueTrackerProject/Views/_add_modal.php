<?php

/**
 * File documentation for app/Modules/IssueTrackerProject/Views/_add_modal.php.
 */
if (defined('ISSUE_WIDGET_ADD_MODAL_RENDERED')) {
    return;
}
define('ISSUE_WIDGET_ADD_MODAL_RENDERED', true);
?>
<div class="modal fade" id="issueModalAdd" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= esc(lang('Module.addNewIssue')) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="<?= site_url('projects/' . $scope_id . '/modules/issue-tracker') ?>">
                <?= csrf_field() ?>
                <div class="modal-body module-modal-body-scroll">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="issue-title"><?= esc(lang('Module.raidColumnTitle')) ?></label>
                            <input id="issue-title" class="form-control" name="title" type="text" maxlength="200" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="issue-description"><?= esc(lang('Module.raidColumnDescription')) ?></label>
                            <textarea id="issue-description" class="form-control" name="description" rows="2"></textarea>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="issue-date-reported"><?= esc(lang('Module.raidColumnReported')) ?></label>
                            <input id="issue-date-reported" class="form-control" name="date_reported" type="date">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="issue-reporter-user-id"><?= esc(lang('Module.raidColumnReporter')) ?></label>
                            <input id="issue-reporter-user-id" class="form-control" name="reporter_user_id" type="number" min="1">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="issue-owner-user-id"><?= esc(lang('Module.raidColumnOwner')) ?></label>
                            <input id="issue-owner-user-id" class="form-control" name="owner_user_id" type="number" min="1">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="issue-impact-level"><?= esc(lang('Module.raidColumnImpactLevel')) ?></label>
                            <select id="issue-impact-level" class="form-select" name="impact_level">
                                <option value=""></option>
                                <option value="low"><?= esc(lang('Module.impactLevelLow')) ?></option>
                                <option value="medium"><?= esc(lang('Module.impactLevelMedium')) ?></option>
                                <option value="high"><?= esc(lang('Module.impactLevelHigh')) ?></option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="issue-priority"><?= esc(lang('Module.raidColumnPriority')) ?></label>
                            <select id="issue-priority" class="form-select" name="priority" required>
                                <option value="low"><?= esc(lang('Module.raidPriorityLow')) ?></option>
                                <option value="medium" selected><?= esc(lang('Module.raidPriorityMedium')) ?></option>
                                <option value="high"><?= esc(lang('Module.raidPriorityHigh')) ?></option>
                                <option value="critical"><?= esc(lang('Module.raidPriorityCritical')) ?></option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="issue-status"><?= esc(lang('Module.raidColumnStatus')) ?></label>
                            <select id="issue-status" class="form-select" name="status" required>
                                <option value="open" selected><?= esc(lang('Module.raidStatusOpen')) ?></option>
                                <option value="in_review"><?= esc(lang('Module.raidStatusIn_review')) ?></option>
                                <option value="blocked"><?= esc(lang('Module.raidStatusBlocked')) ?></option>
                                <option value="resolved"><?= esc(lang('Module.raidStatusResolved')) ?></option>
                                <option value="closed"><?= esc(lang('Module.raidStatusClosed')) ?></option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="issue-target-date"><?= esc(lang('Module.raidColumnTargetDate')) ?></label>
                            <input id="issue-target-date" class="form-control" name="target_date" type="date">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="issue-review-date"><?= esc(lang('Module.raidColumnReviewDate')) ?></label>
                            <input id="issue-review-date" class="form-control" name="review_date" type="date">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="issue-resolution-actions"><?= esc(lang('Module.raidColumnMitigationActions')) ?></label>
                            <textarea id="issue-resolution-actions" class="form-control" name="mitigation_actions" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="issue-lessons"><?= esc(lang('Module.assumptionsColumnLessonsLearned')) ?></label>
                            <textarea id="issue-lessons" class="form-control" name="lessons_learned" rows="2"></textarea>
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
