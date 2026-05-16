<?php
/**
 * @var array<string, mixed> $project
 * @var string $moduleRouteSegment
 * @var string $moduleTitleKey
 * @var string $moduleDescriptionKey
 * @var list<array<string, mixed>> $entries
 * @var list<array{id:int,username:string}> $owners
 * @var list<string> $statusOptions
 * @var list<string> $priorityOptions
 * @var list<string> $riskScaleOptions
 * @var list<string> $impactLevelOptions
 * @var array{q:string,status:string,owner_user_id:int,sort:string} $filters
 * @var bool $isReadOnly
 * @var bool $isRiskModule
 * @var bool $isAssumptionModule
 * @var bool $isDecisionModule
 * @var bool $isIssueModule
 * @var bool $isDependencyModule
 * @var bool $isTaskModule
 * @var string $backUrl
 */

$pageTitle = (string) lang($moduleTitleKey);
$active = 'projects';
?>
<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
    <div class="mb-3">
        <a class="btn btn-outline-secondary btn-sm" href="<?= site_url($backUrl) ?>"><?= esc(lang('Module.backToProject')) ?></a>
    </div>

    <?php if (session('error') !== null): ?>
        <div class="alert alert-danger" role="alert"><?= esc((string) session('error')) ?></div>
    <?php endif; ?>
    <?php if (session('success') !== null): ?>
        <div class="alert alert-success" role="alert"><?= esc((string) session('success')) ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h2 class="h5 mb-2"><?= esc((string) ($project['name'] ?? '')) ?> - <?= esc((string) lang($moduleTitleKey)) ?></h2>
            <p class="mb-0 text-muted"><?= esc((string) lang($moduleDescriptionKey)) ?></p>
        </div>
    </div>

    <?php if ($isReadOnly): ?>
        <div class="alert alert-info" role="status"><?= esc(lang('Module.readOnlyNotice')) ?></div>
    <?php endif; ?>

    <?php if (! $isReadOnly): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body d-flex justify-content-between align-items-center gap-2">
                <h3 class="h6 mb-0"><?= esc(lang('Module.raidCreateTitle')) ?></h3>
                <button type="button" id="<?= $isRiskModule ? 'risk-add-entry-button' : 'raid-add-entry-button' ?>" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#<?= $isRiskModule ? 'riskEntryCreateModal' : 'raidEntryCreateModal' ?>">
                    <?= esc(lang('Module.raidAddEntryButton')) ?>
                </button>
            </div>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="p-3">
                <h3 class="h6 mb-0"><?= esc(lang('Module.entriesTitle')) ?></h3>
            </div>
            <?php if (empty($entries)): ?>
                <p class="text-muted p-4 mb-0"><?= esc(lang('Module.entriesNone')) ?></p>
            <?php else: ?>
                <div class="p-3 pt-0">
                    <div class="<?= $isRiskModule ? '' : 'table-responsive' ?>">
                        <table class="table table-hover mb-0 js-datatable <?= $isRiskModule ? 'js-risk-datatable nowrap' : '' ?>">
                            <thead class="table-light">
                        <tr>
                            <th class="align-top" data-priority="2"><?= esc(lang('Module.raidColumnTitle')) ?></th>
                            <?php if ($isRiskModule): ?>
                                <th class="align-top" data-priority="6"><?= esc(lang('Module.raidColumnDescription')) ?></th>
                                <th class="align-top" data-priority="7"><?= esc(lang('Module.raidColumnMitigationActions')) ?></th>
                            <?php endif; ?>
                            <th class="align-top" data-priority="8"><?= esc(lang('Module.raidColumnOwner')) ?></th>
                            <th class="align-top" data-priority="9"><?= esc(lang('Module.raidColumnStatus')) ?></th>
                            <?php if ($isAssumptionModule): ?>
                                <th class="align-top" data-priority="10"><?= esc(lang('Module.raidColumnImpactIfNotValid')) ?></th>
                                <th class="align-top" data-priority="11"><?= esc(lang('Module.assumptionsColumnValidationActions')) ?></th>
                                <th class="align-top" data-priority="10"><?= esc(lang('Module.raidColumnImpactLevel')) ?></th>
                            <?php endif; ?>
                            <?php if ($isRiskModule): ?>
                                <th class="align-top" data-priority="10"><?= esc(lang('Module.raidColumnImpact')) ?></th>
                                <th class="align-top" data-priority="11"><?= esc(lang('Module.raidColumnLikelihood')) ?></th>
                            <?php endif; ?>
                            <th class="align-top" data-priority="12"><?= esc(lang('Module.raidColumnPriority')) ?></th>
                            <th class="<?= $isRiskModule ? 'align-top' : 'd-none d-md-table-cell align-top' ?>" data-priority="13"><?= esc(lang('Module.raidColumnTargetDate')) ?></th>
                            <th class="<?= $isRiskModule ? 'align-top' : 'd-none d-md-table-cell align-top' ?>" data-priority="14"><?= esc(lang('Module.raidColumnReviewDate')) ?></th>
                            <th class="<?= $isRiskModule ? 'align-top' : 'd-none d-lg-table-cell align-top' ?>" data-priority="15"><?= esc(lang('Module.raidColumnUpdatedAt')) ?></th>
                            <?php if (! $isReadOnly): ?>
                                <th class="all align-top" data-priority="1"><?= esc(lang('Module.columnActions')) ?></th>
                            <?php endif; ?>
                        </tr>
                            </thead>
                            <tbody>
                        <?php foreach ($entries as $entry): ?>
                            <?php $riskEditFormId = 'risk-edit-form-' . (int) ($entry['id'] ?? 0); ?>
                            <tr id="entry-<?= (int) ($entry['id'] ?? 0) ?>">
                                <td>
                                    <?php if ($isRiskModule): ?>
                                        <div data-risk-display class="fw-semibold"><?= esc((string) ($entry['title'] ?? '')) ?></div>
                                        <div data-risk-editor class="d-none">
                                            <input form="<?= esc($riskEditFormId) ?>" name="title" type="text" class="form-control form-control-sm" value="<?= esc((string) ($entry['title'] ?? '')) ?>" maxlength="200" data-risk-row-editable disabled required>
                                        </div>
                                    <?php else: ?>
                                        <div data-risk-display>
                                            <div class="fw-semibold"><?= esc((string) ($entry['title'] ?? '')) ?></div>
                                            <?php if ((string) ($entry['description'] ?? '') !== ''): ?>
                                                <div class="text-muted small"><?= esc((string) ($entry['description'] ?? '')) ?></div>
                                            <?php endif; ?>
                                            <?php if ($isDecisionModule && (string) ($entry['decision_category'] ?? '') !== ''): ?>
                                                <div class="text-muted small"><strong><?= esc(lang('Module.decisionsCategoryLabel')) ?>:</strong> <?= esc((string) ($entry['decision_category'] ?? '')) ?></div>
                                            <?php endif; ?>
                                            <?php if ($isDecisionModule && (string) ($entry['decision_rationale'] ?? '') !== ''): ?>
                                                <div class="text-muted small"><strong><?= esc(lang('Module.decisionsRationaleLabel')) ?>:</strong> <?= esc((string) ($entry['decision_rationale'] ?? '')) ?></div>
                                            <?php endif; ?>
                                            <?php if ($isDependencyModule && (string) ($entry['dependency_type'] ?? '') !== ''): ?>
                                                <div class="text-muted small"><strong><?= esc(lang('Module.dependenciesTypeLabel')) ?>:</strong> <?= esc((string) lang('Module.dependenciesType' . ucfirst((string) ($entry['dependency_type'] ?? 'other')))) ?></div>
                                            <?php endif; ?>
                                            <?php if ($isDependencyModule && (string) ($entry['related_work_package'] ?? '') !== ''): ?>
                                                <div class="text-muted small"><strong><?= esc(lang('Module.dependenciesRelatedWorkLabel')) ?>:</strong> <?= esc((string) ($entry['related_work_package'] ?? '')) ?></div>
                                            <?php endif; ?>
                                            <?php if ($isDependencyModule && (string) ($entry['depends_on'] ?? '') !== ''): ?>
                                                <div class="text-muted small"><strong><?= esc(lang('Module.dependenciesDependsOnLabel')) ?>:</strong> <?= esc((string) ($entry['depends_on'] ?? '')) ?></div>
                                            <?php endif; ?>
                                            <?php if ($isIssueModule && (string) ($entry['impact_level'] ?? '') !== ''): ?>
                                                <div class="text-muted small"><strong><?= esc(lang('Module.raidColumnImpactLevel')) ?>:</strong> <?= esc((string) lang('Module.impactLevel' . ucfirst((string) ($entry['impact_level'] ?? 'medium')))) ?></div>
                                            <?php endif; ?>
                                            <?php if ($isIssueModule && (string) ($entry['mitigation_actions'] ?? '') !== ''): ?>
                                                <div class="text-muted small"><strong><?= esc(lang('Module.raidColumnMitigationActions')) ?>:</strong> <?= esc((string) ($entry['mitigation_actions'] ?? '')) ?></div>
                                            <?php endif; ?>
                                            <?php if ($isTaskModule && (string) ($entry['task_category'] ?? '') !== ''): ?>
                                                <div class="text-muted small"><strong><?= esc(lang('Module.tasksTaskCategoryLabel')) ?>:</strong> <?= esc((string) ($entry['task_category'] ?? '')) ?></div>
                                            <?php endif; ?>
                                            <?php if ($isTaskModule && (string) ($entry['related_objective'] ?? '') !== ''): ?>
                                                <div class="text-muted small"><strong><?= esc(lang('Module.tasksRelatedObjectiveLabel')) ?>:</strong> <?= esc((string) ($entry['related_objective'] ?? '')) ?></div>
                                            <?php endif; ?>
                                            <?php if ($isTaskModule && (string) ($entry['due_date'] ?? '') !== ''): ?>
                                                <div class="text-muted small"><strong><?= esc(lang('Module.tasksDueDateLabel')) ?>:</strong> <?= esc((string) ($entry['due_date'] ?? '')) ?></div>
                                            <?php endif; ?>
                                            <?php if ($isTaskModule): ?>
                                                <div class="text-muted small"><strong><?= esc(lang('Module.tasksPercentCompleteLabel')) ?>:</strong> <?= esc((string) ((int) ($entry['percent_complete'] ?? 0))) ?>%</div>
                                            <?php endif; ?>
                                            <?php if ($isTaskModule && (string) ($entry['next_action'] ?? '') !== ''): ?>
                                                <div class="text-muted small"><strong><?= esc(lang('Module.tasksNextActionLabel')) ?>:</strong> <?= esc((string) ($entry['next_action'] ?? '')) ?></div>
                                            <?php endif; ?>
                                            <?php if ($isAssumptionModule && (string) ($entry['lessons_learned'] ?? '') !== ''): ?>
                                                <div class="text-muted small"><strong><?= esc(lang('Module.assumptionsColumnLessonsLearned')) ?>:</strong> <?= esc((string) ($entry['lessons_learned'] ?? '')) ?></div>
                                            <?php endif; ?>
                                            <?php if (($isDecisionModule || $isDependencyModule || $isIssueModule || $isTaskModule) && (string) ($entry['lessons_learned'] ?? '') !== ''): ?>
                                                <div class="text-muted small"><strong><?= esc(lang('Module.assumptionsColumnLessonsLearned')) ?>:</strong> <?= esc((string) ($entry['lessons_learned'] ?? '')) ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div data-risk-editor class="d-none d-grid gap-2">
                                            <input form="<?= esc($riskEditFormId) ?>" name="title" type="text" class="form-control form-control-sm" maxlength="200" value="<?= esc((string) ($entry['title'] ?? '')) ?>" data-risk-row-editable disabled <?= $isDecisionModule ? '' : 'required' ?>>
                                            <?php if (! $isAssumptionModule): ?>
                                                <textarea form="<?= esc($riskEditFormId) ?>" name="description" rows="2" class="form-control form-control-sm" data-risk-row-editable disabled><?= esc((string) ($entry['description'] ?? '')) ?></textarea>
                                            <?php endif; ?>
                                            <?php if ($isDecisionModule): ?>
                                                <input form="<?= esc($riskEditFormId) ?>" name="decision_date" type="date" class="form-control form-control-sm" value="<?= esc((string) ($entry['decision_date'] ?? '')) ?>" data-risk-row-editable disabled>
                                                <select form="<?= esc($riskEditFormId) ?>" name="made_by_user_id" class="form-select form-select-sm" data-risk-row-editable disabled>
                                                    <option value=""></option>
                                                    <?php foreach ($owners as $owner): ?>
                                                        <option value="<?= (int) ($owner['id'] ?? 0) ?>" <?= (int) ($entry['made_by_user_id'] ?? 0) === (int) ($owner['id'] ?? 0) ? 'selected' : '' ?>><?= esc((string) ($owner['username'] ?? '')) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <select form="<?= esc($riskEditFormId) ?>" name="approver_user_id" class="form-select form-select-sm" data-risk-row-editable disabled>
                                                    <option value=""></option>
                                                    <?php foreach ($owners as $owner): ?>
                                                        <option value="<?= (int) ($owner['id'] ?? 0) ?>" <?= (int) ($entry['approver_user_id'] ?? 0) === (int) ($owner['id'] ?? 0) ? 'selected' : '' ?>><?= esc((string) ($owner['username'] ?? '')) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <input form="<?= esc($riskEditFormId) ?>" name="decision_category" type="text" class="form-control form-control-sm" maxlength="100" value="<?= esc((string) ($entry['decision_category'] ?? '')) ?>" placeholder="<?= esc(lang('Module.decisionsCategoryLabel')) ?>" data-risk-row-editable disabled>
                                                <textarea form="<?= esc($riskEditFormId) ?>" name="decision_rationale" rows="2" class="form-control form-control-sm" placeholder="<?= esc(lang('Module.decisionsRationaleLabel')) ?>" data-risk-row-editable disabled><?= esc((string) ($entry['decision_rationale'] ?? '')) ?></textarea>
                                                <textarea form="<?= esc($riskEditFormId) ?>" name="alternatives_considered" rows="2" class="form-control form-control-sm" placeholder="<?= esc(lang('Module.decisionsAlternativesLabel')) ?>" data-risk-row-editable disabled><?= esc((string) ($entry['alternatives_considered'] ?? '')) ?></textarea>
                                                <textarea form="<?= esc($riskEditFormId) ?>" name="chosen_option" rows="2" class="form-control form-control-sm" placeholder="<?= esc(lang('Module.decisionsChosenOptionLabel')) ?>" data-risk-row-editable disabled><?= esc((string) ($entry['chosen_option'] ?? '')) ?></textarea>
                                                <textarea form="<?= esc($riskEditFormId) ?>" name="implementation_actions" rows="2" class="form-control form-control-sm" placeholder="<?= esc(lang('Module.decisionsImplementationActionsLabel')) ?>" data-risk-row-editable disabled><?= esc((string) ($entry['implementation_actions'] ?? '')) ?></textarea>
                                                <textarea form="<?= esc($riskEditFormId) ?>" name="lessons_learned" rows="2" class="form-control form-control-sm" placeholder="<?= esc(lang('Module.assumptionsColumnLessonsLearned')) ?>" data-risk-row-editable disabled><?= esc((string) ($entry['lessons_learned'] ?? '')) ?></textarea>
                                                <input form="<?= esc($riskEditFormId) ?>" name="superseded_by_entry_id" type="number" min="1" class="form-control form-control-sm" value="<?= esc((string) ($entry['superseded_by_entry_id'] ?? '')) ?>" placeholder="<?= esc(lang('Module.decisionsSupersededByLabel')) ?>" data-risk-row-editable disabled>
                                            <?php endif; ?>
                                            <?php if ($isDependencyModule): ?>
                                                <select form="<?= esc($riskEditFormId) ?>" name="dependency_type" class="form-select form-select-sm" data-risk-row-editable disabled>
                                                    <option value=""></option>
                                                    <?php foreach (['internal', 'external', 'supplier', 'customer', 'technical', 'regulatory', 'other'] as $dependencyType): ?>
                                                        <option value="<?= esc($dependencyType) ?>" <?= (string) ($entry['dependency_type'] ?? '') === $dependencyType ? 'selected' : '' ?>><?= esc((string) lang('Module.dependenciesType' . ucfirst($dependencyType))) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <input form="<?= esc($riskEditFormId) ?>" name="related_work_package" type="text" class="form-control form-control-sm" maxlength="255" value="<?= esc((string) ($entry['related_work_package'] ?? '')) ?>" placeholder="<?= esc(lang('Module.dependenciesRelatedWorkLabel')) ?>" data-risk-row-editable disabled>
                                                <input form="<?= esc($riskEditFormId) ?>" name="depends_on" type="text" class="form-control form-control-sm" maxlength="255" value="<?= esc((string) ($entry['depends_on'] ?? '')) ?>" placeholder="<?= esc(lang('Module.dependenciesDependsOnLabel')) ?>" data-risk-row-editable disabled>
                                                <textarea form="<?= esc($riskEditFormId) ?>" name="mitigation_actions" rows="2" class="form-control form-control-sm" placeholder="<?= esc(lang('Module.raidColumnMitigationActions')) ?>" data-risk-row-editable disabled><?= esc((string) ($entry['mitigation_actions'] ?? '')) ?></textarea>
                                                <textarea form="<?= esc($riskEditFormId) ?>" name="lessons_learned" rows="2" class="form-control form-control-sm" placeholder="<?= esc(lang('Module.assumptionsColumnLessonsLearned')) ?>" data-risk-row-editable disabled><?= esc((string) ($entry['lessons_learned'] ?? '')) ?></textarea>
                                                <div class="form-check">
                                                    <input form="<?= esc($riskEditFormId) ?>" class="form-check-input" type="checkbox" name="escalation_required" value="1" id="<?= esc('escalation-required-' . (int) ($entry['id'] ?? 0)) ?>" <?= (int) ($entry['escalation_required'] ?? 0) === 1 ? 'checked' : '' ?> data-risk-row-editable disabled>
                                                    <label class="form-check-label" for="<?= esc('escalation-required-' . (int) ($entry['id'] ?? 0)) ?>"><?= esc(lang('Module.dependenciesEscalationRequiredLabel')) ?></label>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($isAssumptionModule): ?>
                                                <textarea form="<?= esc($riskEditFormId) ?>" name="lessons_learned" rows="2" class="form-control form-control-sm" data-risk-row-editable disabled><?= esc((string) ($entry['lessons_learned'] ?? '')) ?></textarea>
                                                <input form="<?= esc($riskEditFormId) ?>" type="hidden" name="priority" value="<?= esc((string) ($entry['priority'] ?? 'medium')) ?>">
                                            <?php endif; ?>
                                            <?php if ($isIssueModule): ?>
                                                <input form="<?= esc($riskEditFormId) ?>" name="date_reported" type="date" class="form-control form-control-sm" value="<?= esc((string) ($entry['date_reported'] ?? '')) ?>" data-risk-row-editable disabled>
                                                <select form="<?= esc($riskEditFormId) ?>" name="reporter_user_id" class="form-select form-select-sm" data-risk-row-editable disabled>
                                                    <option value=""></option>
                                                    <?php foreach ($owners as $owner): ?>
                                                        <option value="<?= (int) ($owner['id'] ?? 0) ?>" <?= (int) ($entry['reporter_user_id'] ?? 0) === (int) ($owner['id'] ?? 0) ? 'selected' : '' ?>><?= esc((string) ($owner['username'] ?? '')) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <select form="<?= esc($riskEditFormId) ?>" name="impact_level" class="form-select form-select-sm" data-risk-row-editable disabled>
                                                    <option value=""></option>
                                                    <?php foreach ($impactLevelOptions as $option): ?>
                                                        <option value="<?= esc($option) ?>" <?= (string) ($entry['impact_level'] ?? '') === $option ? 'selected' : '' ?>><?= esc((string) lang('Module.impactLevel' . ucfirst($option))) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <textarea form="<?= esc($riskEditFormId) ?>" name="mitigation_actions" rows="2" class="form-control form-control-sm" placeholder="<?= esc(lang('Module.raidColumnMitigationActions')) ?>" data-risk-row-editable disabled><?= esc((string) ($entry['mitigation_actions'] ?? '')) ?></textarea>
                                                <textarea form="<?= esc($riskEditFormId) ?>" name="lessons_learned" rows="2" class="form-control form-control-sm" placeholder="<?= esc(lang('Module.assumptionsColumnLessonsLearned')) ?>" data-risk-row-editable disabled><?= esc((string) ($entry['lessons_learned'] ?? '')) ?></textarea>
                                            <?php endif; ?>
                                            <?php if ($isTaskModule): ?>
                                                <input form="<?= esc($riskEditFormId) ?>" name="task_category" type="text" class="form-control form-control-sm" maxlength="100" value="<?= esc((string) ($entry['task_category'] ?? '')) ?>" placeholder="<?= esc(lang('Module.tasksTaskCategoryLabel')) ?>" data-risk-row-editable disabled>
                                                <input form="<?= esc($riskEditFormId) ?>" name="related_objective" type="text" class="form-control form-control-sm" maxlength="255" value="<?= esc((string) ($entry['related_objective'] ?? '')) ?>" placeholder="<?= esc(lang('Module.tasksRelatedObjectiveLabel')) ?>" data-risk-row-editable disabled>
                                                <input form="<?= esc($riskEditFormId) ?>" name="related_module_entry_id" type="number" min="1" class="form-control form-control-sm" value="<?= esc((string) ($entry['related_module_entry_id'] ?? '')) ?>" placeholder="<?= esc(lang('Module.tasksRelatedModuleEntryLabel')) ?>" data-risk-row-editable disabled>
                                                <textarea form="<?= esc($riskEditFormId) ?>" name="collaborators" rows="2" class="form-control form-control-sm" placeholder="<?= esc(lang('Module.tasksCollaboratorsLabel')) ?>" data-risk-row-editable disabled><?= esc((string) ($entry['collaborators'] ?? '')) ?></textarea>
                                                <input form="<?= esc($riskEditFormId) ?>" name="percent_complete" type="number" min="0" max="100" class="form-control form-control-sm" value="<?= esc((string) ($entry['percent_complete'] ?? 0)) ?>" placeholder="<?= esc(lang('Module.tasksPercentCompleteLabel')) ?>" data-risk-row-editable disabled>
                                                <input form="<?= esc($riskEditFormId) ?>" name="planned_start_date" type="date" class="form-control form-control-sm" value="<?= esc((string) ($entry['planned_start_date'] ?? '')) ?>" data-risk-row-editable disabled>
                                                <input form="<?= esc($riskEditFormId) ?>" name="due_date" type="date" class="form-control form-control-sm" value="<?= esc((string) ($entry['due_date'] ?? '')) ?>" data-risk-row-editable disabled>
                                                <input form="<?= esc($riskEditFormId) ?>" name="completed_date" type="date" class="form-control form-control-sm" value="<?= esc((string) ($entry['completed_date'] ?? '')) ?>" data-risk-row-editable disabled>
                                                <textarea form="<?= esc($riskEditFormId) ?>" name="blocked_reason" rows="2" class="form-control form-control-sm" placeholder="<?= esc(lang('Module.tasksBlockedReasonLabel')) ?>" data-risk-row-editable disabled><?= esc((string) ($entry['blocked_reason'] ?? '')) ?></textarea>
                                                <textarea form="<?= esc($riskEditFormId) ?>" name="next_action" rows="2" class="form-control form-control-sm" placeholder="<?= esc(lang('Module.tasksNextActionLabel')) ?>" data-risk-row-editable disabled><?= esc((string) ($entry['next_action'] ?? '')) ?></textarea>
                                                <textarea form="<?= esc($riskEditFormId) ?>" name="lessons_learned" rows="2" class="form-control form-control-sm" placeholder="<?= esc(lang('Module.assumptionsColumnLessonsLearned')) ?>" data-risk-row-editable disabled><?= esc((string) ($entry['lessons_learned'] ?? '')) ?></textarea>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <?php if ($isRiskModule): ?>
                                    <td>
                                        <div data-risk-display class="text-muted small mb-0"><?= esc((string) ($entry['description'] ?? '')) ?></div>
                                        <div data-risk-editor class="d-none">
                                            <textarea form="<?= esc($riskEditFormId) ?>" name="description" rows="2" class="form-control form-control-sm" data-risk-row-editable disabled><?= esc((string) ($entry['description'] ?? '')) ?></textarea>
                                        </div>
                                    </td>
                                    <td>
                                        <div data-risk-display class="text-muted small mb-0"><?= esc((string) ($entry['mitigation_actions'] ?? '')) ?></div>
                                        <div data-risk-editor class="d-none">
                                            <textarea form="<?= esc($riskEditFormId) ?>" name="mitigation_actions" rows="2" class="form-control form-control-sm" data-risk-row-editable disabled><?= esc((string) ($entry['mitigation_actions'] ?? '')) ?></textarea>
                                        </div>
                                    </td>
                                <?php endif; ?>
                                <td>
                                    <?php if ($isRiskModule): ?>
                                        <div data-risk-display><?= esc((string) ($entry['owner_username'] ?? '')) ?></div>
                                        <div data-risk-editor class="d-none">
                                            <select form="<?= esc($riskEditFormId) ?>" name="owner_user_id" class="form-select form-select-sm" data-risk-row-editable disabled required>
                                                <?php foreach ($owners as $owner): ?>
                                                    <option value="<?= (int) ($owner['id'] ?? 0) ?>" <?= (int) ($entry['owner_user_id'] ?? 0) === (int) ($owner['id'] ?? 0) ? 'selected' : '' ?>><?= esc((string) ($owner['username'] ?? '')) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    <?php else: ?>
                                        <div data-risk-display><?= esc((string) ($entry['owner_username'] ?? '')) ?></div>
                                        <div data-risk-editor class="d-none">
                                            <select form="<?= esc($riskEditFormId) ?>" name="owner_user_id" class="form-select form-select-sm" data-risk-row-editable disabled required>
                                                <?php foreach ($owners as $owner): ?>
                                                    <option value="<?= (int) ($owner['id'] ?? 0) ?>" <?= (int) ($entry['owner_user_id'] ?? 0) === (int) ($owner['id'] ?? 0) ? 'selected' : '' ?>><?= esc((string) ($owner['username'] ?? '')) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($isRiskModule): ?>
                                        <div data-risk-display><?= esc((string) lang('Module.raidStatus' . ucfirst((string) ($entry['status'] ?? 'open')))) ?></div>
                                        <div data-risk-editor class="d-none">
                                            <select form="<?= esc($riskEditFormId) ?>" name="status" class="form-select form-select-sm" data-risk-row-editable disabled required>
                                                <?php foreach ($statusOptions as $statusOption): ?>
                                                    <option value="<?= esc($statusOption) ?>" <?= (string) ($entry['status'] ?? 'open') === $statusOption ? 'selected' : '' ?>><?= esc((string) lang('Module.raidStatus' . ucfirst($statusOption))) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    <?php else: ?>
                                        <div data-risk-display><?= esc((string) lang('Module.raidStatus' . ucfirst((string) ($entry['status'] ?? 'open')))) ?></div>
                                        <div data-risk-editor class="d-none">
                                            <select form="<?= esc($riskEditFormId) ?>" name="status" class="form-select form-select-sm" data-risk-row-editable disabled required>
                                                <?php foreach ($statusOptions as $statusOption): ?>
                                                    <option value="<?= esc($statusOption) ?>" <?= (string) ($entry['status'] ?? 'open') === $statusOption ? 'selected' : '' ?>><?= esc((string) lang('Module.raidStatus' . ucfirst($statusOption))) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <?php if ($isAssumptionModule): ?>
                                    <td>
                                        <div data-risk-display><?= esc((string) ($entry['impact_if_not_valid'] ?? '')) ?></div>
                                        <div data-risk-editor class="d-none">
                                            <textarea form="<?= esc($riskEditFormId) ?>" name="impact_if_not_valid" rows="2" class="form-control form-control-sm" data-risk-row-editable disabled><?= esc((string) ($entry['impact_if_not_valid'] ?? '')) ?></textarea>
                                        </div>
                                    </td>
                                    <td>
                                        <div data-risk-display><?= esc((string) ($entry['mitigation_actions'] ?? '')) ?></div>
                                        <div data-risk-editor class="d-none">
                                            <textarea form="<?= esc($riskEditFormId) ?>" name="validation_actions" rows="2" class="form-control form-control-sm" data-risk-row-editable disabled><?= esc((string) ($entry['mitigation_actions'] ?? '')) ?></textarea>
                                        </div>
                                    </td>
                                    <td>
                                        <div data-risk-display><?= esc((string) lang('Module.impactLevel' . ucfirst((string) ($entry['impact_level'] ?? 'medium')))) ?></div>
                                        <div data-risk-editor class="d-none">
                                            <select form="<?= esc($riskEditFormId) ?>" name="impact_level" class="form-select form-select-sm" data-risk-row-editable disabled>
                                                <option value=""></option>
                                                <?php foreach ($impactLevelOptions as $option): ?>
                                                    <option value="<?= esc($option) ?>" <?= (string) ($entry['impact_level'] ?? '') === $option ? 'selected' : '' ?>><?= esc((string) lang('Module.impactLevel' . ucfirst($option))) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </td>
                                <?php endif; ?>
                                <?php if ($isRiskModule): ?>
                                    <td>
                                        <div data-risk-display><?= esc((string) lang('Module.riskImpact' . ucfirst((string) ($entry['impact'] ?? 'low')))) ?></div>
                                        <div data-risk-editor class="d-none">
                                            <select form="<?= esc($riskEditFormId) ?>" name="impact" class="form-select form-select-sm" data-risk-row-editable disabled required>
                                                <?php foreach ($riskScaleOptions as $option): ?>
                                                    <option value="<?= esc($option) ?>" <?= (string) ($entry['impact'] ?? 'low') === $option ? 'selected' : '' ?>><?= esc((string) lang('Module.riskImpact' . ucfirst($option))) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <div data-risk-display><?= esc((string) lang('Module.riskLikelihood' . ucfirst((string) ($entry['likelihood'] ?? 'low')))) ?></div>
                                        <div data-risk-editor class="d-none">
                                            <select form="<?= esc($riskEditFormId) ?>" name="likelihood" class="form-select form-select-sm" data-risk-row-editable disabled required>
                                                <?php foreach ($riskScaleOptions as $option): ?>
                                                    <option value="<?= esc($option) ?>" <?= (string) ($entry['likelihood'] ?? 'low') === $option ? 'selected' : '' ?>><?= esc((string) lang('Module.riskLikelihood' . ucfirst($option))) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </td>
                                <?php endif; ?>
                                <td>
                                    <?php if ($isRiskModule || $isAssumptionModule): ?>
                                        <div data-risk-display><?= esc((string) lang('Module.raidPriority' . ucfirst((string) ($entry['priority'] ?? 'medium')))) ?></div>
                                    <?php else: ?>
                                        <div data-risk-display><?= esc((string) lang('Module.raidPriority' . ucfirst((string) ($entry['priority'] ?? 'medium')))) ?></div>
                                        <div data-risk-editor class="d-none">
                                            <select form="<?= esc($riskEditFormId) ?>" name="priority" class="form-select form-select-sm" data-risk-row-editable disabled required>
                                                <?php foreach ($priorityOptions as $priorityOption): ?>
                                                    <option value="<?= esc($priorityOption) ?>" <?= (string) ($entry['priority'] ?? 'medium') === $priorityOption ? 'selected' : '' ?>><?= esc((string) lang('Module.raidPriority' . ucfirst($priorityOption))) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="<?= $isRiskModule ? '' : 'd-none d-md-table-cell' ?>">
                                    <?php if ($isRiskModule): ?>
                                        <div data-risk-display><?= esc((string) ($entry['target_date'] ?? '')) ?></div>
                                        <div data-risk-editor class="d-none">
                                            <input form="<?= esc($riskEditFormId) ?>" name="target_date" type="date" class="form-control form-control-sm" value="<?= esc((string) ($entry['target_date'] ?? '')) ?>" data-risk-row-editable disabled>
                                        </div>
                                    <?php else: ?>
                                        <div data-risk-display><?= esc((string) ($entry['target_date'] ?? '')) ?></div>
                                        <div data-risk-editor class="d-none">
                                            <input form="<?= esc($riskEditFormId) ?>" name="target_date" type="date" class="form-control form-control-sm" value="<?= esc((string) ($entry['target_date'] ?? '')) ?>" data-risk-row-editable disabled>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="<?= $isRiskModule ? '' : 'd-none d-md-table-cell' ?>">
                                    <?php if ($isRiskModule): ?>
                                        <div data-risk-display><?= esc((string) ($entry['review_date'] ?? '')) ?></div>
                                        <div data-risk-editor class="d-none">
                                            <input form="<?= esc($riskEditFormId) ?>" name="review_date" type="date" class="form-control form-control-sm" value="<?= esc((string) ($entry['review_date'] ?? '')) ?>" data-risk-row-editable disabled>
                                        </div>
                                    <?php else: ?>
                                        <div data-risk-display><?= esc((string) ($entry['review_date'] ?? '')) ?></div>
                                        <div data-risk-editor class="d-none">
                                            <input form="<?= esc($riskEditFormId) ?>" name="review_date" type="date" class="form-control form-control-sm" value="<?= esc((string) ($entry['review_date'] ?? '')) ?>" data-risk-row-editable disabled>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="<?= $isRiskModule ? '' : 'd-none d-lg-table-cell' ?>"><?= esc((string) ($entry['updated_at'] ?? '')) ?></td>
                                <?php if (! $isReadOnly): ?>
                                    <td>
                                        <form id="<?= esc($riskEditFormId) ?>" method="post" action="<?= site_url('projects/' . (int) ($project['id'] ?? 0) . '/modules/' . $moduleRouteSegment . '/' . (int) ($entry['id'] ?? 0) . '/update') ?>" class="d-none" data-risk-edit-form="true">
                                            <?= csrf_field() ?>
                                        </form>
                                        <div class="d-grid gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-risk-edit-toggle><?= esc(lang('Module.raidEditButton')) ?></button>
                                            <button type="submit" form="<?= esc($riskEditFormId) ?>" class="btn btn-sm btn-primary d-none" data-risk-edit-save><?= esc(lang('Module.raidUpdateButton')) ?></button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary d-none" data-risk-edit-cancel><?= esc(lang('Domain.cancelButton')) ?></button>
                                            <form method="post" action="<?= site_url('projects/' . (int) ($project['id'] ?? 0) . '/modules/' . $moduleRouteSegment . '/' . (int) ($entry['id'] ?? 0) . '/delete') ?>" onsubmit="return window.confirm('<?= esc((string) lang('Module.raidDeleteConfirm'), 'js') ?>');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger w-100"><?= esc(lang('Module.raidDeleteButton')) ?></button>
                                            </form>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (! $isReadOnly && $isRiskModule): ?>
        <div class="modal fade" id="riskEntryCreateModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title h5 mb-0"><?= esc(lang('Module.addNewRisk')) ?></h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="post" action="<?= site_url('projects/' . (int) ($project['id'] ?? 0) . '/modules/' . $moduleRouteSegment) ?>">
                        <?= csrf_field() ?>
                        <div class="modal-body">
                            <div class="row g-2">
                                <div class="col-12">
                                    <label class="form-label" for="risk-create-title"><?= esc(lang('Module.raidColumnTitle')) ?></label>
                                    <input id="risk-create-title" name="title" type="text" class="form-control" maxlength="200" required>
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="risk-create-description"><?= esc(lang('Module.raidColumnDescription')) ?></label>
                                    <textarea id="risk-create-description" name="description" rows="3" class="form-control"></textarea>
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="risk-create-mitigation-actions"><?= esc(lang('Module.raidColumnMitigationActions')) ?></label>
                                    <textarea id="risk-create-mitigation-actions" name="mitigation_actions" rows="3" class="form-control"></textarea>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="risk-create-owner-user-id"><?= esc(lang('Module.raidColumnOwner')) ?></label>
                                    <select id="risk-create-owner-user-id" name="owner_user_id" class="form-select" required>
                                        <?php foreach ($owners as $owner): ?>
                                            <option value="<?= (int) ($owner['id'] ?? 0) ?>"><?= esc((string) ($owner['username'] ?? '')) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="risk-create-status"><?= esc(lang('Module.raidColumnStatus')) ?></label>
                                    <select id="risk-create-status" name="status" class="form-select" required>
                                        <?php foreach ($statusOptions as $statusOption): ?>
                                            <option value="<?= esc($statusOption) ?>"><?= esc((string) lang('Module.raidStatus' . ucfirst($statusOption))) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="risk-create-impact"><?= esc(lang('Module.raidColumnImpact')) ?></label>
                                    <select id="risk-create-impact" name="impact" class="form-select" required>
                                        <?php foreach ($riskScaleOptions as $option): ?>
                                            <option value="<?= esc($option) ?>"><?= esc((string) lang('Module.riskImpact' . ucfirst($option))) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="risk-create-likelihood"><?= esc(lang('Module.raidColumnLikelihood')) ?></label>
                                    <select id="risk-create-likelihood" name="likelihood" class="form-select" required>
                                        <?php foreach ($riskScaleOptions as $option): ?>
                                            <option value="<?= esc($option) ?>"><?= esc((string) lang('Module.riskLikelihood' . ucfirst($option))) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="risk-create-target-date"><?= esc(lang('Module.raidColumnTargetDate')) ?></label>
                                    <input id="risk-create-target-date" name="target_date" type="date" class="form-control">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="risk-create-review-date"><?= esc(lang('Module.raidColumnReviewDate')) ?></label>
                                    <input id="risk-create-review-date" name="review_date" type="date" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= esc(lang('Domain.cancelButton')) ?></button>
                            <button type="submit" class="btn btn-primary"><?= esc(lang('Module.raidCreateButton')) ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (! $isReadOnly && ! $isRiskModule): ?>
        <div class="modal fade" id="raidEntryCreateModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title h5 mb-0"><?= esc(lang('Module.raidCreateTitle')) ?></h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="post" action="<?= site_url('projects/' . (int) ($project['id'] ?? 0) . '/modules/' . $moduleRouteSegment) ?>">
                        <?= csrf_field() ?>
                        <div class="modal-body">
                            <div class="row g-2">
                                <div class="col-12 col-md-4">
                                    <label class="form-label" for="raid-create-title"><?= esc($isDecisionModule ? lang('Module.decisionsDescriptionLabel') : ($isAssumptionModule ? lang('Module.assumptionsColumnDescription') : lang('Module.raidColumnTitle'))) ?></label>
                                    <?php if ($isDecisionModule): ?>
                                        <textarea id="raid-create-title" name="description" rows="2" class="form-control" required></textarea>
                                    <?php else: ?>
                                        <input id="raid-create-title" name="title" type="text" class="form-control" maxlength="200" required>
                                    <?php endif; ?>
                                </div>

                                <div class="col-12 col-md-4 <?= $isDecisionModule ? 'd-none' : '' ?>">
                                    <label class="form-label" for="raid-create-owner-user-id"><?= esc(lang('Module.raidColumnOwner')) ?></label>
                                    <select id="raid-create-owner-user-id" name="owner_user_id" class="form-select" <?= $isDecisionModule ? '' : 'required' ?>>
                                        <?php foreach ($owners as $owner): ?>
                                            <option value="<?= (int) ($owner['id'] ?? 0) ?>"><?= esc((string) ($owner['username'] ?? '')) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-6 col-md-2 <?= $isDecisionModule ? 'd-none' : '' ?>">
                                    <label class="form-label" for="raid-create-status"><?= esc(lang('Module.raidColumnStatus')) ?></label>
                                    <select id="raid-create-status" name="status" class="form-select" <?= $isDecisionModule ? '' : 'required' ?>>
                                        <?php foreach ($statusOptions as $statusOption): ?>
                                            <option value="<?= esc($statusOption) ?>"><?= esc((string) lang('Module.raidStatus' . ucfirst($statusOption))) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-6 col-md-2 <?= ($isDecisionModule || $isAssumptionModule) ? 'd-none' : '' ?>">
                                    <label class="form-label" for="raid-create-priority"><?= esc(lang('Module.raidColumnPriority')) ?></label>
                                    <select id="raid-create-priority" name="priority" class="form-select" required>
                                        <?php foreach ($priorityOptions as $priorityOption): ?>
                                            <option value="<?= esc($priorityOption) ?>"><?= esc((string) lang('Module.raidPriority' . ucfirst($priorityOption))) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-12 <?= ($isDecisionModule || $isAssumptionModule) ? 'd-none' : '' ?>">
                                    <label class="form-label" for="raid-create-description"><?= esc(lang('Module.raidColumnDescription')) ?></label>
                                    <textarea id="raid-create-description" name="description" rows="2" class="form-control"></textarea>
                                </div>

                                <?php if ($isAssumptionModule): ?>
                                    <div class="col-12">
                                        <label class="form-label" for="raid-create-impact-if-not-valid"><?= esc(lang('Module.raidColumnImpactIfNotValid')) ?></label>
                                        <textarea id="raid-create-impact-if-not-valid" name="impact_if_not_valid" rows="2" class="form-control"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="raid-create-validation-actions"><?= esc(lang('Module.assumptionsColumnValidationActions')) ?></label>
                                        <textarea id="raid-create-validation-actions" name="validation_actions" rows="2" class="form-control"></textarea>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="form-label" for="raid-create-impact-level"><?= esc(lang('Module.raidColumnImpactLevel')) ?></label>
                                        <select id="raid-create-impact-level" name="impact_level" class="form-select">
                                            <option value=""></option>
                                            <?php foreach ($impactLevelOptions as $option): ?>
                                                <option value="<?= esc($option) ?>"><?= esc((string) lang('Module.impactLevel' . ucfirst($option))) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="raid-create-lessons-learned"><?= esc(lang('Module.assumptionsColumnLessonsLearned')) ?></label>
                                        <textarea id="raid-create-lessons-learned" name="lessons_learned" rows="2" class="form-control"></textarea>
                                    </div>
                                    <input type="hidden" name="priority" value="medium">
                                <?php endif; ?>

                                <?php if ($isDependencyModule): ?>
                                    <div class="col-6 col-md-3">
                                        <label class="form-label" for="raid-create-impact-level"><?= esc(lang('Module.raidColumnImpactLevel')) ?></label>
                                        <select id="raid-create-impact-level" name="impact_level" class="form-select">
                                            <option value=""></option>
                                            <?php foreach ($impactLevelOptions as $option): ?>
                                                <option value="<?= esc($option) ?>"><?= esc((string) lang('Module.impactLevel' . ucfirst($option))) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="form-label" for="raid-create-dependency-type"><?= esc(lang('Module.dependenciesTypeLabel')) ?></label>
                                        <select id="raid-create-dependency-type" name="dependency_type" class="form-select">
                                            <option value=""></option>
                                            <?php foreach (['internal', 'external', 'supplier', 'customer', 'technical', 'regulatory', 'other'] as $dependencyType): ?>
                                                <option value="<?= esc($dependencyType) ?>"><?= esc((string) lang('Module.dependenciesType' . ucfirst($dependencyType))) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label" for="raid-create-related-work-package"><?= esc(lang('Module.dependenciesRelatedWorkLabel')) ?></label>
                                        <input id="raid-create-related-work-package" name="related_work_package" type="text" class="form-control" maxlength="255">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label" for="raid-create-depends-on"><?= esc(lang('Module.dependenciesDependsOnLabel')) ?></label>
                                        <input id="raid-create-depends-on" name="depends_on" type="text" class="form-control" maxlength="255">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="raid-create-dependency-mitigation-actions"><?= esc(lang('Module.raidColumnMitigationActions')) ?></label>
                                        <textarea id="raid-create-dependency-mitigation-actions" name="mitigation_actions" rows="2" class="form-control"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="raid-create-dependency-lessons-learned"><?= esc(lang('Module.assumptionsColumnLessonsLearned')) ?></label>
                                        <textarea id="raid-create-dependency-lessons-learned" name="lessons_learned" rows="2" class="form-control"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check mt-2">
                                            <input id="raid-create-escalation-required" class="form-check-input" type="checkbox" name="escalation_required" value="1">
                                            <label class="form-check-label" for="raid-create-escalation-required"><?= esc(lang('Module.dependenciesEscalationRequiredLabel')) ?></label>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($isIssueModule): ?>
                                    <div class="col-6 col-md-3">
                                        <label class="form-label" for="raid-create-date-reported"><?= esc(lang('Module.raidColumnReported')) ?></label>
                                        <input id="raid-create-date-reported" name="date_reported" type="date" class="form-control">
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="form-label" for="raid-create-reporter-user-id"><?= esc(lang('Module.raidColumnReporter')) ?></label>
                                        <select id="raid-create-reporter-user-id" name="reporter_user_id" class="form-select">
                                            <option value=""></option>
                                            <?php foreach ($owners as $owner): ?>
                                                <option value="<?= (int) ($owner['id'] ?? 0) ?>"><?= esc((string) ($owner['username'] ?? '')) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="form-label" for="raid-create-issue-impact-level"><?= esc(lang('Module.raidColumnImpactLevel')) ?></label>
                                        <select id="raid-create-issue-impact-level" name="impact_level" class="form-select">
                                            <option value=""></option>
                                            <?php foreach ($impactLevelOptions as $option): ?>
                                                <option value="<?= esc($option) ?>"><?= esc((string) lang('Module.impactLevel' . ucfirst($option))) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="raid-create-issue-resolution-actions"><?= esc(lang('Module.raidColumnMitigationActions')) ?></label>
                                        <textarea id="raid-create-issue-resolution-actions" name="mitigation_actions" rows="2" class="form-control"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="raid-create-issue-lessons-learned"><?= esc(lang('Module.assumptionsColumnLessonsLearned')) ?></label>
                                        <textarea id="raid-create-issue-lessons-learned" name="lessons_learned" rows="2" class="form-control"></textarea>
                                    </div>
                                <?php endif; ?>

                                <?php if ($isTaskModule): ?>
                                    <div class="col-6 col-md-3">
                                        <label class="form-label" for="raid-create-task-category"><?= esc(lang('Module.tasksTaskCategoryLabel')) ?></label>
                                        <input id="raid-create-task-category" name="task_category" type="text" class="form-control" maxlength="100">
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="form-label" for="raid-create-task-related-objective"><?= esc(lang('Module.tasksRelatedObjectiveLabel')) ?></label>
                                        <input id="raid-create-task-related-objective" name="related_objective" type="text" class="form-control" maxlength="255">
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="form-label" for="raid-create-task-related-module-entry-id"><?= esc(lang('Module.tasksRelatedModuleEntryLabel')) ?></label>
                                        <input id="raid-create-task-related-module-entry-id" name="related_module_entry_id" type="number" min="1" class="form-control">
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="form-label" for="raid-create-task-percent-complete"><?= esc(lang('Module.tasksPercentCompleteLabel')) ?></label>
                                        <input id="raid-create-task-percent-complete" name="percent_complete" type="number" min="0" max="100" value="0" class="form-control">
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="form-label" for="raid-create-task-planned-start-date"><?= esc(lang('Module.tasksPlannedStartDateLabel')) ?></label>
                                        <input id="raid-create-task-planned-start-date" name="planned_start_date" type="date" class="form-control">
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="form-label" for="raid-create-task-due-date"><?= esc(lang('Module.tasksDueDateLabel')) ?></label>
                                        <input id="raid-create-task-due-date" name="due_date" type="date" class="form-control">
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="form-label" for="raid-create-task-completed-date"><?= esc(lang('Module.tasksCompletedDateLabel')) ?></label>
                                        <input id="raid-create-task-completed-date" name="completed_date" type="date" class="form-control">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="raid-create-task-collaborators"><?= esc(lang('Module.tasksCollaboratorsLabel')) ?></label>
                                        <textarea id="raid-create-task-collaborators" name="collaborators" rows="2" class="form-control"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="raid-create-task-blocked-reason"><?= esc(lang('Module.tasksBlockedReasonLabel')) ?></label>
                                        <textarea id="raid-create-task-blocked-reason" name="blocked_reason" rows="2" class="form-control"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="raid-create-task-next-action"><?= esc(lang('Module.tasksNextActionLabel')) ?></label>
                                        <textarea id="raid-create-task-next-action" name="next_action" rows="2" class="form-control"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="raid-create-task-lessons-learned"><?= esc(lang('Module.assumptionsColumnLessonsLearned')) ?></label>
                                        <textarea id="raid-create-task-lessons-learned" name="lessons_learned" rows="2" class="form-control"></textarea>
                                    </div>
                                <?php endif; ?>

                                <div class="col-6 col-md-3">
                                    <label class="form-label" for="raid-create-target-date"><?= esc(lang('Module.raidColumnTargetDate')) ?></label>
                                    <input id="raid-create-target-date" name="target_date" type="date" class="form-control">
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label" for="raid-create-review-date"><?= esc(lang('Module.raidColumnReviewDate')) ?></label>
                                    <input id="raid-create-review-date" name="review_date" type="date" class="form-control">
                                </div>

                                <?php if ($isDecisionModule): ?>
                                    <div class="col-6 col-md-3">
                                        <label class="form-label" for="raid-create-decision-date"><?= esc(lang('Module.decisionsDateLabel')) ?></label>
                                        <input id="raid-create-decision-date" name="decision_date" type="date" class="form-control" required>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="form-label" for="raid-create-made-by-user-id"><?= esc(lang('Module.decisionsMadeByLabel')) ?></label>
                                        <select id="raid-create-made-by-user-id" name="made_by_user_id" class="form-select" required>
                                            <?php foreach ($owners as $owner): ?>
                                                <option value="<?= (int) ($owner['id'] ?? 0) ?>"><?= esc((string) ($owner['username'] ?? '')) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="form-label" for="raid-create-approver-user-id"><?= esc(lang('Module.decisionsApproverLabel')) ?></label>
                                        <select id="raid-create-approver-user-id" name="approver_user_id" class="form-select">
                                            <option value=""></option>
                                            <?php foreach ($owners as $owner): ?>
                                                <option value="<?= (int) ($owner['id'] ?? 0) ?>"><?= esc((string) ($owner['username'] ?? '')) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="form-label" for="raid-create-decision-category"><?= esc(lang('Module.decisionsCategoryLabel')) ?></label>
                                        <input id="raid-create-decision-category" name="decision_category" type="text" class="form-control" maxlength="100">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="raid-create-decision-rationale"><?= esc(lang('Module.decisionsRationaleLabel')) ?></label>
                                        <textarea id="raid-create-decision-rationale" name="decision_rationale" rows="2" class="form-control"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="raid-create-alternatives-considered"><?= esc(lang('Module.decisionsAlternativesLabel')) ?></label>
                                        <textarea id="raid-create-alternatives-considered" name="alternatives_considered" rows="2" class="form-control"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="raid-create-chosen-option"><?= esc(lang('Module.decisionsChosenOptionLabel')) ?></label>
                                        <textarea id="raid-create-chosen-option" name="chosen_option" rows="2" class="form-control"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="raid-create-implementation-actions"><?= esc(lang('Module.decisionsImplementationActionsLabel')) ?></label>
                                        <textarea id="raid-create-implementation-actions" name="implementation_actions" rows="2" class="form-control"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="raid-create-decision-lessons-learned"><?= esc(lang('Module.assumptionsColumnLessonsLearned')) ?></label>
                                        <textarea id="raid-create-decision-lessons-learned" name="lessons_learned" rows="2" class="form-control"></textarea>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="form-label" for="raid-create-superseded-by-entry-id"><?= esc(lang('Module.decisionsSupersededByLabel')) ?></label>
                                        <input id="raid-create-superseded-by-entry-id" name="superseded_by_entry_id" type="number" min="1" class="form-control">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= esc(lang('Domain.cancelButton')) ?></button>
                            <button type="submit" class="btn btn-primary"><?= esc(lang('Module.raidCreateButton')) ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?= $this->endSection() ?>

<?= $this->section('extraScripts') ?>
    <?php if (! $isReadOnly): ?>
        <script>
            (function () {
                function refreshResponsiveRow(row, forceShowDetails) {
                    if (typeof window.jQuery === 'undefined' || typeof window.jQuery.fn.DataTable === 'undefined') {
                        return;
                    }

                    var tableElement = row.closest('table');
                    if (!tableElement || !window.jQuery.fn.DataTable.isDataTable(tableElement)) {
                        return;
                    }

                    var dt = window.jQuery(tableElement).DataTable();
                    if (!dt.responsive) {
                        return;
                    }

                    var rowId = row.id;
                    var rowApi = dt.row(row);
                    if (typeof rowApi.invalidate === 'function') {
                        rowApi.invalidate('dom');
                    }

                    dt.draw(false);
                    dt.responsive.rebuild();
                    dt.responsive.recalc();

                    if (!forceShowDetails || !rowId) {
                        return;
                    }

                    var refreshedRow = tableElement.querySelector('#' + rowId);
                    if (!refreshedRow) {
                        return;
                    }

                    var refreshedRowApi = dt.row(refreshedRow);
                    if (refreshedRowApi.child && typeof refreshedRowApi.child.isShown === 'function') {
                        if (refreshedRowApi.child.isShown()) {
                            refreshedRowApi.child.hide();
                        }

                        if (typeof dt.responsive.hasHidden === 'function' ? dt.responsive.hasHidden() : tableElement.classList.contains('collapsed')) {
                            refreshedRowApi.child.show();
                        }
                    }
                }

                function resolveDataRow(startNode) {
                    var row = startNode.closest('tr');
                    if (!row) {
                        return null;
                    }

                    if (row.id && row.id.indexOf('entry-') === 0) {
                        return row;
                    }

                    if (row.classList.contains('child') && row.previousElementSibling && row.previousElementSibling.id && row.previousElementSibling.id.indexOf('entry-') === 0) {
                        return row.previousElementSibling;
                    }

                    return null;
                }

                document.addEventListener('click', function (event) {
                    var editButton = event.target.closest('[data-risk-edit-toggle]');
                    if (editButton) {
                        var row = resolveDataRow(editButton);
                        if (!row) {
                            return;
                        }

                        var saveButton = row.querySelector('[data-risk-edit-save]');
                        var cancelButton = row.querySelector('[data-risk-edit-cancel]');
                        var fields = row.querySelectorAll('[data-risk-row-editable]');
                        var displayValues = row.querySelectorAll('[data-risk-display]');
                        var editors = row.querySelectorAll('[data-risk-editor]');

                        displayValues.forEach(function (el) {
                            el.classList.add('d-none');
                        });
                        editors.forEach(function (el) {
                            el.classList.remove('d-none');
                        });
                        fields.forEach(function (field) {
                            field.disabled = false;
                        });

                        if (saveButton) {
                            saveButton.classList.remove('d-none');
                        }
                        if (cancelButton) {
                            cancelButton.classList.remove('d-none');
                        }
                        editButton.classList.add('d-none');
                        refreshResponsiveRow(row, true);
                        return;
                    }

                    var cancelButton = event.target.closest('[data-risk-edit-cancel]');
                    if (cancelButton) {
                        window.location.reload();
                    }
                });
            })();
        </script>
    <?php endif; ?>
<?= $this->endSection() ?>

<?= $this->section('postMain') ?>
<?= view('layouts/datatable_assets') ?>
<?= $this->endSection() ?>
