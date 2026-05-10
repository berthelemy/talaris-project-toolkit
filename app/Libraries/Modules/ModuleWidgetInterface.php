<?php

namespace App\Libraries\Modules;

/**
 * Interface for modules that provide dashboard widgets.
 * Modules implementing this interface can display summary content
 * on Programme and Project detail pages.
 */
interface ModuleWidgetInterface
{
    /**
     * Get the widget view path for this scope.
     * Return null if widget should not be displayed for this scope.
     *
     * @param int $scopeId Programme or project identifier for the current page context.
     * @return string|null Full qualified view path (e.g., 'App\Modules\HelloWorldProgramme\Views\widget')
     */
    public function getWidgetView(int $scopeId): ?string;

    /**
     * Get widget data to pass to the view.
     *
     * @param int $scopeId Programme or project identifier for the current page context.
     * @return array<string, mixed> Data to be passed to the view
     */
    public function getWidgetData(int $scopeId, array $config = []): array;

    /**
     * Provide default widget configuration values.
     *
     * @return array<string, mixed>
     */
    public function getDefaultConfig(): array;
}
