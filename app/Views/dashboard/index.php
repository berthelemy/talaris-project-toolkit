<!doctype html>
<html lang="<?= esc(service('request')->getLocale()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc(lang('Auth.dashboardTitle')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body class="bg-light">
<header class="border-bottom bg-white">
    <div class="container py-3 d-flex justify-content-between align-items-center">
        <h1 class="h5 mb-0"><?= esc(lang('Auth.dashboardTitle')) ?></h1>
        <form method="post" action="<?= site_url('logout') ?>">
            <?= csrf_field() ?>
            <button class="btn btn-outline-secondary btn-sm" type="submit"><?= esc(lang('Auth.logoutButton')) ?></button>
        </form>
    </div>
</header>
<main class="container py-4">
    <?php if (session('success') !== null): ?>
        <div class="alert alert-success" role="alert"><?= esc((string) session('success')) ?></div>
    <?php endif; ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <p class="mb-0"><?= esc(lang('Auth.dashboardSubtitle', ['username' => $username])) ?></p>
        </div>
    </div>
</main>
</body>
</html>
