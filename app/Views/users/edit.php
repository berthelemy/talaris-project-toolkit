<!doctype html>
<?php $locale = (string) service('request')->getLocale(); ?>
<?php $user = (array) ($user ?? []); ?>
<?php $availableRoles = (array) ($availableRoles ?? []); ?>
<?php $assignments = (array) ($assignments ?? []); ?>
<?php $programmes = (array) ($programmes ?? []); ?>
<?php $projects = (array) ($projects ?? []); ?>
<?php $canManageRoles = (bool) ($canManageRoles ?? false); ?>
<html lang="<?= esc($locale) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc(lang('UserAdmin.editPageTitle', ['username' => (string) ($user['username'] ?? '')])) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <?= view('layouts/theme_assets') ?>
</head>
<body class="bg-light">
<?= view('layouts/app_header', ['pageTitle' => lang('UserAdmin.pageTitle'), 'active' => 'users']) ?>
<main class="container py-4">
    <div class="mb-3">
        <a href="<?= site_url('users') ?>" class="btn btn-outline-secondary btn-sm"><?= esc(lang('UserAdmin.backToList')) ?></a>
    </div>

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

    <div class="row g-4">
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h6 mb-3"><?= esc(lang('UserAdmin.editHeading')) ?></h2>
                    <form method="post" action="<?= site_url('users/' . (int) ($user['id'] ?? 0)) ?>" novalidate>
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label" for="edit_username"><?= esc(lang('UserAdmin.usernameLabel')) ?></label>
                            <input id="edit_username" name="username" type="text" class="form-control" maxlength="100" required value="<?= esc((string) old('username', (string) ($user['username'] ?? ''))) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="edit_email"><?= esc(lang('UserAdmin.emailLabel')) ?></label>
                            <input id="edit_email" name="email" type="email" class="form-control" maxlength="255" required value="<?= esc((string) old('email', (string) ($user['email'] ?? ''))) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="edit_language"><?= esc(lang('Auth.profileLanguage')) ?></label>
                            <?php $language = (string) old('language_preference', (string) ($user['language_preference'] ?? '')); ?>
                            <select class="form-select" id="edit_language" name="language_preference">
                                <option value=""><?= esc(lang('Auth.profileLanguageNoPreference')) ?></option>
                                <option value="en" <?= $language === 'en' ? 'selected' : '' ?>><?= esc(lang('Auth.languageEnglish')) ?></option>
                                <option value="fr" <?= $language === 'fr' ? 'selected' : '' ?>><?= esc(lang('Auth.languageFrench')) ?></option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="edit_profile_description"><?= esc(lang('Auth.profileDescription')) ?></label>
                            <textarea id="edit_profile_description" name="profile_description" rows="4" class="form-control" maxlength="1000"><?= esc((string) old('profile_description', (string) ($user['profile_description'] ?? ''))) ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="reset_password_to"><?= esc(lang('UserAdmin.optionalResetPasswordLabel')) ?></label>
                            <input id="reset_password_to" name="reset_password_to" type="password" class="form-control" autocomplete="new-password">
                            <div class="form-text"><?= esc(lang('UserAdmin.optionalResetPasswordHint')) ?></div>
                        </div>
                        <?php $checked = old('is_active', (string) ((int) ($user['is_active'] ?? 0))); ?>
                        <div class="form-check mb-3">
                            <input id="edit_active" name="is_active" type="checkbox" class="form-check-input" value="1" <?= (string) $checked === '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="edit_active"><?= esc(lang('UserAdmin.activeFlagLabel')) ?></label>
                        </div>
                        <button type="submit" class="btn btn-primary"><?= esc(lang('UserAdmin.saveButton')) ?></button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="h6 mb-3"><?= esc(lang('UserAdmin.assignedRolesLabel')) ?></h2>
                    <?php if ($assignments === []): ?>
                        <p class="text-muted mb-0"><?= esc(lang('UserAdmin.noRolesAssigned')) ?></p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                <tr>
                                    <th scope="col"><?= esc(lang('UserAdmin.roleLabel')) ?></th>
                                    <th scope="col"><?= esc(lang('UserAdmin.scopeLabel')) ?></th>
                                    <th scope="col" class="text-end"><?= esc(lang('UserAdmin.actionsLabel')) ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($assignments as $assignment): ?>
                                    <?php $scopeType = (string) ($assignment['scope_type'] ?? ''); ?>
                                    <?php $scopeId = $assignment['scope_id']; ?>
                                    <tr>
                                        <td><?= esc((string) ($assignment['role_name'] ?? '')) ?></td>
                                        <td>
                                            <?= esc($scopeType) ?>
                                            <?php if ($scopeId !== null): ?>
                                                #<?= esc((string) (int) $scopeId) ?>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <?php if ($canManageRoles): ?>
                                                <form method="post" action="<?= site_url('users/' . (int) ($user['id'] ?? 0) . '/roles/revoke') ?>" class="d-inline">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="role_slug" value="<?= esc((string) ($assignment['role_slug'] ?? '')) ?>">
                                                    <input type="hidden" name="scope_type" value="<?= esc($scopeType) ?>">
                                                    <input type="hidden" name="scope_id" value="<?= esc($scopeId === null ? '' : (string) (int) $scopeId) ?>">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm"><?= esc(lang('UserAdmin.revokeButton')) ?></button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($canManageRoles): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h2 class="h6 mb-3"><?= esc(lang('UserAdmin.assignRoleHeading')) ?></h2>
                        <form method="post" action="<?= site_url('users/' . (int) ($user['id'] ?? 0) . '/roles') ?>" id="role-assignment-form">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label class="form-label" for="role_slug"><?= esc(lang('UserAdmin.roleLabel')) ?></label>
                                <select id="role_slug" name="role_slug" class="form-select" required>
                                    <option value=""><?= esc(lang('UserAdmin.selectRolePlaceholder')) ?></option>
                                    <?php foreach ($availableRoles as $role): ?>
                                        <option value="<?= esc((string) $role['slug']) ?>"><?= esc((string) $role['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="scope_type"><?= esc(lang('UserAdmin.scopeLabel')) ?></label>
                                <select id="scope_type" name="scope_type" class="form-select" required>
                                    <option value="system"><?= esc(lang('UserAdmin.scopeSystem')) ?></option>
                                    <option value="programme"><?= esc(lang('UserAdmin.scopeProgramme')) ?></option>
                                    <option value="project"><?= esc(lang('UserAdmin.scopeProject')) ?></option>
                                </select>
                            </div>
                            <div class="mb-3 d-none" data-scope-field="programme">
                                <label class="form-label" for="programme_scope_id"><?= esc(lang('UserAdmin.scopeProgramme')) ?></label>
                                <select id="programme_scope_id" name="programme_scope_id" class="form-select">
                                    <option value=""><?= esc(lang('UserAdmin.selectProgrammePlaceholder')) ?></option>
                                    <?php foreach ($programmes as $programme): ?>
                                        <option value="<?= esc((string) (int) $programme['id']) ?>"><?= esc((string) $programme['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3 d-none" data-scope-field="project">
                                <label class="form-label" for="project_scope_id"><?= esc(lang('UserAdmin.scopeProject')) ?></label>
                                <select id="project_scope_id" name="project_scope_id" class="form-select">
                                    <option value=""><?= esc(lang('UserAdmin.selectProjectPlaceholder')) ?></option>
                                    <?php foreach ($projects as $project): ?>
                                        <option value="<?= esc((string) (int) $project['id']) ?>"><?= esc((string) $project['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-outline-primary"><?= esc(lang('UserAdmin.assignButton')) ?></button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>
<script>
    (function () {
        var scopeSelect = document.getElementById('scope_type');
        if (!scopeSelect) {
            return;
        }

        var programmeField = document.querySelector('[data-scope-field="programme"]');
        var projectField = document.querySelector('[data-scope-field="project"]');

        var toggleFields = function () {
            var value = String(scopeSelect.value || 'system');
            if (programmeField) {
                programmeField.classList.toggle('d-none', value !== 'programme');
            }
            if (projectField) {
                projectField.classList.toggle('d-none', value !== 'project');
            }
        };

        scopeSelect.addEventListener('change', toggleFields);
        toggleFields();
    })();
</script>
</body>
</html>
