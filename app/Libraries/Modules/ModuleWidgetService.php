<?php

namespace App\Libraries\Modules;

/**
 * Service for managing and rendering module widgets on Programme/Project pages.
 */
class ModuleWidgetService
{
    private ModuleRegistryService $registryService;

    /**
    * Build widget service with module registry dependency.
     */
    public function __construct()
    {
        $this->registryService = new ModuleRegistryService();
    }

    /**
    * Discover enabled and accessible widgets for a scope record.
     *
    * @param string $scopeType Scope type, either 'programme' or 'project'.
    * @param int $scopeId Programme or project identifier.
    * @return array<string, array{widget: ModuleWidgetInterface, data: array<string, mixed>, view: string}> Widget definitions keyed by module slug.
     */
    public function getAvailableWidgets(string $scopeType, int $scopeId): array
    {
        $widgets = [];
        $actorId = (int) (session('user_id') ?? 0);

        if ($actorId === 0) {
            return $widgets;
        }

        // Get all enabled modules for this scope type
        $modules = $this->registryService->getEnabledModulesByType($scopeType);

        foreach ($modules as $module) {
            try {
                $widget = $this->loadModuleWidget($module['slug']);

                if ($widget !== null && $this->canAccessWidget($actorId, $module['slug'], $scopeType)) {
                    $widgetView = $widget->getWidgetView($scopeId);

                    if ($widgetView !== null) {
                        $widgets[$module['slug']] = [
                            'widget' => $widget,
                            'data' => $widget->getWidgetData($scopeId),
                            'view' => $widgetView,
                        ];
                    }
                }
            } catch (\Throwable $e) {
                // Log widget loading errors but don't break page rendering
                log_message('warning', "Failed to load widget for module {$module['slug']}: {$e->getMessage()}");
            }
        }

        return $widgets;
    }

    /**
    * Render all discovered widgets into concatenated HTML fragments.
     *
    * @param string $scopeType Scope type, either 'programme' or 'project'.
    * @param int $scopeId Programme or project identifier.
    * @return string Rendered widget markup.
     */
    public function renderWidgets(string $scopeType, int $scopeId): string
    {
        $widgets = $this->getAvailableWidgets($scopeType, $scopeId);
        $html = '';

        foreach ($widgets as $moduleSlug => $widget) {
            try {
                $html .= view($widget['view'], array_merge($widget['data'], [
                    'scope_id' => $scopeId,
                    'scope_type' => $scopeType,
                    'module_slug' => $moduleSlug,
                ]));
            } catch (\Throwable $e) {
                log_message('warning', "Failed to render widget for module {$moduleSlug}: {$e->getMessage()}");
            }
        }

        return $html;
    }

    /**
    * Resolve a widget provider implementation for a module slug.
     *
    * @param string $moduleSlug Module slug, for example 'hello_world_project'.
    * @return ModuleWidgetInterface|null Widget instance when discovered; otherwise null.
     */
    private function loadModuleWidget(string $moduleSlug): ?ModuleWidgetInterface
    {
        $moduleDir = APPPATH . 'Modules' . DIRECTORY_SEPARATOR;
        $modules = array_diff(scandir($moduleDir) ?? [], ['.', '..']);

        foreach ($modules as $module) {
            // Convert module directory name to registry slug
            // e.g., 'HelloWorldProject' -> 'hello_world_project'
            $computedSlug = $this->directoryToSlug($module);

            // Only load widget if slug matches
            if ($computedSlug !== $moduleSlug) {
                continue;
            }

            // Try common widget class locations
            $classNames = [
                "App\\Modules\\{$module}\\Widgets\\ModuleWidget",
                "App\\Modules\\{$module}\\Services\\WidgetService",
                "App\\Modules\\{$module}\\Controllers\\{$module}WidgetController",
            ];

            foreach ($classNames as $className) {
                if (class_exists($className) && in_array(ModuleWidgetInterface::class, class_implements($className) ?: [], true)) {
                    return new $className();
                }
            }
        }

        return null;
    }

    /**
     * Convert a module directory name to its registry slug.
     * e.g., 'HelloWorldProject' -> 'hello_world_project'
    *
    * @param string $module Directory name under app/Modules.
    * @return string Module slug in snake_case.
     */
    private function directoryToSlug(string $module): string
    {
        // Insert underscore before capital letters (HelloWorldProject -> Hello_World_Project)
        $slug = preg_replace('/(?<!^)(?=[A-Z])/', '_', $module);
        // Convert to lowercase (Hello_World_Project -> hello_world_project)
        return strtolower($slug) ?? '';
    }

    /**
    * Evaluate whether an actor is allowed to view a widget.
    *
    * Current behavior allows all discovered widgets and is reserved for RBAC expansion.
     *
    * @param int $actorId Authenticated user identifier.
    * @param string $moduleSlug Module slug, for example 'hello_world_programme'.
    * @param string $scopeType Scope type, either 'programme' or 'project'.
    * @return bool True when widget should be visible to the actor.
     */
    private function canAccessWidget(int $actorId, string $moduleSlug, string $scopeType): bool
    {
        // Delegate to RBAC if needed; for now, allow access
        return true;
    }
}
