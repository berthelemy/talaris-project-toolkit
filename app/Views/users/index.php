<?php
$pageTitle = (string) lang('UserAdmin.pageTitle');
$active = 'users';
?>
<?php $filters = (array) ($filters ?? []); ?>
<?php $users = (array) ($users ?? []); ?>
<?php $rolesByUser = (array) ($rolesByUser ?? []); ?>
<?php $availableRoles = (array) ($availableRoles ?? []); ?>
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

    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h6 mb-3"><?= esc(lang('UserAdmin.searchHeading')) ?></h2>
                    <form method="get" action="<?= site_url('users') ?>" class="row g-2 mb-3">
                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label" for="username_filter"><?= esc(lang('UserAdmin.usernameLabel')) ?></label>
                            <input id="username_filter" type="text" name="username" value="<?= esc((string) ($filters['username'] ?? '')) ?>" class="form-control">
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label" for="email_filter"><?= esc(lang('UserAdmin.emailLabel')) ?></label>
                            <input id="email_filter" type="text" name="email" value="<?= esc((string) ($filters['email'] ?? '')) ?>" class="form-control">
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label" for="status_filter"><?= esc(lang('UserAdmin.statusLabel')) ?></label>
                            <?php $status = (string) ($filters['status'] ?? ''); ?>
                            <select id="status_filter" name="status" class="form-select">
                                <option value=""><?= esc(lang('UserAdmin.statusAny')) ?></option>
                                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>><?= esc(lang('UserAdmin.statusActive')) ?></option>
                                <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>><?= esc(lang('UserAdmin.statusInactive')) ?></option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label" for="role_filter"><?= esc(lang('UserAdmin.roleLabel')) ?></label>
                            <?php $selectedRole = (string) ($filters['role'] ?? ''); ?>
                            <select id="role_filter" name="role" class="form-select">
                                <option value=""><?= esc(lang('UserAdmin.roleAny')) ?></option>
                                <?php foreach ($availableRoles as $role): ?>
                                    <?php $slug = (string) ($role['slug'] ?? ''); ?>
                                    <option value="<?= esc($slug) ?>" <?= $slug === $selectedRole ? 'selected' : '' ?>>
                                        <?= esc((string) ($role['name'] ?? $slug)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm"><?= esc(lang('UserAdmin.searchButton')) ?></button>
                            <a href="<?= site_url('users') ?>" class="btn btn-outline-secondary btn-sm"><?= esc(lang('UserAdmin.resetButton')) ?></a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                            <tr>
                                <th scope="col"><?= esc(lang('UserAdmin.usernameLabel')) ?></th>
                                <th scope="col"><?= esc(lang('UserAdmin.emailLabel')) ?></th>
                                <th scope="col"><?= esc(lang('UserAdmin.statusLabel')) ?></th>
                                <th scope="col"><?= esc(lang('UserAdmin.assignedRolesLabel')) ?></th>
                                <th scope="col" class="text-end"><?= esc(lang('UserAdmin.actionsLabel')) ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if ($users === []): ?>
                                <tr>
                                    <td colspan="5" class="text-muted"><?= esc(lang('UserAdmin.noUsersFound')) ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($users as $user): ?>
                                <?php $uid = (int) $user['id']; ?>
                                <?php $roleLabels = (array) ($rolesByUser[$uid] ?? []); ?>
                                <tr>
                                    <td><?= esc((string) $user['username']) ?></td>
                                    <td><?= esc((string) $user['email']) ?></td>
                                    <td>
                                        <?php if ((bool) ($user['is_active'] ?? false)): ?>
                                            <span class="badge text-bg-success"><?= esc(lang('UserAdmin.statusActive')) ?></span>
                                        <?php else: ?>
                                            <span class="badge text-bg-secondary"><?= esc(lang('UserAdmin.statusInactive')) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($roleLabels === []): ?>
                                            <span class="text-muted"><?= esc(lang('UserAdmin.noRolesAssigned')) ?></span>
                                        <?php else: ?>
                                            <?= esc(implode(', ', $roleLabels)) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= site_url('users/' . $uid . '/edit') ?>" class="btn btn-outline-primary btn-sm">
                                            <?= esc(lang('UserAdmin.editButton')) ?>
                                        </a>
                                        <?php if ((bool) ($user['is_active'] ?? false)): ?>
                                            <form method="post" action="<?= site_url('users/' . $uid . '/deactivate') ?>" class="d-inline">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('<?= esc(lang('UserAdmin.deactivateConfirm')) ?>');">
                                                    <?= esc(lang('UserAdmin.deactivateButton')) ?>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h6 mb-3"><?= esc(lang('UserAdmin.createHeading')) ?></h2>
                    <form method="post" action="<?= site_url('users') ?>" novalidate>
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label" for="create_username"><?= esc(lang('UserAdmin.usernameLabel')) ?></label>
                            <input id="create_username" name="username" type="text" class="form-control" maxlength="100" required value="<?= esc((string) old('username')) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="create_email"><?= esc(lang('UserAdmin.emailLabel')) ?></label>
                            <input id="create_email" name="email" type="email" class="form-control" maxlength="255" required value="<?= esc((string) old('email')) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="create_password"><?= esc(lang('UserAdmin.initialPasswordLabel')) ?></label>
                            <input id="create_password" name="password" type="password" class="form-control" required>
                        </div>
                        <?php $createActive = old('is_active'); ?>
                        <div class="form-check mb-3">
                            <input id="create_active" name="is_active" type="checkbox" class="form-check-input" value="1" <?= $createActive === null || $createActive === '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="create_active"><?= esc(lang('UserAdmin.activeFlagLabel')) ?></label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><?= esc(lang('UserAdmin.createButton')) ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>
