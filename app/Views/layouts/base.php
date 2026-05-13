<?php
/** @var string|null $pageTitle */
/** @var string|null $active */
/** @var string|null $bodyClass */
/** @var string|null $mainClass */
$pageTitle = (string) ($pageTitle ?? '');
$active = (string) ($active ?? '');
$bodyClass = (string) ($bodyClass ?? 'bg-light');
$mainClass = (string) ($mainClass ?? 'container py-4');
$locale = (string) service('request')->getLocale();
?>
<!doctype html>
<html lang="<?= esc($locale) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <?= view('layouts/theme_assets') ?>
    <?= $this->renderSection('extraHead') ?>
</head>
<body class="<?= esc($bodyClass) ?>">
<?= view('layouts/app_header', ['pageTitle' => $pageTitle, 'active' => $active]) ?>
<main class="<?= esc($mainClass) ?>">
    <?= $this->renderSection('content') ?>
</main>
<?= $this->renderSection('postMain') ?>
<?= view('layouts/app_footer') ?>
<?= $this->renderSection('extraScripts') ?>
</body>
</html>
