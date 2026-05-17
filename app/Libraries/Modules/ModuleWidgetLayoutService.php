<?php

/**
 * Widget layout preference service for scope-specific visibility and ordering.
 */

namespace App\Libraries\Modules;

use App\Models\ModuleWidgetLayoutPreferenceModel;

/**
 * Resolve and persist widget layout preferences for default and scoped contexts.
 */
class ModuleWidgetLayoutService
{
    private ModuleWidgetLayoutPreferenceModel $preferences;

    public function __construct()
    {
        $this->preferences = new ModuleWidgetLayoutPreferenceModel();
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getDefaultByScope(string $scopeType): array
    {
        $rows = $this->preferences
            ->where('scope_type', $scopeType)
            ->where('scope_id', 0)
            ->findAll();

        return $this->keyByModuleSlug($rows);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getScoped(string $scopeType, int $scopeId): array
    {
        $rows = $this->preferences
            ->where('scope_type', $scopeType)
            ->where('scope_id', $scopeId)
            ->findAll();

        return $this->keyByModuleSlug($rows);
    }

    /**
     * @param array<string, mixed> $module
     * @param array<string, array<string, mixed>> $default
     * @param array<string, array<string, mixed>> $scoped
     * @return array{is_visible: bool, display_order: int}
     */
    public function resolveForModule(array $module, array $default, array $scoped): array
    {
        $slug = (string) ($module['slug'] ?? '');

        return $this->resolveForWidget($slug, $module, $default, $scoped);
    }

    /**
     * Resolve widget layout for a concrete widget key.
     *
     * Widget-specific preferences override module-level defaults/scoped values.
     *
     * @param array<string, mixed> $module
     * @param array<string, array<string, mixed>> $default
     * @param array<string, array<string, mixed>> $scoped
     * @return array{is_visible: bool, display_order: int}
     */
    public function resolveForWidget(string $widgetKey, array $module, array $default, array $scoped): array
    {
        $slug = (string) ($module['slug'] ?? '');
        $isVisible = true;
        $displayOrder = (int) ($module['display_order'] ?? 0);

        if (isset($default[$slug])) {
            $isVisible = (bool) ($default[$slug]['is_visible'] ?? true);
            if ($default[$slug]['display_order'] !== null) {
                $displayOrder = (int) $default[$slug]['display_order'];
            }
        }

        if (isset($scoped[$slug])) {
            $isVisible = (bool) ($scoped[$slug]['is_visible'] ?? $isVisible);
            if ($scoped[$slug]['display_order'] !== null) {
                $displayOrder = (int) $scoped[$slug]['display_order'];
            }
        }

        if ($widgetKey !== '' && $widgetKey !== $slug && isset($default[$widgetKey])) {
            $isVisible = (bool) ($default[$widgetKey]['is_visible'] ?? $isVisible);
            if ($default[$widgetKey]['display_order'] !== null) {
                $displayOrder = (int) $default[$widgetKey]['display_order'];
            }
        }

        if ($widgetKey !== '' && $widgetKey !== $slug && isset($scoped[$widgetKey])) {
            $isVisible = (bool) ($scoped[$widgetKey]['is_visible'] ?? $isVisible);
            if ($scoped[$widgetKey]['display_order'] !== null) {
                $displayOrder = (int) $scoped[$widgetKey]['display_order'];
            }
        }

        return [
            'is_visible' => $isVisible,
            'display_order' => $displayOrder,
        ];
    }

    public function upsert(
        string $scopeType,
        int $scopeId,
        string $moduleSlug,
        bool $isVisible,
        int $displayOrder,
        int $actorId,
    ): void {
        $existing = $this->preferences
            ->where('scope_type', $scopeType)
            ->where('scope_id', $scopeId)
            ->where('module_slug', $moduleSlug)
            ->first();

        $payload = [
            'scope_type' => $scopeType,
            'scope_id' => $scopeId,
            'module_slug' => $moduleSlug,
            'is_visible' => $isVisible ? 1 : 0,
            'display_order' => max(0, $displayOrder),
            'updated_by_user_id' => $actorId,
        ];

        if (! is_array($existing)) {
            $this->preferences->insert($payload);
            $this->clearWidgetCaches($scopeType, $scopeId);

            return;
        }

        $this->preferences->update((int) $existing['id'], $payload);
        $this->clearWidgetCaches($scopeType, $scopeId);
    }

    private function clearWidgetCaches(string $scopeType, int $scopeId): void
    {
        $cache = cache();

        if (method_exists($cache, 'deleteMatching')) {
            if ($scopeId === 0) {
                $cache->deleteMatching('widgets_' . $scopeType . '_*');
                $cache->deleteMatching('widgets_html_' . $scopeType . '_*');

                return;
            }

            $cache->deleteMatching('widgets_' . $scopeType . '_' . $scopeId . '_*');
            $cache->deleteMatching('widgets_html_' . $scopeType . '_' . $scopeId . '*');

            return;
        }

        $cache->clean();
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return array<string, array<string, mixed>>
     */
    private function keyByModuleSlug(array $rows): array
    {
        $indexed = [];

        foreach ($rows as $row) {
            $slug = (string) ($row['module_slug'] ?? '');
            if ($slug === '') {
                continue;
            }

            $indexed[$slug] = $row;
        }

        return $indexed;
    }
}
