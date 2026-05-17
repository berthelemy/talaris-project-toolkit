<?php

/**
 * Shared layout partial: app header.
 */
use App\Libraries\Auth\RbacService;
use App\Libraries\Theme\ThemeSettingsService;

/** @var string $pageTitle */
/** @var string $active */
$pageTitle = (string) ($pageTitle ?? '');
$active = (string) ($active ?? '');
$activeLocale = service('request')->getLocale();
$theme = (new ThemeSettingsService())->get();
$siteTitle = trim((string) ($theme['site_title'] ?? ''));
$siteTitle = $siteTitle !== '' ? $siteTitle : 'Talaris Project Toolkit';
$logoPath = trim((string) ($theme['logo_path'] ?? ''));
$userId = session('user_id');
$canManageTheme = false;
$canManageUsers = false;
$canManageModules = false;

if (is_int($userId) || ctype_digit((string) $userId)) {
    $rbac = new RbacService();
    $canManageTheme = $rbac->hasPermission((int) $userId, 'system.theme.manage', 'system', null);
    $canManageUsers = $rbac->hasPermission((int) $userId, 'system.users.invite', 'system', null)
        || $rbac->hasPermission((int) $userId, 'system.users.impersonate', 'system', null);
    $canManageModules = $rbac->hasPermission((int) $userId, 'system.modules.manage', 'system', null)
        || $rbac->hasPermission((int) $userId, 'system.modules.add', 'system', null);
}
?>
<header class="border-bottom bg-white">
    <nav class="container navbar navbar-expand-lg py-2" aria-label="Main">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= site_url('dashboard') ?>">
            <?php if ($logoPath !== ''): ?>
                <img class="app-header-logo" src="<?= esc(base_url($logoPath)) ?>" alt="<?= esc(lang('Theme.logoAlt')) ?>">
            <?php endif; ?>
            <span class="fw-semibold"><?= esc($siteTitle) ?></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a href="<?= site_url('programmes') ?>" class="nav-link <?= $active === 'programmes' ? 'active fw-semibold' : '' ?>"><?= esc(lang('Domain.programmesTitle')) ?></a>
                </li>
                <li class="nav-item">
                    <a href="<?= site_url('projects') ?>" class="nav-link <?= $active === 'projects' ? 'active fw-semibold' : '' ?>"><?= esc(lang('Domain.projectsTitle')) ?></a>
                </li>
                <?php if ($canManageUsers || $canManageModules || $canManageTheme): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?= in_array($active, ['users', 'modules', 'theme', 'site_settings'], true) ? 'active fw-semibold' : '' ?>" href="#" id="adminMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?= esc(lang('Domain.adminMenuLabel')) ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="adminMenu">
                            <?php if ($canManageUsers): ?>
                                <li><a class="dropdown-item" href="<?= site_url('users') ?>"><?= esc(lang('UserAdmin.navLabel')) ?></a></li>
                            <?php endif; ?>
                            <?php if ($canManageModules): ?>
                                <li><a class="dropdown-item" href="<?= site_url('modules') ?>"><?= esc(lang('Module.navLabel')) ?></a></li>
                            <?php endif; ?>
                            <?php if ($canManageTheme): ?>
                                <li><a class="dropdown-item" href="<?= site_url('site-settings') ?>"><?= esc(lang('SiteSettings.navLabel')) ?></a></li>
                                <li><a class="dropdown-item" href="<?= site_url('theme') ?>"><?= esc(lang('Theme.navLabel')) ?></a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a href="<?= site_url('profile') ?>" class="nav-link <?= $active === 'profile' ? 'active fw-semibold' : '' ?>"><?= esc(lang('Auth.profileButton')) ?></a>
                </li>
            </ul>

            <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-2">
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
    </nav>
</header>
