<!doctype html>
<?php $locale = (string) service('request')->getLocale(); ?>
<html lang="<?= esc($locale) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc(lang('Module.managementTitle')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <?= view('layouts/theme_assets') ?>
</head>
<body class="bg-light">
<?= view('layouts/app_header', ['pageTitle' => lang('Module.managementTitle'), 'active' => 'modules']) ?>
<main class="container py-4">
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
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><?= esc(lang('Module.columnName')) ?></th>
                                <th><?= esc(lang('Module.columnSlug')) ?></th>
                                <th><?= esc(lang('Module.columnScope')) ?></th>
                                <th><?= esc(lang('Module.columnStatus')) ?></th>
                                <th class="text-end"><?= esc(lang('Module.columnActions')) ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($modules as $module): ?>
                                <?php $enabled = (bool) ($module['is_enabled'] ?? false); ?>
                                <tr>
                                    <td>
                                        <div><?= esc((string) ($module['name'] ?? '')) ?></div>
                                        <div class="text-muted small"><?= esc((string) ($module['description'] ?? '')) ?></div>
                                    </td>
                                    <td><code><?= esc((string) ($module['slug'] ?? '')) ?></code></td>
                                    <td><?= esc(lang('Module.scope.' . (string) ($module['scope_type'] ?? 'unknown'))) ?></td>
                                    <td>
                                        <?php if ($enabled): ?>
                                            <span class="badge text-bg-success"><?= esc(lang('Module.statusEnabled')) ?></span>
                                        <?php else: ?>
                                            <span class="badge text-bg-secondary"><?= esc(lang('Module.statusDisabled')) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <form method="post" action="<?= site_url('modules/' . rawurlencode((string) ($module['slug'] ?? '')) . '/toggle') ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="is_enabled" value="<?= $enabled ? '0' : '1' ?>">
                                            <button class="btn btn-sm <?= $enabled ? 'btn-outline-secondary' : 'btn-primary' ?>" type="submit">
                                                <?= esc($enabled ? lang('Module.disableButton') : lang('Module.enableButton')) ?>
                                            </button>
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
</main>
</body>
</html>
