<?php
$pageTitle = (string) lang('Module.programmeLayoutPageTitle');
$active = 'programmes';
?>
<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
    <div class="mb-3">
        <a class="btn btn-outline-secondary btn-sm" href="<?= site_url('programmes/' . (int) ($programme['id'] ?? 0)) ?>"><?= esc(lang('Module.backToProgramme')) ?></a>
    </div>

    <?php if (session('error') !== null): ?>
        <div class="alert alert-danger" role="alert"><?= esc((string) session('error')) ?></div>
    <?php endif; ?>
    <?php if (session('success') !== null): ?>
        <div class="alert alert-success" role="alert"><?= esc((string) session('success')) ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h2 class="h5 mb-1"><?= esc(lang('Module.programmeLayoutTitle')) ?></h2>
            <p class="text-muted small mb-4"><?= esc(lang('Module.programmeLayoutDescription')) ?></p>
            <p class="text-muted small mb-3" id="widget-layout-order-help"><?= esc(lang('Module.projectLayoutOrderHelp')) ?></p>

            <form method="post" action="<?= site_url('programmes/' . (int) ($programme['id'] ?? 0) . '/widgets/layout') ?>">
                <?= csrf_field() ?>
                <div class="table-responsive">
                    <table class="table table-sm align-middle" data-widget-order-list aria-describedby="widget-layout-order-help">
                        <thead>
                            <tr>
                                <th class="text-nowrap" style="width: 124px;"><?= esc(lang('Module.projectLayoutOrder')) ?></th>
                                <th><?= esc(lang('Module.columnName')) ?></th>
                                <th><?= esc(lang('Module.projectLayoutVisible')) ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ((array) ($widgetLayoutOptions ?? []) as $option): ?>
                                <?php $optionSlug = (string) ($option['slug'] ?? ''); ?>
                                <tr data-widget-row draggable="true">
                                    <td class="text-nowrap">
                                        <span class="badge text-bg-light me-2" data-widget-position><?= esc((string) ((int) ($option['display_order'] ?? 0))) ?></span>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-widget-move="up" aria-label="<?= esc(lang('Module.projectLayoutMoveUp')) ?>">&#8593;</button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-widget-move="down" aria-label="<?= esc(lang('Module.projectLayoutMoveDown')) ?>">&#8595;</button>
                                    </td>
                                    <td>
                                        <span class="me-2" aria-hidden="true" title="<?= esc(lang('Module.projectLayoutDragHandle')) ?>">&#8801;</span>
                                        <?= esc((string) ($option['name'] ?? $optionSlug)) ?>
                                    </td>
                                    <td>
                                        <input type="hidden" name="widget_visible_hidden[<?= esc($optionSlug) ?>]" value="0">
                                        <input class="form-check-input" type="checkbox" name="widget_visible[<?= esc($optionSlug) ?>]" value="1" <?= (bool) ($option['is_visible'] ?? false) ? 'checked' : '' ?>>
                                    </td>
                                    <input type="hidden" data-widget-order-input name="widget_order[<?= esc($optionSlug) ?>]" value="<?= esc((string) ((int) ($option['display_order'] ?? 0))) ?>">
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <button type="submit" class="btn btn-outline-primary btn-sm"><?= esc(lang('Module.projectLayoutSaveButton')) ?></button>
            </form>
        </div>
    </div>
<?= $this->endSection() ?>

<?= $this->section('extraScripts') ?>
    <script src="<?= base_url('js/widget-layout-ordering.js') ?>"></script>
<?= $this->endSection() ?>
