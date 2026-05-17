<?php

/**
 * HelloWorldProgramme module view template: index.
 */
?>
<!doctype html>
<?php $locale = (string) service('request')->getLocale(); ?>
<html lang="<?= esc($locale) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc(lang('Module.programmeHelloWorldTitle')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <?= view('layouts/theme_assets') ?>
</head>
<body class="bg-light">
<?= view('layouts/app_header', ['pageTitle' => lang('Module.programmeHelloWorldTitle'), 'active' => 'programmes']) ?>
<main class="container py-4">
    <?php
    $lockDeniedData = [];
    if (isset($lockDenied) && is_array($lockDenied)) {
        $lockDeniedData = $lockDenied;
    }
    ?>
    <div class="mb-3">
        <a class="btn btn-outline-secondary btn-sm" href="<?= site_url('programmes/' . (int) ($programme['id'] ?? 0)) ?>"><?= esc(lang('Module.backToProgramme')) ?></a>
    </div>
    <?php if (session('error') !== null): ?>
        <div class="alert alert-danger" role="alert"><?= esc((string) session('error')) ?></div>
    <?php endif; ?>
    <?php if (session('success') !== null): ?>
        <div class="alert alert-success" role="alert"><?= esc((string) session('success')) ?></div>
    <?php endif; ?>
    <?php if (session('errors') !== null): ?>
        <?php foreach ((array) session('errors') as $error): ?>
            <div class="alert alert-danger" role="alert"><?= esc((string) $error) ?></div>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php if (($isReadOnly ?? false) === true): ?>
        <div class="alert alert-warning" role="alert">
            <?php if ($lockDeniedData !== []): ?>
                <?= esc(lang('Module.lockedByOtherEditor', [
                    (string) (($lockDeniedData['locked_by_username'] ?? '') !== '' ? $lockDeniedData['locked_by_username'] : ('#' . (int) ($lockDeniedData['locked_by_user_id'] ?? 0))),
                    (string) ($lockDeniedData['expires_at'] ?? ''),
                ])) ?>
            <?php else: ?>
                <?= esc(lang('Module.readOnlyNotice')) ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h2 class="h5 mb-2"><?= esc((string) ($programme['name'] ?? '')) ?></h2>
            <p class="mb-0 text-muted"><?= esc(lang('Module.programmeHelloWorldDescription')) ?></p>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="post" action="<?= site_url('programmes/' . (int) ($programme['id'] ?? 0) . '/modules/hello-world') ?>" class="row g-2 align-items-end">
                <?= csrf_field() ?>
                <div class="col-12 col-md-9">
                    <label for="message" class="form-label"><?= esc(lang('Module.entryLabel')) ?></label>
                    <input id="message" name="message" type="text" maxlength="500" class="form-control" required value="<?= esc((string) old('message')) ?>" <?= ($isReadOnly ?? false) ? 'readonly' : '' ?>>
                </div>
                <div class="col-12 col-md-3">
                    <button class="btn btn-primary w-100" type="submit" <?= ($isReadOnly ?? false) ? 'disabled' : '' ?>><?= esc(lang('Module.entryCreateButton')) ?></button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="p-3 border-bottom">
                <h3 class="h6 mb-0"><?= esc(lang('Module.entriesTitle')) ?></h3>
            </div>
            <?php if (empty($entries)): ?>
                <p class="text-muted p-4 mb-0"><?= esc(lang('Module.entriesNone')) ?></p>
            <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($entries as $entry): ?>
                        <li id="entry-<?= (int) ($entry['id'] ?? 0) ?>" class="list-group-item">
                            <input
                                id="entry-message-<?= (int) ($entry['id'] ?? 0) ?>"
                                name="message"
                                type="text"
                                class="form-control"
                                <?= ($isReadOnly ?? false) ? 'readonly' : '' ?>
                                value="<?= esc((string) ($entry['message'] ?? '')) ?>"
                                maxlength="500"
                                data-autosave="<?= ($isReadOnly ?? false) ? 'false' : 'true' ?>"
                                data-autosave-url="<?= site_url('programmes/' . (int) ($programme['id'] ?? 0) . '/modules/hello-world/entries/' . (int) ($entry['id'] ?? 0) . '/autosave') ?>"
                                data-last-updated-at="<?= esc((string) ($entry['updated_at'] ?? '')) ?>"
                                data-autosave-status="entry-status-<?= (int) ($entry['id'] ?? 0) ?>"
                                data-status-saving="<?= esc(lang('Module.autosaveSaving')) ?>"
                                data-status-saved="<?= esc(lang('Module.autosaveSaved')) ?>"
                                data-status-error="<?= esc(lang('Module.autosaveError')) ?>"
                                data-status-conflict="<?= esc(lang('Module.autosaveConflict')) ?>"
                                data-status-locked="<?= esc(lang('Module.autosaveLocked')) ?>"
                                data-csrf-name="<?= esc(csrf_token()) ?>"
                                data-csrf-value="<?= esc(csrf_hash()) ?>"
                                data-csrf-cookie-name="<?= esc((string) config('Security')->cookieName) ?>"
                            >
                            <div id="entry-status-<?= (int) ($entry['id'] ?? 0) ?>" class="small text-muted mt-1">
                                <?= esc(($isReadOnly ?? false) ? lang('Module.readOnlyNotice') : lang('Module.autosaveIdle')) ?>
                            </div>
                            <div class="text-muted small"><?= esc((string) ($entry['created_at'] ?? '')) ?></div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</main>
<script src="<?= base_url('js/autosave.js') ?>"></script>
</body>
</html>
