<?php

/**
 * Authentication view template: forgot password.
 */
?>
<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
    <?php $emailConfig = config('Email'); ?>
    <?php $usingMailpit = $emailConfig->protocol === 'smtp' && $emailConfig->SMTPHost === 'mailpit' && $emailConfig->SMTPPort === 1025; ?>

    <h1 class="h4 mb-4"><?= esc(lang('Auth.titleForgotPassword')) ?></h1>

    <div class="alert <?= $usingMailpit ? 'alert-info' : 'alert-secondary' ?>" role="status" aria-live="polite">
        <strong>Email delivery:</strong>
        <?php if ($usingMailpit): ?>
            Reset emails are being sent to Mailpit at <code>mailpit:1025</code>. Open the Mailpit inbox at <code>localhost:8025</code> after the devcontainer rebuild.
        <?php else: ?>
            Reset emails are using SMTP host <?= esc($emailConfig->SMTPHost !== '' ? $emailConfig->SMTPHost : 'not configured') ?> on port <?= esc((string) $emailConfig->SMTPPort) ?>.
        <?php endif; ?>
    </div>

    <?php if (ENVIRONMENT === 'development' && session('dev_reset_url') !== null): ?>
        <div class="alert alert-warning" role="alert">
            <strong>Dev mode:</strong> Email could not be sent. Use this link to reset the password:<br>
            <a href="<?= esc(session('dev_reset_url')) ?>"><?= esc(session('dev_reset_url')) ?></a>
        </div>
    <?php endif ?>

    <form method="post" action="<?= site_url('forgot-password') ?>" novalidate>
        <?= csrf_field() ?>
        <div class="mb-3">
            <label for="email" class="form-label"><?= esc(lang('Auth.email')) ?></label>
            <input
                id="email"
                name="email"
                type="email"
                class="form-control"
                value="<?= esc(old('email')) ?>"
                autocomplete="email"
                required
            >
        </div>
        <button type="submit" class="btn btn-primary w-100"><?= esc(lang('Auth.requestResetButton')) ?></button>
    </form>

    <p class="mt-3 mb-0 text-center">
        <a href="<?= site_url('login') ?>"><?= esc(lang('Auth.backToLoginLink')) ?></a>
    </p>
<?= $this->endSection() ?>
