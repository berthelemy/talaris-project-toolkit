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
            <?= lang('Module.programmeHelloWorldTitle') ?>
        </h5>
        <a href="<?= base_url('programmes/' . $scope_id . '/modules/hello-world') ?>" class="btn btn-sm btn-outline-primary">
            <?= lang('Module.openModule') ?>
        </a>
    </div>
    <div class="card-body">
        <form method="post" action="<?= site_url('programmes/' . $scope_id . '/modules/hello-world') ?>" class="row g-2 mb-3">
            <?= csrf_field() ?>
            <div class="col-12">
                <label class="form-label" for="hello-programme-message-<?= $scope_id ?>"><?= lang('Module.entryLabel') ?></label>
                <input id="hello-programme-message-<?= $scope_id ?>" class="form-control form-control-sm" type="text" name="message" maxlength="500" required>
            </div>
            <div class="col-12 d-grid">
                <button class="btn btn-sm btn-primary" type="submit"><?= lang('Module.entryCreateButton') ?></button>
            </div>
        </form>

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
                    <a href="<?= base_url('programmes/' . $scope_id . '/modules/hello-world') ?>" class="btn btn-sm btn-outline-primary">
                        <?= lang('Module.viewAll') ?>
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
