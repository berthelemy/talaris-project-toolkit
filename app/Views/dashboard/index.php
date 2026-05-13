<?php
$pageTitle = (string) lang('Auth.dashboardTitle');
$active = 'dashboard';
?>
<?php $displayUsername = (string) ($username ?? session('username') ?? ''); ?>
<?php $canImpersonateUsers = (bool) ($canImpersonate ?? false); ?>
<?php $isImpersonatingSession = (bool) ($isImpersonating ?? false); ?>
<?php $impersonationUsers = (array) ($impersonationCandidates ?? []); ?>
<?php $impersonatorName = (string) ($impersonatorUsername ?? ''); ?>
<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
    <?php if (session('error') !== null): ?>
        <div class="alert alert-danger" role="alert"><?= esc((string) session('error')) ?></div>
    <?php endif; ?>
    <?php if (session('success') !== null): ?>
        <div class="alert alert-success" role="alert"><?= esc((string) session('success')) ?></div>
    <?php endif; ?>

    <?php if ($isImpersonatingSession): ?>
        <div class="alert alert-warning d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3" role="alert">
            <span><?= esc(lang('Auth.impersonationBanner', ['username' => $impersonatorName])) ?></span>
            <form method="post" action="<?= site_url('impersonate/stop') ?>">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-dark btn-sm"><?= esc(lang('Auth.impersonationStopButton')) ?></button>
            </form>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <p class="mb-0"><?= esc(lang('Auth.dashboardSubtitle', ['username' => $displayUsername])) ?></p>
        </div>
    </div>

    <?php if ($canImpersonateUsers): ?>
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-body">
                <h2 class="h6 mb-3"><?= esc(lang('Auth.impersonationTitle')) ?></h2>
                <form method="post" action="<?= site_url('impersonate/0') ?>" id="impersonation-form" class="row g-2 align-items-end">
                    <?= csrf_field() ?>
                    <div class="col-12 col-md-8">
                        <label for="impersonation_user" class="form-label"><?= esc(lang('Auth.impersonationSelectLabel')) ?></label>
                        <select id="impersonation_user" name="impersonation_user" class="form-select" required>
                            <option value=""><?= esc(lang('Auth.impersonationSelectLabel')) ?></option>
                            <?php foreach ($impersonationUsers as $user): ?>
                                <option value="<?= esc((string) $user['id']) ?>"><?= esc((string) $user['username']) ?> (<?= esc((string) $user['email']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <button class="btn btn-primary w-100" type="submit"><?= esc(lang('Auth.impersonationStartButton')) ?></button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
<?= $this->endSection() ?>

<?= $this->section('extraScripts') ?>
    <?php if ($canImpersonateUsers): ?>
        <script>
            (function () {
                var form = document.getElementById('impersonation-form');
                var select = document.getElementById('impersonation_user');
                if (!form || !select) {
                    return;
                }

                form.addEventListener('submit', function (event) {
                    var target = String(select.value || '').trim();
                    if (target === '') {
                        event.preventDefault();
                        return;
                    }

                    form.action = '<?= site_url('impersonate') ?>/' + encodeURIComponent(target);
                });
            })();
        </script>
    <?php endif; ?>
<?= $this->endSection() ?>
