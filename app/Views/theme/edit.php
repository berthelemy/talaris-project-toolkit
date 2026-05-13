<?php
$pageTitle = (string) lang('Theme.pageTitle');
$active = 'theme';
?>
<?php $settings = (array) ($settings ?? []); ?>
<?php $fontOptions = (array) ($fontOptions ?? []); ?>
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
            <p class="text-muted mb-4"><?= esc(lang('Theme.subtitle')) ?></p>
            <form method="post" action="<?= site_url('theme') ?>" enctype="multipart/form-data" novalidate>
                <?= csrf_field() ?>
                <div class="row g-4">
                    <div class="col-12 col-lg-6">
                        <label for="heading_font" class="form-label"><?= esc(lang('Theme.headingFontLabel')) ?></label>
                        <select id="heading_font" name="heading_font" class="form-select" required>
                            <?php $headingFont = (string) old('heading_font', (string) ($settings['heading_font'] ?? 'poppins')); ?>
                            <?php foreach ($fontOptions as $slug => $fontOption): ?>
                                <option value="<?= esc((string) $slug) ?>" <?= $headingFont === $slug ? 'selected' : '' ?>><?= esc(lang('Theme.font.' . $slug)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-lg-6">
                        <label for="body_font" class="form-label"><?= esc(lang('Theme.bodyFontLabel')) ?></label>
                        <select id="body_font" name="body_font" class="form-select" required>
                            <?php $bodyFont = (string) old('body_font', (string) ($settings['body_font'] ?? 'source_sans')); ?>
                            <?php foreach ($fontOptions as $slug => $fontOption): ?>
                                <option value="<?= esc((string) $slug) ?>" <?= $bodyFont === $slug ? 'selected' : '' ?>><?= esc(lang('Theme.font.' . $slug)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 col-lg-3">
                        <label for="primary_color" class="form-label"><?= esc(lang('Theme.primaryColorLabel')) ?></label>
                        <input id="primary_color" name="primary_color" type="color" class="form-control form-control-color" value="<?= esc((string) old('primary_color', (string) ($settings['primary_color'] ?? '#0d6efd'))) ?>">
                    </div>
                    <div class="col-6 col-lg-3">
                        <label for="secondary_color" class="form-label"><?= esc(lang('Theme.secondaryColorLabel')) ?></label>
                        <input id="secondary_color" name="secondary_color" type="color" class="form-control form-control-color" value="<?= esc((string) old('secondary_color', (string) ($settings['secondary_color'] ?? '#6c757d'))) ?>">
                    </div>
                    <div class="col-6 col-lg-3">
                        <label for="background_color" class="form-label"><?= esc(lang('Theme.backgroundColorLabel')) ?></label>
                        <input id="background_color" name="background_color" type="color" class="form-control form-control-color" value="<?= esc((string) old('background_color', (string) ($settings['background_color'] ?? '#f8f9fa'))) ?>">
                    </div>
                    <div class="col-6 col-lg-3">
                        <label for="text_color" class="form-label"><?= esc(lang('Theme.textColorLabel')) ?></label>
                        <input id="text_color" name="text_color" type="color" class="form-control form-control-color" value="<?= esc((string) old('text_color', (string) ($settings['text_color'] ?? '#212529'))) ?>">
                    </div>
                </div>

                <hr class="my-4">

                <div class="mb-3">
                    <label for="logo" class="form-label"><?= esc(lang('Theme.logoLabel')) ?></label>
                    <?php if (! empty($settings['logo_path'])): ?>
                        <div class="mb-2">
                            <img src="<?= esc(base_url((string) $settings['logo_path'])) ?>" alt="<?= esc(lang('Theme.logoAlt')) ?>" style="max-height:64px; width:auto;">
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" value="1" id="remove_logo" name="remove_logo">
                            <label class="form-check-label" for="remove_logo"><?= esc(lang('Theme.removeLogoLabel')) ?></label>
                        </div>
                    <?php endif; ?>
                    <input id="logo" name="logo" type="file" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp">
                    <div class="form-text"><?= esc(lang('Theme.logoHint')) ?></div>
                </div>

                <button class="btn btn-primary" type="submit"><?= esc(lang('Theme.saveButton')) ?></button>
            </form>
        </div>
    </div>
<?= $this->endSection() ?>
