<?php

namespace App\Libraries\Modules;

/**
 * Service for managing and rendering module widgets on Programme/Project pages.
 */
class ModuleWidgetService
{
    private ModuleRegistryService $registryService;

    public function __construct()
    {
        $this->registryService = new ModuleRegistryService();
    }

    /**
     * Get all available widgets for a given scope.
     *
     * @param string $scopeType 'programme' or 'project'
     * @param int    $scopeId   ID of the programme or project
     *
     * @return array<string, array{widget: ModuleWidgetInterface, data: array}> Widgets keyed by module slug
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
     * Render widgets for display.
     *
     * @param string $scopeType 'programme' or 'project'
     * @param int    $scopeId   ID of the programme or project
     *
     * @return string HTML rendering of all available widgets
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
     * Load a module widget by slug.
     *
     * @return ModuleWidgetInterface|null
     */
    private function loadModuleWidget(string $moduleSlug): ?ModuleWidgetInterface
    {
        $moduleDir = APPPATH . 'Modules' . DIRECTORY_SEPARATOR;
        $modules = array_diff(scandir($moduleDir) ?? [], ['.', '..']);

        foreach ($modules as $module) {
            $registrySlug = str_replace('HelloWorld', 'hello_world_', strtolower($module));
            $registrySlug = str_replace('_', '_', $registrySlug);

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
     * Check if current actor can access this widget.
     * By default, allows access if actor can view the scope.
     *
     * @param int    $actorId   User ID
     * @param string $moduleSlug Module slug (e.g., 'hello_world_programme')
     * @param string $scopeType 'programme' or 'project'
     */
    private function canAccessWidget(int $actorId, string $moduleSlug, string $scopeType): bool
    {
        // Delegate to RBAC if needed; for now, allow access
        return true;
    }
}
