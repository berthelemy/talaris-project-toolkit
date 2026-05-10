<!doctype html>
<?php $locale = (string) service('request')->getLocale(); ?>
<html lang="<?= esc($locale) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc(lang('Module.issueTrackerTitle')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <?= view('layouts/theme_assets') ?>
</head>
<body class="bg-light">
<?= view('layouts/app_header', ['pageTitle' => lang('Module.issueTrackerTitle'), 'active' => 'projects']) ?>
<main class="container py-4">
    <?php if (session('error') !== null): ?>
        <div class="alert alert-danger" role="alert"><?= esc((string) session('error')) ?></div>
    <?php endif; ?>
    <?php if (session('success') !== null): ?>
        <div class="alert alert-success" role="alert"><?= esc((string) session('success')) ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h2 class="h5 mb-2"><?= esc((string) ($project['name'] ?? '')) ?></h2>
            <p class="mb-0 text-muted"><?= esc(lang('Module.issueTrackerDescription')) ?></p>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="post" action="<?= site_url('projects/' . (int) ($project['id'] ?? 0) . '/modules/issue-tracker') ?>" class="row g-2 align-items-end">
                <?= csrf_field() ?>
                <div class="col-12 col-md-9">
                    <label for="message" class="form-label"><?= esc(lang('Module.entryLabel')) ?></label>
                    <input id="message" name="message" type="text" maxlength="500" class="form-control" required>
                </div>
                <div class="col-12 col-md-3">
                    <button class="btn btn-primary w-100" type="submit"><?= esc(lang('Module.entryCreateButton')) ?></button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="p-3 border-bottom">
                <h3 class="h6 mb-0"><?= esc(lang('Module.entriesTitle')) ?></h3>
            </div>
            <?php if (empty($entries)): ?>
                <p class="text-muted p-4 mb-0"><?= esc(lang('Module.entriesNone')) ?></p>
            <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($entries as $entry): ?>
                        <li class="list-group-item">
                            <div><?= esc((string) ($entry['message'] ?? '')) ?></div>
                            <div class="text-muted small"><?= esc((string) ($entry['created_at'] ?? '')) ?></div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</main>
</body>
</html>
