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

<?php include __DIR__ . '/_add_modal.php'; ?>

