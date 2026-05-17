<?php

/**
 * View template for install: admin.
 */
?>
<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
    <h1 class="h4 mb-3"><?= esc(lang('Auth.setupTitle')) ?></h1>
    <p class="text-muted mb-4"><?= esc(lang('Auth.setupIntro')) ?></p>

    <?php if (! $canInstall): ?>
        <div class="alert alert-warning" role="alert">
            <?= esc(lang('Auth.setupMigrationsRequired')) ?>
        </div>
    <?php else: ?>
        <form method="post" action="<?= site_url('install/admin') ?>" novalidate>
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="username" class="form-label"><?= esc(lang('Auth.username')) ?></label>
                <input id="username" name="username" type="text" class="form-control" value="<?= esc(old('username')) ?>" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label"><?= esc(lang('Auth.email')) ?></label>
                <input id="email" name="email" type="email" class="form-control" value="<?= esc(old('email')) ?>" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label"><?= esc(lang('Auth.password')) ?></label>
                <input id="password" name="password" type="password" class="form-control" autocomplete="new-password" required>
            </div>

            <div class="mb-3">
                <label for="password_confirm" class="form-label"><?= esc(lang('Auth.passwordConfirm')) ?></label>
                <input id="password_confirm" name="password_confirm" type="password" class="form-control" autocomplete="new-password" required>
            </div>

            <button type="submit" class="btn btn-primary w-100"><?= esc(lang('Auth.setupCreateButton')) ?></button>
        </form>
    <?php endif; ?>
<?= $this->endSection() ?>
