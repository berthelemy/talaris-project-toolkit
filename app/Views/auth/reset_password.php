<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
    <h1 class="h4 mb-4"><?= esc(lang('Auth.titleResetPassword')) ?></h1>

    <form method="post" action="<?= site_url('reset-password/' . $token) ?>" novalidate>
        <?= csrf_field() ?>
        <div class="mb-3">
            <label for="password" class="form-label"><?= esc(lang('Auth.password')) ?></label>
            <input
                id="password"
                name="password"
                type="password"
                class="form-control"
                autocomplete="new-password"
                required
            >
        </div>
        <div class="mb-3">
            <label for="password_confirm" class="form-label"><?= esc(lang('Auth.passwordConfirm')) ?></label>
            <input
                id="password_confirm"
                name="password_confirm"
                type="password"
                class="form-control"
                autocomplete="new-password"
                required
            >
        </div>
        <button type="submit" class="btn btn-primary w-100"><?= esc(lang('Auth.resetPasswordButton')) ?></button>
    </form>
<?= $this->endSection() ?>
