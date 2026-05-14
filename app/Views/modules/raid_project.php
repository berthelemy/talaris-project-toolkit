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
                                            <div class="fw-semibold\"><?= esc((string) ($entry['title'] ?? '')) ?></div>
                                            <?php if ((string) ($entry['description'] ?? '') !== ''): ?>
                                                <div class="text-muted small\"><?= esc((string) ($entry['description'] ?? '')) ?></div>
                                            <?php endif; ?>
                                            <?php if ($isAssumptionModule && (string) ($entry['lessons_learned'] ?? '') !== ''): ?>
                                                <div class="text-muted small"><strong><?= esc(lang('Module.assumptionsColumnLessonsLearned')) ?>:</strong> <?= esc((string) ($entry['lessons_learned'] ?? '')) ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div data-risk-editor class="d-none d-grid gap-2">
                                            <input form="<?= esc($riskEditFormId) ?>" name="title" type="text" class="form-control form-control-sm" maxlength="200" value="<?= esc((string) ($entry['title'] ?? '')) ?>" data-risk-row-editable disabled <?= $isDecisionModule ? '' : 'required' ?>>
                                            <?php if (! $isAssumptionModule): ?>
                                                <textarea form="<?= esc($riskEditFormId) ?>" name="description" rows="2" class="form-control form-control-sm" data-risk-row-editable disabled><?= esc((string) ($entry['description'] ?? '')) ?></textarea>
                                            <?php endif; ?>
                                            <?php if ($isAssumptionModule): ?>
                                                <textarea form="<?= esc($riskEditFormId) ?>" name="lessons_learned" rows="2" class="form-control form-control-sm" data-risk-row-editable disabled><?= esc((string) ($entry['lessons_learned'] ?? '')) ?></textarea>
                                                <input form="<?= esc($riskEditFormId) ?>" type="hidden" name="priority" value="<?= esc((string) ($entry['priority'] ?? 'medium')) ?>">
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
                                <?php endif; ?>

                                <?php if ($isIssueModule): ?>
                                    <div class="col-6 col-md-3">
                                        <label class="form-label" for="raid-create-date-reported"><?= esc(lang('Module.raidColumnReported')) ?></label>
                                        <input id="raid-create-date-reported" name="date_reported" type="date" class="form-control">
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
