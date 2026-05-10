<?php
use App\Libraries\Auth\RbacService;
use App\Libraries\Theme\ThemeSettingsService;

/** @var string $pageTitle */
/** @var string $active */
$pageTitle = (string) ($pageTitle ?? '');
$active = (string) ($active ?? '');
$activeLocale = service('request')->getLocale();
$theme = (new ThemeSettingsService())->get();
$logoPath = trim((string) ($theme['logo_path'] ?? ''));
$userId = session('user_id');
$canManageTheme = false;

if (is_int($userId) || ctype_digit((string) $userId)) {
    $canManageTheme = (new RbacService())->hasPermission((int) $userId, 'system.theme.manage', 'system', null);
}
?>
<header class="border-bottom bg-white">
    <div class="container py-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
            <?php if ($logoPath !== ''): ?>
                <img src="<?= esc(base_url($logoPath)) ?>" alt="<?= esc(lang('Theme.logoAlt')) ?>" style="height:40px; width:auto;">
            <?php endif; ?>
            <h1 class="h5 mb-0"><?= esc($pageTitle) ?></h1>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <a href="<?= site_url('dashboard') ?>" class="btn btn-sm <?= $active === 'dashboard' ? 'btn-primary' : 'btn-outline-secondary' ?>"><?= esc(lang('Auth.dashboardTitle')) ?></a>
            <a href="<?= site_url('programmes') ?>" class="btn btn-sm <?= $active === 'programmes' ? 'btn-primary' : 'btn-outline-secondary' ?>"><?= esc(lang('Domain.programmesTitle')) ?></a>
            <a href="<?= site_url('projects') ?>" class="btn btn-sm <?= $active === 'projects' ? 'btn-primary' : 'btn-outline-secondary' ?>"><?= esc(lang('Domain.projectsTitle')) ?></a>
            <a href="<?= site_url('profile') ?>" class="btn btn-sm <?= $active === 'profile' ? 'btn-primary' : 'btn-outline-secondary' ?>"><?= esc(lang('Auth.profileButton')) ?></a>
            <?php if ($canManageTheme): ?>
                <a href="<?= site_url('theme') ?>" class="btn btn-sm <?= $active === 'theme' ? 'btn-primary' : 'btn-outline-secondary' ?>"><?= esc(lang('Theme.navLabel')) ?></a>
            <?php endif; ?>
            <form method="post" action="<?= site_url('language') ?>" class="m-0 d-flex align-items-center gap-1">
                <?= csrf_field() ?>
                <label class="visually-hidden" for="header-locale"><?= esc(lang('Auth.languageSelectorLabel')) ?></label>
                <select id="header-locale" name="locale" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="<?= esc(lang('Auth.languageSelectorLabel')) ?>">
                    <option value="en" <?= $activeLocale === 'en' ? 'selected' : '' ?>><?= esc(lang('Auth.languageEnglish')) ?></option>
                    <option value="fr" <?= $activeLocale === 'fr' ? 'selected' : '' ?>><?= esc(lang('Auth.languageFrench')) ?></option>
                </select>
                <noscript>
                    <button class="btn btn-outline-secondary btn-sm" type="submit"><?= esc(lang('Auth.languageSelectorApply')) ?></button>
                </noscript>
            </form>
            <form method="post" action="<?= site_url('logout') ?>" class="m-0">
                <?= csrf_field() ?>
                <button class="btn btn-outline-secondary btn-sm" type="submit"><?= esc(lang('Auth.logoutButton')) ?></button>
            </form>
        </div>
    </div>
</header>
