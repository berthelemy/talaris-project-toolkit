<?php

/**
 * Theme settings service for persisted branding and appearance configuration.
 */

namespace App\Libraries\Theme;

use App\Models\ThemeSettingsModel;
use Throwable;

/**
 * ThemeSettingsService component.
 */
class ThemeSettingsService
{
    /**
     * @var array<string, array{stack: string, import: string|null}>
     */
    private array $fontMap = [
        'poppins' => [
            'stack' => '"Poppins", "Segoe UI", Tahoma, Geneva, Verdana, sans-serif',
            'import' => 'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap',
        ],
        'merriweather' => [
            'stack' => '"Merriweather", Georgia, Cambria, "Times New Roman", serif',
            'import' => 'https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&display=swap',
        ],
        'source_sans' => [
            'stack' => '"Source Sans 3", "Segoe UI", Tahoma, Geneva, Verdana, sans-serif',
            'import' => 'https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;500;600;700&display=swap',
        ],
    ];

    /**
     * @return array<string, mixed>
     */
    public function get(): array
    {
        $fallback = [
            'site_title' => 'Talaris Project Toolkit',
            'logo_path' => null,
            'heading_font' => 'poppins',
            'body_font' => 'source_sans',
            'primary_color' => '#0d6efd',
            'secondary_color' => '#6c757d',
            'background_color' => '#f8f9fa',
            'text_color' => '#212529',
        ];

        try {
            $row = (new ThemeSettingsModel())->first();
        } catch (Throwable) {
            // Fallback keeps pages rendering when migrations are not yet applied.
            return $this->enrich($fallback);
        }

        if (! is_array($row)) {
            return $this->enrich($fallback);
        }

        $settings = [
            'site_title' => (string) ($row['site_title'] ?? $fallback['site_title']),
            'logo_path' => $row['logo_path'] ?? $fallback['logo_path'],
            'heading_font' => (string) ($row['heading_font'] ?? $fallback['heading_font']),
            'body_font' => (string) ($row['body_font'] ?? $fallback['body_font']),
            'primary_color' => (string) ($row['primary_color'] ?? $fallback['primary_color']),
            'secondary_color' => (string) ($row['secondary_color'] ?? $fallback['secondary_color']),
            'background_color' => (string) ($row['background_color'] ?? $fallback['background_color']),
            'text_color' => (string) ($row['text_color'] ?? $fallback['text_color']),
        ];

        return $this->enrich($settings);
    }

    /**
     * @return list<string>
     */
    public function allowedFonts(): array
    {
        return array_keys($this->fontMap);
    }

    /**
     * @return array<string, array{stack: string, import: string|null}>
     */
    public function fontOptions(): array
    {
        return $this->fontMap;
    }

    /**
     * @param array<string, mixed> $settings
     *
     * @return array<string, mixed>
     */
    private function enrich(array $settings): array
    {
        $headingFont = $this->fontMap[$settings['heading_font']] ?? $this->fontMap['poppins'];
        $bodyFont = $this->fontMap[$settings['body_font']] ?? $this->fontMap['source_sans'];

        $imports = [];

        if ($headingFont['import'] !== null) {
            $imports[] = $headingFont['import'];
        }

        if ($bodyFont['import'] !== null) {
            $imports[] = $bodyFont['import'];
        }

        $settings['heading_font_stack'] = $headingFont['stack'];
        $settings['body_font_stack'] = $bodyFont['stack'];
        $settings['font_imports'] = array_values(array_unique($imports));

        return $settings;
    }
}
