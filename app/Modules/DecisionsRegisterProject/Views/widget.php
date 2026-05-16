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

<?php include __DIR__ . '/_add_modal.php'; ?>

