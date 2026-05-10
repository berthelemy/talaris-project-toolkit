<!doctype html>
<?php $locale = (string) service('request')->getLocale(); ?>
<?php $displayUsername = (string) ($username ?? session('username') ?? ''); ?>
<?php $canImpersonateUsers = (bool) ($canImpersonate ?? false); ?>
<?php $isImpersonatingSession = (bool) ($isImpersonating ?? false); ?>
<?php $impersonationUsers = (array) ($impersonationCandidates ?? []); ?>
<?php $impersonatorName = (string) ($impersonatorUsername ?? ''); ?>
<html lang="<?= esc($locale) ?>">
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
        <div class="d-flex gap-2">
            <a href="<?= site_url('profile') ?>" class="btn btn-outline-primary btn-sm"><?= esc(lang('Auth.profileButton')) ?></a>
            <form method="post" action="<?= site_url('logout') ?>">
                <?= csrf_field() ?>
                <button class="btn btn-outline-secondary btn-sm" type="submit"><?= esc(lang('Auth.logoutButton')) ?></button>
            </form>
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

    <?php if ($isImpersonatingSession): ?>
        <div class="alert alert-warning d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3" role="alert">
            <span><?= esc(lang('Auth.impersonationBanner', ['username' => $impersonatorName])) ?></span>
            <form method="post" action="<?= site_url('impersonate/stop') ?>">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-dark btn-sm"><?= esc(lang('Auth.impersonationStopButton')) ?></button>
            </form>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <p class="mb-0"><?= esc(lang('Auth.dashboardSubtitle', ['username' => $displayUsername])) ?></p>
        </div>
    </div>

    <?php if ($canImpersonateUsers): ?>
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-body">
                <h2 class="h6 mb-3"><?= esc(lang('Auth.impersonationTitle')) ?></h2>
                <form method="post" action="<?= site_url('impersonate/0') ?>" id="impersonation-form" class="row g-2 align-items-end">
                    <?= csrf_field() ?>
                    <div class="col-12 col-md-8">
                        <label for="impersonation_user" class="form-label"><?= esc(lang('Auth.impersonationSelectLabel')) ?></label>
                        <select id="impersonation_user" name="impersonation_user" class="form-select" required>
                            <option value=""><?= esc(lang('Auth.impersonationSelectLabel')) ?></option>
                            <?php foreach ($impersonationUsers as $user): ?>
                                <option value="<?= esc((string) $user['id']) ?>"><?= esc((string) $user['username']) ?> (<?= esc((string) $user['email']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <button class="btn btn-primary w-100" type="submit"><?= esc(lang('Auth.impersonationStartButton')) ?></button>
                    </div>
                </form>
            </div>
        </div>
        <script>
            (function () {
                var form = document.getElementById('impersonation-form');
                var select = document.getElementById('impersonation_user');
                if (!form || !select) {
                    return;
                }

                form.addEventListener('submit', function (event) {
                    var target = String(select.value || '').trim();
                    if (target === '') {
                        event.preventDefault();
                        return;
                    }

                    form.action = '<?= site_url('impersonate') ?>/' + encodeURIComponent(target);
                });
            })();
        </script>
    <?php endif; ?>
</main>
</body>
</html>
