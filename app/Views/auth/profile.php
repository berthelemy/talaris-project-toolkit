<!doctype html>
<?php $locale = (string) service('request')->getLocale(); ?>
<html lang="<?= esc($locale) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc(lang('Auth.profileTitle')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body class="bg-light">
<header class="border-bottom bg-white">
    <div class="container py-3 d-flex justify-content-between align-items-center gap-3">
        <h1 class="h5 mb-0"><?= esc(lang('Auth.profileTitle')) ?></h1>
        <div class="d-flex gap-2">
            <a href="<?= site_url('dashboard') ?>" class="btn btn-outline-primary btn-sm"><?= esc(lang('Auth.dashboardTitle')) ?></a>
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
    <?php if (session('errors') !== null): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0 ps-3">
                <?php foreach ((array) session('errors') as $error): ?>
                    <li><?= esc((string) $error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted mb-4"><?= esc(lang('Auth.profileSubtitle')) ?></p>
                    <form method="post" action="<?= site_url('profile') ?>" novalidate>
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="language_preference" class="form-label"><?= esc(lang('Auth.profileLanguage')) ?></label>
                            <select class="form-select" id="language_preference" name="language_preference">
                                <?php $language = old('language_preference', (string) ($user['language_preference'] ?? '')); ?>
                                <option value="">-</option>
                                <option value="en" <?= $language === 'en' ? 'selected' : '' ?>><?= esc(lang('Auth.languageEnglish')) ?></option>
                                <option value="fr" <?= $language === 'fr' ? 'selected' : '' ?>><?= esc(lang('Auth.languageFrench')) ?></option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="profile_description" class="form-label"><?= esc(lang('Auth.profileDescription')) ?></label>
                            <textarea class="form-control" id="profile_description" name="profile_description" rows="5" maxlength="1000"><?= esc((string) old('profile_description', (string) ($user['profile_description'] ?? ''))) ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="avatar_path" class="form-label"><?= esc(lang('Auth.profileAvatarPath')) ?></label>
                            <input
                                id="avatar_path"
                                name="avatar_path"
                                type="text"
                                class="form-control"
                                maxlength="255"
                                value="<?= esc((string) old('avatar_path', (string) ($user['avatar_path'] ?? ''))) ?>"
                            >
                        </div>
                        <button class="btn btn-primary" type="submit"><?= esc(lang('Auth.profileSaveButton')) ?></button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h6 mb-3"><?= esc(lang('Auth.profilePasswordSection')) ?></h2>
                    <form method="post" action="<?= site_url('profile/password') ?>" novalidate>
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="current_password" class="form-label"><?= esc(lang('Auth.profileCurrentPassword')) ?></label>
                            <input id="current_password" name="current_password" type="password" class="form-control" autocomplete="current-password" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label"><?= esc(lang('Auth.profileNewPassword')) ?></label>
                            <input id="new_password" name="new_password" type="password" class="form-control" autocomplete="new-password" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password_confirm" class="form-label"><?= esc(lang('Auth.profileNewPasswordConfirm')) ?></label>
                            <input id="new_password_confirm" name="new_password_confirm" type="password" class="form-control" autocomplete="new-password" required>
                        </div>
                        <button class="btn btn-outline-primary" type="submit"><?= esc(lang('Auth.profileChangePasswordButton')) ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
</body>
</html>