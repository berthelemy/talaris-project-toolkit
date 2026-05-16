<?php
if (defined('TASKS_WIDGET_ADD_MODAL_RENDERED')) {
    return;
}
define('TASKS_WIDGET_ADD_MODAL_RENDERED', true);
?>
<div class="modal fade" id="taskModalAdd" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= esc(lang('Module.addNewTask')) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="<?= site_url('projects/' . $scope_id . '/modules/tasks-register') ?>">
                <?= csrf_field() ?>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="task-title"><?= esc(lang('Module.raidColumnTitle')) ?></label>
                            <input id="task-title" class="form-control" name="title" type="text" maxlength="200" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="task-description"><?= esc(lang('Module.raidColumnDescription')) ?></label>
                            <textarea id="task-description" class="form-control" name="description" rows="2"></textarea>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="task-owner-user-id"><?= esc(lang('Module.raidColumnOwner')) ?></label>
                            <input id="task-owner-user-id" class="form-control" name="owner_user_id" type="number" min="1">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="task-task-category"><?= esc(lang('Module.tasksTaskCategoryLabel')) ?></label>
                            <input id="task-task-category" class="form-control" name="task_category" type="text" maxlength="100">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="task-priority"><?= esc(lang('Module.raidColumnPriority')) ?></label>
                            <select id="task-priority" class="form-select" name="priority" required>
                                <option value="low"><?= esc(lang('Module.raidPriorityLow')) ?></option>
                                <option value="medium" selected><?= esc(lang('Module.raidPriorityMedium')) ?></option>
                                <option value="high"><?= esc(lang('Module.raidPriorityHigh')) ?></option>
                                <option value="critical"><?= esc(lang('Module.raidPriorityCritical')) ?></option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="task-status"><?= esc(lang('Module.raidColumnStatus')) ?></label>
                            <select id="task-status" class="form-select" name="status" required>
                                <option value="open" selected><?= esc(lang('Module.raidStatusOpen')) ?></option>
                                <option value="in_progress"><?= esc(lang('Module.raidStatusIn_progress')) ?></option>
                                <option value="blocked"><?= esc(lang('Module.raidStatusBlocked')) ?></option>
                                <option value="in_review"><?= esc(lang('Module.raidStatusIn_review')) ?></option>
                                <option value="completed"><?= esc(lang('Module.raidStatusCompleted')) ?></option>
                                <option value="cancelled"><?= esc(lang('Module.raidStatusCancelled')) ?></option>
                                <option value="closed"><?= esc(lang('Module.raidStatusClosed')) ?></option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="task-related-objective"><?= esc(lang('Module.tasksRelatedObjectiveLabel')) ?></label>
                            <input id="task-related-objective" class="form-control" name="related_objective" type="text" maxlength="255">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="task-related-module-entry-id"><?= esc(lang('Module.tasksRelatedModuleEntryLabel')) ?></label>
                            <input id="task-related-module-entry-id" class="form-control" name="related_module_entry_id" type="number" min="1">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="task-percent-complete"><?= esc(lang('Module.tasksPercentCompleteLabel')) ?></label>
                            <input id="task-percent-complete" class="form-control" name="percent_complete" type="number" min="0" max="100" value="0">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="task-planned-start-date"><?= esc(lang('Module.tasksPlannedStartDateLabel')) ?></label>
                            <input id="task-planned-start-date" class="form-control" name="planned_start_date" type="date">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="task-due-date"><?= esc(lang('Module.tasksDueDateLabel')) ?></label>
                            <input id="task-due-date" class="form-control" name="due_date" type="date">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="task-completed-date"><?= esc(lang('Module.tasksCompletedDateLabel')) ?></label>
                            <input id="task-completed-date" class="form-control" name="completed_date" type="date">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="task-collaborators"><?= esc(lang('Module.tasksCollaboratorsLabel')) ?></label>
                            <textarea id="task-collaborators" class="form-control" name="collaborators" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="task-blocked-reason"><?= esc(lang('Module.tasksBlockedReasonLabel')) ?></label>
                            <textarea id="task-blocked-reason" class="form-control" name="blocked_reason" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="task-next-action"><?= esc(lang('Module.tasksNextActionLabel')) ?></label>
                            <textarea id="task-next-action" class="form-control" name="next_action" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="task-lessons"><?= esc(lang('Module.assumptionsColumnLessonsLearned')) ?></label>
                            <textarea id="task-lessons" class="form-control" name="lessons_learned" rows="2"></textarea>
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
