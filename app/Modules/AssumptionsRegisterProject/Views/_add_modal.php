<?php
if (defined('ASSUMPTIONS_WIDGET_ADD_MODAL_RENDERED')) {
    return;
}
define('ASSUMPTIONS_WIDGET_ADD_MODAL_RENDERED', true);
?>
<div class="modal fade" id="assumptionModalAdd" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= lang('Module.addNewAssumption') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="<?= site_url('projects/' . $scope_id . '/modules/assumptions-register') ?>">
                <?= csrf_field() ?>
                <div class="modal-body module-modal-body-scroll">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="assumption-title"><?= lang('Module.raidColumnTitle') ?></label>
                            <input id="assumption-title" class="form-control" name="title" type="text" maxlength="200" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="assumption-description"><?= lang('Module.raidColumnDescription') ?></label>
                            <textarea id="assumption-description" class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="assumption-impact-level"><?= lang('Module.raidColumnImpactLevel') ?></label>
                            <select id="assumption-impact-level" class="form-select" name="impact_level">
                                <option value=""><?= lang('Module.selectOption') ?></option>
                                <option value="low"><?= lang('Module.impactLevelLow') ?></option>
                                <option value="medium"><?= lang('Module.impactLevelMedium') ?></option>
                                <option value="high"><?= lang('Module.impactLevelHigh') ?></option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="assumption-owner"><?= lang('Module.raidColumnOwner') ?></label>
                            <select id="assumption-owner" class="form-select" name="owner_user_id">
                                <option value=""><?= lang('Module.selectOption') ?></option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="assumption-validation-actions"><?= lang('Module.assumptionsColumnValidationActions') ?></label>
                            <textarea id="assumption-validation-actions" class="form-control" name="validation_actions" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="assumption-lessons-learned"><?= lang('Module.assumptionsColumnLessonsLearned') ?></label>
                            <textarea id="assumption-lessons-learned" class="form-control" name="lessons_learned" rows="2"></textarea>
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
