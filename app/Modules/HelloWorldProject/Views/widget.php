<?php
/**
 * @var array<int, array{id: int, module_slug: string, scope_type: string, scope_id: int, message: string, created_by_user_id: int, created_at: string, updated_at: string}> $entries
 * @var int $entry_count
 * @var int $scope_id
 */
?>

<div class="card mb-4">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="bi bi-chat-left-text"></i>
            <?= lang('Module.projectHelloWorldTitle') ?>
        </h5>
        <div class="btn-group btn-group-sm" role="group">
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#helloProjectModalAdd-<?= (int) $scope_id ?>">
                <?= lang('Module.addNew') ?>
            </button>
            <a href="<?= base_url('projects/' . $scope_id . '/modules/hello-world') ?>" class="btn btn-outline-primary">
                <?= lang('Module.openModule') ?>
            </a>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($entries)): ?>
            <p class="text-muted mb-0"><?= lang('Module.entriesNone') ?></p>
        <?php else: ?>
            <div class="list-group list-group-sm">
                <?php foreach ($entries as $entry): ?>
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <small class="text-muted">
                                <?= date('M d, Y H:i', strtotime($entry['created_at'])) ?>
                            </small>
                        </div>
                        <p class="mb-1"><?= esc($entry['message']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if ($entry_count >= 5): ?>
                <div class="mt-3">
                    <a href="<?= base_url('projects/' . $scope_id . '/modules/hello-world') ?>" class="btn btn-sm btn-outline-primary">
                        <?= lang('Module.viewAll') ?>
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="helloProjectModalAdd-<?= (int) $scope_id ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= lang('Module.entryCreateButton') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="<?= site_url('projects/' . $scope_id . '/modules/hello-world') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <label class="form-label" for="hello-project-message-<?= $scope_id ?>\"><?= lang('Module.entryLabel') ?></label>
                    <input id="hello-project-message-<?= $scope_id ?>" class="form-control" type="text" name="message" maxlength="500" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= lang('Domain.cancelButton') ?></button>
                    <button class="btn btn-primary" type="submit"><?= lang('Module.entryCreateButton') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
