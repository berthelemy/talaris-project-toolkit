<?php
/**
 * @var list<array<string,mixed>> $entries
 * @var int $entry_count
 * @var int $scope_id
 * @var list<array{id:int,username:string}> $owners
 */
?>
<div class="card mb-4">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Open Meeting Actions</h5>
        <div class="btn-group btn-group-sm" role="group">
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#meetingNotesModalAdd">Add New</button>
            <a class="btn btn-outline-primary" href="<?= site_url('projects/' . $scope_id . '/modules/meeting-notes') ?>">Open Module</a>
        </div>
    </div>
    <div class="card-body">
        <?php if ($entry_count === 0): ?>
            <p class="text-muted mb-0">No open action items.</p>
        <?php else: ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($entries as $entry): ?>
                    <li class="list-group-item px-0">
                        <div class="fw-semibold"><?= esc((string) ($entry['title'] ?? '')) ?></div>
                        <div class="small text-muted">
                            <?= esc((string) ($entry['meeting_title'] ?? '')) ?>
                            <?php if ((string) ($entry['owner_username'] ?? '') !== ''): ?>
                                | Owner: <?= esc((string) ($entry['owner_username'] ?? '')) ?>
                            <?php endif; ?>
                            <?php if ((string) ($entry['due_date'] ?? '') !== ''): ?>
                                | Due: <?= esc((string) ($entry['due_date'] ?? '')) ?>
                            <?php endif; ?>
                        </div>
                        <?php if ((string) ($entry['description'] ?? '') !== ''): ?>
                            <div class="small text-muted mt-1"><?= esc((string) ($entry['description'] ?? '')) ?></div>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/_add_modal.php'; ?>