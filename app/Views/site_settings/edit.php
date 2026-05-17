<?php

/**
 * View template for site_settings: edit.
 */
$pageTitle = (string) lang('SiteSettings.pageTitle');
$active = 'site_settings';
$settings = (array) ($settings ?? []);
?>
<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
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

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <p class="text-muted mb-4"><?= esc(lang('SiteSettings.subtitle')) ?></p>

            <form method="post" action="<?= site_url('site-settings') ?>" novalidate>
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="site_title" class="form-label"><?= esc(lang('SiteSettings.siteTitleLabel')) ?></label>
                    <input
                        id="site_title"
                        name="site_title"
                        type="text"
                        class="form-control"
                        maxlength="120"
                        required
                        value="<?= esc((string) old('site_title', (string) ($settings['site_title'] ?? 'Talaris Project Toolkit'))) ?>"
                    >
                    <div class="form-text"><?= esc(lang('SiteSettings.siteTitleHint')) ?></div>
                </div>

                <button class="btn btn-primary" type="submit"><?= esc(lang('SiteSettings.saveButton')) ?></button>
            </form>
        </div>
    </div>
<?= $this->endSection() ?>
