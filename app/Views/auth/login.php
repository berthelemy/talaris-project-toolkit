<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
    <h1 class="h4 mb-4"><?= esc(lang('Auth.titleSignIn')) ?></h1>

    <form method="post" action="<?= site_url('login') ?>" novalidate>
        <?= csrf_field() ?>
        <div class="mb-3">
            <label for="username" class="form-label"><?= esc(lang('Auth.username')) ?></label>
            <input
                id="username"
                name="username"
                type="text"
                class="form-control"
                value="<?= esc(old('username')) ?>"
                autocomplete="username"
                required
            >
        </div>
        <div class="mb-3">
            <label for="password" class="form-label"><?= esc(lang('Auth.password')) ?></label>
            <input
                id="password"
                name="password"
                type="password"
                class="form-control"
                autocomplete="current-password"
                required
            >
        </div>
        <button type="submit" class="btn btn-primary w-100"><?= esc(lang('Auth.loginButton')) ?></button>
    </form>

    <p class="mt-3 mb-0 text-center">
        <a href="<?= site_url('forgot-password') ?>"><?= esc(lang('Auth.forgotPasswordLink')) ?></a>
    </p>
<?= $this->endSection() ?>
