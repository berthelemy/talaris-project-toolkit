<?php

/**
 * HTML error view template: production.
 */
?>
<!doctype html>
<?php $locale = (string) service('request')->getLocale(); ?>
<html lang="<?= esc($locale) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title><?= esc(lang('Errors.whoops')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <?= view('layouts/theme_assets') ?>
</head>
<body class="bg-light">
<?= view('layouts/app_header', ['pageTitle' => (string) lang('Errors.whoops'), 'active' => '']) ?>
<main class="container py-4">
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center p-4 p-md-5">
            <h1 class="display-6 mb-3"><?= esc(lang('Errors.whoops')) ?></h1>
            <p class="lead mb-0"><?= esc(lang('Errors.weHitASnag')) ?></p>
        </div>
    </div>
</main>
<?= view('layouts/app_footer') ?>
</body>
</html>
