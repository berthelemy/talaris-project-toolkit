<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
    <h1 class="h4 mb-4"><?= esc(lang('Auth.titleForgotPassword')) ?></h1>

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
