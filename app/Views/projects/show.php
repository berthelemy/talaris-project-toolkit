<!doctype html>
<?php $locale = (string) service('request')->getLocale(); ?>
<html lang="<?= esc($locale) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc(lang('Domain.projectDetailsTitle')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body class="bg-light">
<?= view('layouts/app_header', ['pageTitle' => lang('Domain.projectDetailsTitle'), 'active' => 'projects']) ?>
<main class="container py-4">
    <?php if (session('error') !== null): ?>
        <div class="alert alert-danger" role="alert"><?= esc((string) session('error')) ?></div>
    <?php endif; ?>
    <?php if (session('success') !== null): ?>
        <div class="alert alert-success" role="alert"><?= esc((string) session('success')) ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h2 class="h5 mb-3"><?= esc((string) ($project['name'] ?? '')) ?></h2>
            <div class="mb-2">
                <span class="text-muted"><?= esc(lang('Domain.projectDescription')) ?>:</span>
                <div><?= esc((string) ($project['description'] ?? '')) ?></div>
            </div>
            <div class="text-muted small">
                <?= esc(lang('Domain.projectCreatedAt')) ?>: <?= esc((string) ($project['created_at'] ?? '')) ?>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="p-3 border-bottom">
                <h3 class="h6 mb-0"><?= esc(lang('Domain.linkedProgrammesTitle')) ?></h3>
            </div>
            <?php if (empty($linkedProgrammes)): ?>
                <p class="text-muted p-4 mb-0"><?= esc(lang('Domain.noLinkedProgrammes')) ?></p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><?= esc(lang('Domain.programmeName')) ?></th>
                                <th class="d-none d-md-table-cell"><?= esc(lang('Domain.programmeDescription')) ?></th>
                                <th class="d-none d-sm-table-cell"><?= esc(lang('Domain.programmeCreatedAt')) ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($linkedProgrammes as $linkedProgramme): ?>
                                <tr>
                                    <td><a href="<?= site_url('programmes/' . (int) ($linkedProgramme['id'] ?? 0)) ?>"><?= esc((string) ($linkedProgramme['name'] ?? '')) ?></a></td>
                                    <td class="d-none d-md-table-cell text-muted"><?= esc((string) ($linkedProgramme['description'] ?? '')) ?></td>
                                    <td class="d-none d-sm-table-cell text-muted"><?= esc((string) ($linkedProgramme['created_at'] ?? '')) ?></td>
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
