<?php
/**
 * @var array<int, array{id: int, title: string, created_at: string, date_reported: ?string, reporter_username: ?string, owner_username: string, status: string}> $entries
 * @var int $entry_count
 * @var int $scope_id
 */
?>
<div class="card mb-4">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><?= lang('Module.issueTrackerTitle') ?></h5>
        <div class="btn-group btn-group-sm" role="group">
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#issueModalAdd"><?= lang('Module.addNew') ?></button>
            <a class="btn btn-outline-primary" href="<?= site_url('projects/' . $scope_id . '/modules/issue-tracker') ?>"><?= lang('Module.openModule') ?></a>
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
                        <th><?= lang('Module.raidColumnReported') ?></th>
                        <th><?= lang('Module.raidColumnReporter') ?></th>
                        <th><?= lang('Module.raidColumnOwner') ?></th>
                        <th><?= lang('Module.raidColumnStatus') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entries as $entry): ?>
                        <tr>
                            <td>
                                <a href="<?= site_url('projects/' . $scope_id . '/modules/issue-tracker') ?>#entry-<?= (int) ($entry['id'] ?? 0) ?>">
                                    <?= esc((string) ($entry['title'] ?? '')) ?>
                                </a>
                            </td>
                            <td><?= esc((string) ($entry['date_reported'] ?? '')) ?></td>
                            <td><?= esc((string) ($entry['reporter_username'] ?? '')) ?></td>
                            <td><?= esc((string) ($entry['owner_username'] ?? '')) ?></td>
                            <td><?= esc((string) ($entry['status'] ?? '')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if ($entry_count >= 5): ?>
                <a class="btn btn-sm btn-outline-primary mt-3" href="<?= site_url('projects/' . $scope_id . '/modules/issue-tracker') ?>"><?= lang('Module.viewAll') ?></a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal for adding new Issue entry -->
<div class="modal fade" id="issueModalAdd" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= lang('Module.addNewIssue') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="<?= site_url('projects/' . $scope_id . '/modules/issue-tracker') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="issue-title"><?= lang('Module.raidColumnTitle') ?></label>
                        <input id="issue-title" class="form-control" name="title" type="text" maxlength="200" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="issue-date-reported"><?= lang('Module.raidColumnReported') ?></label>
                        <input id="issue-date-reported" class="form-control" name="date_reported" type="date">
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
    const card = document.querySelector('#issueModalAdd').closest('.card');
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
