<?php

use App\Libraries\Theme\ThemeSettingsService;

$theme = (new ThemeSettingsService())->get();
$fontImports = (array) ($theme['font_imports'] ?? []);
$headingFontStack = (string) ($theme['heading_font_stack'] ?? '"Poppins", "Segoe UI", Tahoma, Geneva, Verdana, sans-serif');
$bodyFontStack = (string) ($theme['body_font_stack'] ?? '"Source Sans 3", "Segoe UI", Tahoma, Geneva, Verdana, sans-serif');
?>
<?php foreach ($fontImports as $fontImport): ?>
    <link rel="stylesheet" href="<?= esc((string) $fontImport, 'attr') ?>">
<?php endforeach; ?>
<style>
    :root {
        --talaris-primary: <?= esc((string) ($theme['primary_color'] ?? '#0d6efd')) ?>;
        --talaris-secondary: <?= esc((string) ($theme['secondary_color'] ?? '#6c757d')) ?>;
        --talaris-background: <?= esc((string) ($theme['background_color'] ?? '#f8f9fa')) ?>;
        --talaris-text: <?= esc((string) ($theme['text_color'] ?? '#212529')) ?>;
    }

    body {
        background-color: var(--talaris-background) !important;
        color: var(--talaris-text);
        font-family: <?= esc($bodyFontStack) ?>;
    }

    h1,
    h2,
    h3,
    h4,
    h5,
    h6,
    .btn {
        font-family: <?= esc($headingFontStack) ?>;
    }

    a {
        color: var(--talaris-primary);
    }

    .btn-primary {
        --bs-btn-bg: var(--talaris-primary);
        --bs-btn-border-color: var(--talaris-primary);
        --bs-btn-hover-bg: color-mix(in srgb, var(--talaris-primary) 85%, #000 15%);
        --bs-btn-hover-border-color: color-mix(in srgb, var(--talaris-primary) 85%, #000 15%);
        --bs-btn-active-bg: color-mix(in srgb, var(--talaris-primary) 80%, #000 20%);
        --bs-btn-active-border-color: color-mix(in srgb, var(--talaris-primary) 80%, #000 20%);
    }

    .btn-outline-primary {
        --bs-btn-color: var(--talaris-primary);
        --bs-btn-border-color: var(--talaris-primary);
        --bs-btn-hover-bg: var(--talaris-primary);
        --bs-btn-hover-border-color: var(--talaris-primary);
        --bs-btn-active-bg: color-mix(in srgb, var(--talaris-primary) 85%, #000 15%);
        --bs-btn-active-border-color: color-mix(in srgb, var(--talaris-primary) 85%, #000 15%);
    }

    .table-light {
        --bs-table-bg: color-mix(in srgb, var(--talaris-background) 88%, var(--talaris-secondary) 12%);
    }

    .module-modal-body-scroll {
        max-height: 70vh;
        overflow-y: auto;
    }
</style>
