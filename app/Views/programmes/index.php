<!doctype html>
<?php $locale = (string) service('request')->getLocale(); ?>
<html lang="<?= esc($locale) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc(lang('Domain.programmesTitle')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body class="bg-light">
<header class="border-bottom bg-white">
    <div class="container py-3 d-flex justify-content-between align-items-center gap-3">
        <h1 class="h5 mb-0"><?= esc(lang('Domain.programmesTitle')) ?></h1>
        <div class="d-flex gap-2">
            <a href="<?= site_url('dashboard') ?>" class="btn btn-outline-secondary btn-sm"><?= esc(lang('Auth.dashboardTitle')) ?></a>
            <a href="<?= site_url('projects') ?>" class="btn btn-outline-secondary btn-sm"><?= esc(lang('Domain.projectsTitle')) ?></a>
        </div>
    </div>
</header>
<main class="container py-4">
    <?php if (session('error') !== null): ?>
        <div class="alert alert-danger" role="alert"><?= esc((string) session('error')) ?></div>
    <?php endif; ?>
    <?php if (session('success') !== null): ?>
        <div class="alert alert-success" role="alert"><?= esc((string) session('success')) ?></div>
    <?php endif; ?>
    <?php if (session('errors') !== null): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0 ps-3">
                <?php foreach ((array) session('errors') as $err): ?>
                    <li><?= esc((string) $err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ((bool) ($canCreate ?? false)): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h2 class="h6 mb-3"><?= esc(lang('Domain.programmeCreateButton')) ?></h2>
            <form method="post" action="<?= site_url('programmes') ?>" novalidate>
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <input type="text" name="name" class="form-control" placeholder="<?= esc(lang('Domain.programmeName')) ?>" required maxlength="150" value="<?= esc((string) old('name', '')) ?>">
                    </div>
                    <div class="col-12 col-md-6">
                        <input type="text" name="description" class="form-control" placeholder="<?= esc(lang('Domain.programmeDescription')) ?>" maxlength="5000" value="<?= esc((string) old('description', '')) ?>">
                    </div>
                    <div class="col-12 col-md-2">
                        <button type="submit" class="btn btn-primary w-100"><?= esc(lang('Domain.programmeCreateButton')) ?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($programmes)): ?>
                <p class="text-muted p-4 mb-0"><?= esc(lang('Domain.programmesNone')) ?></p>
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
                            <?php foreach ($programmes as $programme): ?>
                                <tr>
                                    <td><?= esc((string) $programme['name']) ?></td>
                                    <td class="d-none d-md-table-cell text-muted"><?= esc((string) ($programme['description'] ?? '')) ?></td>
                                    <td class="d-none d-sm-table-cell text-muted"><?= esc((string) ($programme['created_at'] ?? '')) ?></td>
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
