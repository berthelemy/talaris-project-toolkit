<?php

/**
 * Shared layout partial: theme assets.
 */

use App\Libraries\Theme\ThemeSettingsService;

$theme = (new ThemeSettingsService())->get();
$fontImports = (array) ($theme['font_imports'] ?? []);
$bootstrapFontStack = 'system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji"';
$sanitizeFontStack = static function (string $stack, string $fallback): string {
    $stack = trim($stack);

    if ($stack === '') {
        return $fallback;
    }

    $sanitized = preg_replace('/[^A-Za-z0-9\s,"\'\-()]/', '', $stack);
    $sanitized = is_string($sanitized) ? preg_replace('/\s+/', ' ', $sanitized) : '';
    $sanitized = is_string($sanitized) ? trim($sanitized) : '';

    return $sanitized !== '' ? $sanitized : $fallback;
};
$headingFontStack = $sanitizeFontStack((string) ($theme['heading_font_stack'] ?? ''), $bootstrapFontStack);
$bodyFontStack = $sanitizeFontStack((string) ($theme['body_font_stack'] ?? ''), $bootstrapFontStack);
?>
<?php foreach ($fontImports as $fontImport): ?>
    <link rel="stylesheet" href="<?= esc((string) $fontImport, 'attr') ?>">
<?php endforeach; ?>
<link rel="stylesheet" href="<?= esc(base_url('css/app-theme.css'), 'attr') ?>">
<style>
    :root {
        --talaris-primary: <?= esc((string) ($theme['primary_color'] ?? '#0d6efd')) ?>;
        --talaris-secondary: <?= esc((string) ($theme['secondary_color'] ?? '#6c757d')) ?>;
        --talaris-background: <?= esc((string) ($theme['background_color'] ?? '#f8f9fa')) ?>;
        --talaris-text: <?= esc((string) ($theme['text_color'] ?? '#212529')) ?>;
        --talaris-heading-font-family: <?= esc($headingFontStack, 'raw') ?>;
        --talaris-body-font-family: <?= esc($bodyFontStack, 'raw') ?>;
    }
</style>
