<?php
/** @var string $pageTitle */
/** @var string $active */
$pageTitle = (string) ($pageTitle ?? '');
$active = (string) ($active ?? '');
?>
<header class="border-bottom bg-white">
    <div class="container py-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <h1 class="h5 mb-0"><?= esc($pageTitle) ?></h1>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <a href="<?= site_url('dashboard') ?>" class="btn btn-sm <?= $active === 'dashboard' ? 'btn-primary' : 'btn-outline-secondary' ?>"><?= esc(lang('Auth.dashboardTitle')) ?></a>
            <a href="<?= site_url('programmes') ?>" class="btn btn-sm <?= $active === 'programmes' ? 'btn-primary' : 'btn-outline-secondary' ?>"><?= esc(lang('Domain.programmesTitle')) ?></a>
            <a href="<?= site_url('projects') ?>" class="btn btn-sm <?= $active === 'projects' ? 'btn-primary' : 'btn-outline-secondary' ?>"><?= esc(lang('Domain.projectsTitle')) ?></a>
            <a href="<?= site_url('profile') ?>" class="btn btn-sm <?= $active === 'profile' ? 'btn-primary' : 'btn-outline-secondary' ?>"><?= esc(lang('Auth.profileButton')) ?></a>
            <form method="post" action="<?= site_url('logout') ?>" class="m-0">
                <?= csrf_field() ?>
                <button class="btn btn-outline-secondary btn-sm" type="submit"><?= esc(lang('Auth.logoutButton')) ?></button>
            </form>
        </div>
    </div>
</header>
