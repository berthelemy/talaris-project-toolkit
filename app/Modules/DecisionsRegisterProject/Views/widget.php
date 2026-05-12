<?php
/**
 * @var array<int, array{id: int, title: string, description: string, decision_date: string, made_by_username: ?string, status: string}> $entries
 * @var int $entry_count
 * @var int $scope_id
 */
?>
<div class="card mb-4">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><?= lang('Module.decisionsRegisterTitle') ?></h5>
        <div class="btn-group btn-group-sm" role="group">
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#decisionModalAdd"><?= lang('Module.addNew') ?></button>
            <a class="btn btn-outline-primary" href="<?= site_url('projects/' . $scope_id . '/modules/decisions-register') ?>"><?= lang('Module.openModule') ?></a>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($entries)): ?>
            <p class="text-muted mb-0"><?= lang('Module.entriesNone') ?></p>
        <?php else: ?>
            <table class="table table-sm table-hover mb-0 js-datatable">
                <thead class="table-light">
                    <tr>
                        <th><?= lang('Module.decisionsDescriptionLabel') ?></th>
                        <th><?= lang('Module.decisionsDateLabel') ?></th>
                        <th><?= lang('Module.raidColumnMadeBy') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entries as $entry): ?>
                        <tr>
                            <td>
                                <a href="<?= site_url('projects/' . $scope_id . '/modules/decisions-register') ?>#entry-<?= (int) ($entry['id'] ?? 0) ?>">
                                    <?= esc((string) (substr($entry['description'] ?? '', 0, 50)) . (strlen((string) ($entry['description'] ?? '')) > 50 ? '...' : '')) ?>
                                </a>
                            </td>
                            <td><?= esc((string) ($entry['decision_date'] ?? '')) ?></td>
                            <td><?= esc((string) ($entry['made_by_username'] ?? '')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if ($entry_count >= 5): ?>
                <a class="btn btn-sm btn-outline-primary mt-3" href="<?= site_url('projects/' . $scope_id . '/modules/decisions-register') ?>"><?= lang('Module.viewAll') ?></a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal for adding new Decision entry -->
<div class="modal fade" id="decisionModalAdd" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= lang('Module.addNewDecision') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="<?= site_url('projects/' . $scope_id . '/modules/decisions-register') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="decision-description"><?= lang('Module.decisionsDescriptionLabel') ?></label>
                        <textarea id="decision-description" class="form-control" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="decision-date"><?= lang('Module.decisionsDateLabel') ?></label>
                        <input id="decision-date" class="form-control" type="date" name="decision_date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= lang('Common.cancel') ?></button>
                    <button type="submit" class="btn btn-primary"><?= lang('Module.raidCreateButton') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const card = document.querySelector('#decisionModalAdd').closest('.card');
    if (card) {
        const table = card.querySelector('.js-datatable');
        if (table && typeof DataTable !== 'undefined') {
            new DataTable(table, {
                paging: false,
                searching: false,
                info: false,
                columnDefs: [{targets: 'no-sort', orderable: false}]
            });
        }
    }
});
</script>
