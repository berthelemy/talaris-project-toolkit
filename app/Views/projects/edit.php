<!doctype html>
<?php $locale = (string) service('request')->getLocale(); ?>
<html lang="<?= esc($locale) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc(lang('Domain.projectEditTitle')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body class="bg-light">
<header class="border-bottom bg-white">
    <div class="container py-3 d-flex justify-content-between align-items-center gap-3">
        <h1 class="h5 mb-0"><?= esc(lang('Domain.projectEditTitle')) ?></h1>
        <a href="<?= site_url('projects') ?>" class="btn btn-outline-secondary btn-sm">&larr; <?= esc(lang('Domain.projectsTitle')) ?></a>
    </div>
</header>
<main class="container py-4">
    <?php if (session('errors') !== null): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0 ps-3">
                <?php foreach ((array) session('errors') as $err): ?>
                    <li><?= esc((string) $err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="post" action="<?= site_url('projects/' . esc((string) $project['id'])) ?>" novalidate>
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="name" class="form-label"><?= esc(lang('Domain.projectName')) ?> <span class="text-danger">*</span></label>
                            <input id="name" name="name" type="text" class="form-control" required maxlength="150"
                                   value="<?= esc((string) old('name', (string) ($project['name'] ?? ''))) ?>">
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label"><?= esc(lang('Domain.projectDescription')) ?></label>
                            <textarea id="description" name="description" class="form-control" rows="5" maxlength="5000"><?= esc((string) old('description', (string) ($project['description'] ?? ''))) ?></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><?= esc(lang('Domain.projectSaveButton')) ?></button>
                            <a href="<?= site_url('projects') ?>" class="btn btn-outline-secondary"><?= esc(lang('Auth.backToLoginLink')) ?></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm border-danger">
                <div class="card-body">
                    <h2 class="h6 text-danger mb-3"><?= esc(lang('Domain.projectDeleteButton')) ?></h2>
                    <p class="text-muted small"><?= esc(lang('Domain.projectDeleteConfirm')) ?></p>
                    <form method="post" action="<?= site_url('projects/' . esc((string) $project['id']) . '/delete') ?>"
                          onsubmit="return confirm(<?= json_encode(lang('Domain.projectDeleteConfirm')) ?>)">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-danger btn-sm"><?= esc(lang('Domain.projectDeleteButton')) ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
</body>
</html>
