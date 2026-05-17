<?php

/**
 * View template for modules: index.
 */
$pageTitle = (string) lang('Module.managementTitle');
$active = 'modules';
?>
<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
    <?php $activeLocksList = (array) ($activeLocks ?? []); ?>
    <?php if (session('error') !== null): ?>
        <div class="alert alert-danger" role="alert"><?= esc((string) session('error')) ?></div>
    <?php endif; ?>
    <?php if (session('success') !== null): ?>
        <div class="alert alert-success" role="alert"><?= esc((string) session('success')) ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="p-3 border-bottom">
                <h2 class="h6 mb-0"><?= esc(lang('Module.managementSubtitle')) ?></h2>
            </div>
            <?php if (empty($modules)): ?>
                <p class="text-muted p-4 mb-0"><?= esc(lang('Module.noneRegistered')) ?></p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 js-datatable">
                        <thead class="table-light">
                            <tr>
                                <th><?= esc(lang('Module.columnName')) ?></th>
                                <th><?= esc(lang('Module.columnSlug')) ?></th>
                                <th><?= esc(lang('Module.columnScope')) ?></th>
                                <th><?= esc(lang('Module.columnVersion')) ?></th>
                                <th><?= esc(lang('Module.columnStatus')) ?></th>
                                <th class="text-end"><?= esc(lang('Module.columnActions')) ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($modules as $module): ?>
                                <?php $enabled = (bool) ($module['is_enabled'] ?? false); ?>
                                <?php $moduleSlug = (string) ($module['slug'] ?? ''); ?>
                                <?php $defaultPref = (array) (($defaultLayoutPreferences[$moduleSlug] ?? []) ?: []); ?>
                                <?php $defaultVisible = array_key_exists('is_visible', $defaultPref) ? (bool) $defaultPref['is_visible'] : true; ?>
                                <?php $defaultOrder = $defaultPref['display_order'] ?? $module['display_order'] ?? 0; ?>
                                <?php $widgetConfig = json_decode((string) ($module['widget_config_json'] ?? ''), true); ?>
                                <?php if (! is_array($widgetConfig)) {
                                    $widgetConfig = [];
                                } ?>
                                <?php $maxEntries = (int) ($widgetConfig['max_entries'] ?? 5); ?>
                                <?php $failureCount = (int) (($recentFailures[$moduleSlug] ?? 0)); ?>
                                <tr>
                                    <td>
                                        <div><?= esc((string) ($module['name'] ?? '')) ?></div>
                                        <div class="text-muted small"><?= esc((string) ($module['description'] ?? '')) ?></div>
                                        <?php if ($failureCount > 0): ?>
                                            <div class="small text-danger mt-1"><?= esc(lang('Module.failureSignal', [$failureCount])) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><code><?= esc($moduleSlug) ?></code></td>
                                    <td><?= esc(lang('Module.scope.' . (string) ($module['scope_type'] ?? 'unknown'))) ?></td>
                                    <td><?= esc((string) ($module['version'] ?? 'n/a')) ?></td>
                                    <td>
                                        <?php if ($enabled): ?>
                                            <span class="badge text-bg-success"><?= esc(lang('Module.statusEnabled')) ?></span>
                                        <?php else: ?>
                                            <span class="badge text-bg-secondary"><?= esc(lang('Module.statusDisabled')) ?></span>
                                        <?php endif; ?>
                                        <?php if (! empty($module['dependencies_json'])): ?>
                                            <div class="text-muted small mt-1">
                                                <?= esc(lang('Module.dependenciesLabel')) ?>:
                                                <?= esc((string) $module['dependencies_json']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <form method="post" action="<?= site_url('modules/' . rawurlencode($moduleSlug) . '/toggle') ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="is_enabled" value="<?= $enabled ? '0' : '1' ?>">
                                            <button class="btn btn-sm <?= $enabled ? 'btn-outline-secondary' : 'btn-primary' ?>" type="submit">
                                                <?= esc($enabled ? lang('Module.disableButton') : lang('Module.enableButton')) ?>
                                            </button>
                                        </form>
                                        <form method="post" action="<?= site_url('modules/' . rawurlencode($moduleSlug) . '/ordering') ?>" class="d-inline-flex align-items-center gap-1 ms-1">
                                            <?= csrf_field() ?>
                                            <input class="form-control form-control-sm app-input-width-72" name="display_order" type="number" min="0" value="<?= esc((string) ((int) ($module['display_order'] ?? 0))) ?>">
                                            <button class="btn btn-sm btn-outline-primary" type="submit"><?= esc(lang('Module.updateOrderButton')) ?></button>
                                        </form>
                                        <form method="post" action="<?= site_url('modules/' . rawurlencode($moduleSlug) . '/widget-config') ?>" class="d-inline-flex align-items-center gap-1 ms-1">
                                            <?= csrf_field() ?>
                                            <input class="form-control form-control-sm app-input-width-72" name="max_entries" type="number" min="1" max="25" value="<?= esc((string) $maxEntries) ?>">
                                            <button class="btn btn-sm btn-outline-secondary" type="submit"><?= esc(lang('Module.updateConfigButton')) ?></button>
                                        </form>
                                        <form method="post" action="<?= site_url('modules/' . rawurlencode($moduleSlug) . '/widget-layout-default') ?>" class="d-inline-flex align-items-center gap-1 ms-1">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="is_visible" value="0">
                                            <input class="form-check-input" name="is_visible" value="1" type="checkbox" aria-label="<?= esc(lang('Module.defaultVisibleLabel')) ?>" <?= $defaultVisible ? 'checked' : '' ?>>
                                            <input class="form-control form-control-sm app-input-width-72" name="display_order" type="number" min="0" value="<?= esc((string) ((int) $defaultOrder)) ?>" aria-label="<?= esc(lang('Module.defaultOrderLabel')) ?>">
                                            <button class="btn btn-sm btn-outline-dark" type="submit"><?= esc(lang('Module.updateDefaultLayoutButton')) ?></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body p-0">
            <div class="p-3 border-bottom">
                <h2 class="h6 mb-1"><?= esc(lang('Module.activeLocksTitle')) ?></h2>
                <p class="text-muted small mb-0"><?= esc(lang('Module.activeLocksSubtitle')) ?></p>
            </div>
            <?php if (empty($activeLocksList)): ?>
                <p class="text-muted p-4 mb-0"><?= esc(lang('Module.activeLocksNone')) ?></p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 js-datatable">
                        <thead class="table-light">
                            <tr>
                                <th><?= esc(lang('Module.lockColumnModule')) ?></th>
                                <th><?= esc(lang('Module.lockColumnScope')) ?></th>
                                <th><?= esc(lang('Module.lockColumnOwner')) ?></th>
                                <th><?= esc(lang('Module.lockColumnAcquiredAt')) ?></th>
                                <th><?= esc(lang('Module.lockColumnExpiresAt')) ?></th>
                                <th class="text-end"><?= esc(lang('Module.columnActions')) ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activeLocksList as $lock): ?>
                                <tr>
                                    <td><code><?= esc((string) ($lock['module_slug'] ?? '')) ?></code></td>
                                    <td>
                                        <?= esc(lang('Module.scope.' . (string) ($lock['scope_type'] ?? 'unknown'))) ?>
                                        #<?= esc((string) ((int) ($lock['scope_id'] ?? 0))) ?>
                                    </td>
                                    <td>
                                        <?= esc((string) (($lock['locked_by_username'] ?? '') !== '' ? $lock['locked_by_username'] : ('#' . (int) ($lock['locked_by_user_id'] ?? 0)))) ?>
                                    </td>
                                    <td><?= esc((string) ($lock['acquired_at'] ?? '')) ?></td>
                                    <td><?= esc((string) ($lock['expires_at'] ?? '')) ?></td>
                                    <td class="text-end">
                                        <form method="post" action="<?= site_url('modules/locks/' . (int) ($lock['id'] ?? 0) . '/release') ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-sm btn-outline-danger" type="submit"><?= esc(lang('Module.lockReleaseButton')) ?></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?= $this->endSection() ?>

<?= $this->section('postMain') ?>
<?= view('layouts/datatable_assets') ?>
<?= $this->endSection() ?>
