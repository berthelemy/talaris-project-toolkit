<?php
/**
 * @var array<int, array{id: int, title: string, impact_level: ?string, owner_username: string}> $entries
 * @var int $entry_count
 * @var int $scope_id
 */
?>
<div class="card h-100">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><?= esc(lang('Module.assumptionsWidgetHighPriorityTitle')) ?></h5>
        <div class="btn-group btn-group-sm" role="group">
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#assumptionHighPriorityModalAdd"><?= lang('Module.addNew') ?></button>
            <a class="btn btn-outline-primary" href="<?= site_url('projects/' . $scope_id . '/modules/assumptions-register') ?>"><?= lang('Module.openModule') ?></a>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($entries)): ?>
            <p class="text-muted mb-0"><?= lang('Module.entriesNone') ?></p>
        <?php else: ?>
            <table class="table table-sm table-hover mb-0 js-datatable">
                <thead class="table-light">
                    <tr>
                        <th><?= lang('Module.assumptionsColumnDescription') ?></th>
                        <th><?= lang('Module.raidColumnImpactLevel') ?></th>
                        <th><?= lang('Module.raidColumnOwner') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entries as $entry): ?>
                        <tr>
                            <td>
                                <a href="<?= site_url('projects/' . $scope_id . '/modules/assumptions-register') ?>#entry-<?= (int) ($entry['id'] ?? 0) ?>">
                                    <?= esc((string) ($entry['title'] ?? '')) ?>
                                </a>
                            </td>
                            <td><?= esc((string) lang('Module.impactLevel' . ucfirst((string) ($entry['impact_level'] ?? 'low')))) ?></td>
                            <td><?= esc((string) ($entry['owner_username'] ?? '')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if ($entry_count >= 5): ?>
                <a class="btn btn-sm btn-outline-primary mt-3" href="<?= site_url('projects/' . $scope_id . '/modules/assumptions-register') ?>"><?= lang('Module.viewAll') ?></a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="assumptionHighPriorityModalAdd" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= lang('Module.addNewAssumption') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="<?= site_url('projects/' . $scope_id . '/modules/assumptions-register') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="assumption-high-priority-title"><?= lang('Module.assumptionsColumnDescription') ?></label>
                        <input id="assumption-high-priority-title" class="form-control" name="title" type="text" maxlength="200" required>
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
