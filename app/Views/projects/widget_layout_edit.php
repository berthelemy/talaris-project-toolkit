<!doctype html>
<?php $locale = (string) service('request')->getLocale(); ?>
<html lang="<?= esc($locale) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc(lang('Module.projectLayoutPageTitle')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <?= view('layouts/theme_assets') ?>
</head>
<body class="bg-light">
<?= view('layouts/app_header', ['pageTitle' => lang('Module.projectLayoutPageTitle'), 'active' => 'projects']) ?>
<main class="container py-4">
    <?php if (session('error') !== null): ?>
        <div class="alert alert-danger" role="alert"><?= esc((string) session('error')) ?></div>
    <?php endif; ?>
    <?php if (session('success') !== null): ?>
        <div class="alert alert-success" role="alert"><?= esc((string) session('success')) ?></div>
    <?php endif; ?>

    <div class="row g-3">
        <aside class="col-12 col-lg-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h6 mb-3"><?= esc((string) ($project['name'] ?? '')) ?></h2>
                    <nav aria-label="<?= esc(lang('Domain.projectModulesLabel')) ?>">
                        <ul class="nav nav-pills flex-column gap-1">
                            <li class="nav-item"><a class="nav-link" href="<?= site_url('projects/' . (int) ($project['id'] ?? 0)) ?>"><?= esc(lang('Domain.overviewLabel')) ?></a></li>
                            <li class="nav-item"><a class="nav-link active" href="<?= site_url('projects/' . (int) ($project['id'] ?? 0) . '/widgets/layout') ?>"><?= esc(lang('Module.projectLayoutManageWidgets')) ?></a></li>
                            <?php foreach ((array) ($projectModules ?? []) as $module): ?>
                                <li class="nav-item"><a class="nav-link" href="<?= esc((string) ($module['url'] ?? '#')) ?>"><?= esc((string) ($module['name'] ?? '')) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </aside>

        <section class="col-12 col-lg-10">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h5 mb-1"><?= esc(lang('Module.projectLayoutTitle')) ?></h2>
                    <p class="text-muted small mb-4"><?= esc(lang('Module.projectLayoutDescription')) ?></p>

                    <form method="post" action="<?= site_url('projects/' . (int) ($project['id'] ?? 0) . '/widgets/layout') ?>">
                        <?= csrf_field() ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th><?= esc(lang('Module.columnName')) ?></th>
                                        <th><?= esc(lang('Module.projectLayoutVisible')) ?></th>
                                        <th><?= esc(lang('Module.projectLayoutOrder')) ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ((array) ($widgetLayoutOptions ?? []) as $option): ?>
                                        <?php $optionSlug = (string) ($option['slug'] ?? ''); ?>
                                        <tr>
                                            <td><?= esc((string) ($option['name'] ?? $optionSlug)) ?></td>
                                            <td>
                                                <input type="hidden" name="widget_visible_hidden[<?= esc($optionSlug) ?>]" value="0">
                                                <input class="form-check-input" type="checkbox" name="widget_visible[<?= esc($optionSlug) ?>]" value="1" <?= (bool) ($option['is_visible'] ?? false) ? 'checked' : '' ?>>
                                            </td>
                                            <td>
                                                <input class="form-control form-control-sm" style="max-width: 96px;" type="number" min="0" name="widget_order[<?= esc($optionSlug) ?>]" value="<?= esc((string) ((int) ($option['display_order'] ?? 0))) ?>">
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <button type="submit" class="btn btn-outline-primary btn-sm"><?= esc(lang('Module.projectLayoutSaveButton')) ?></button>
                    </form>
                </div>
            </div>
        </section>
    </div>
</main>
<?= view('layouts/app_footer') ?>
</body>
</html>
