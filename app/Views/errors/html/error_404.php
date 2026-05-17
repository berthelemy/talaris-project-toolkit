<?php

/**
 * HTML error view template: error 404.
 */
?>
<!DOCTYPE html>
<?php $errorMessage = (string) ($message ?? ''); ?>
<?php $locale = (string) service('request')->getLocale(); ?>
<html lang="<?= esc($locale) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc(lang('Errors.pageNotFound')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <?= view('layouts/theme_assets') ?>
</head>
<body class="bg-light">
<?= view('layouts/app_header', ['pageTitle' => (string) lang('Errors.pageNotFound'), 'active' => '']) ?>
<main class="container py-4">
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center p-4 p-md-5">
            <h1 class="display-4 mb-3">404</h1>
            <p class="mb-0">
                <?php if (ENVIRONMENT !== 'production') : ?>
                    <?= nl2br(esc($errorMessage)) ?>
                <?php else : ?>
                    <?= esc(lang('Errors.sorryCannotFind')) ?>
                <?php endif; ?>
            </p>
        </div>
    </div>
</main>
<?= view('layouts/app_footer') ?>
</body>
</html>
