<?php

namespace App\Libraries\Modules;

use App\Libraries\Auth\AuditLogger;
use App\Libraries\Auth\RbacService;
use App\Libraries\Modules\ModuleApiAuthorizationService;
use App\Models\ModuleWidgetFailureModel;
use App\Models\ModuleWidgetMetricModel;

/**
 * Service for managing and rendering module widgets on Programme/Project pages.
 */
class ModuleWidgetService
{
    private const WIDGET_DATA_CACHE_TTL = 300;
    private const WIDGET_HTML_CACHE_TTL = 180;

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
            $moduleSlug = (string) ($module['slug'] ?? '');
            if ($moduleSlug === '') {
                continue;
            }

            try {
                $widget = $this->loadModuleWidget($moduleSlug);

                if ($widget === null) {
                    continue;
                }

                $this->incrementMetric($moduleSlug, $scopeType, $scopeId, 'loaded_count');

                if (! $this->canAccessWidget($actorId, $module, $scopeId)) {
                    (new AuditLogger())->log('module_widget_access_denied', 'failed', $actorId, [
                        'module_slug' => $moduleSlug,
                        'scope_type' => $scopeType,
                        'scope_id' => $scopeId,
                    ]);
                    continue;
                }

                $widgetView = $widget->getWidgetView($scopeId);

                if ($widgetView !== null) {
                    $cacheKey = 'widgets_' . $scopeType . '_' . $scopeId . '_' . $moduleSlug;
                    /** @var array<string, mixed>|null $cached */
                    $cached = cache($cacheKey);

                    if (! is_array($cached)) {
                        $resolvedConfig = $this->resolveWidgetConfig($module, $widget);
                        $cached = $widget->getWidgetData($scopeId, $resolvedConfig);
                        cache()->save($cacheKey, $cached, self::WIDGET_DATA_CACHE_TTL);
                    }

                    $widgets[$moduleSlug] = [
                        'widget' => $widget,
                        'data' => $cached,
                        'view' => $widgetView,
                    ];
                }
            } catch (\Throwable $e) {
                // Log widget loading errors but don't break page rendering
                log_message('warning', "Failed to load widget for module {$moduleSlug}: {$e->getMessage()}");
                $this->incrementMetric($moduleSlug, $scopeType, $scopeId, 'error_count');
                $this->recordFailure($moduleSlug, $scopeType, $scopeId, $actorId, 'load', $e);
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
        $htmlCacheKey = 'widgets_html_' . $scopeType . '_' . $scopeId;
        $cachedHtml = cache($htmlCacheKey);

        if (is_string($cachedHtml) && $cachedHtml !== '') {
            return $cachedHtml;
        }

        $widgets = $this->getAvailableWidgets($scopeType, $scopeId);
        $html = '';
        $failureCount = 0;

        foreach ($widgets as $moduleSlug => $widget) {
            try {
                $start = microtime(true);
                $html .= view($widget['view'], array_merge($widget['data'], [
                    'scope_id' => $scopeId,
                    'scope_type' => $scopeType,
                    'module_slug' => $moduleSlug,
                ]));
                $this->incrementMetric($moduleSlug, $scopeType, $scopeId, 'rendered_count');
                $this->touchMetricLastRenderedAt($moduleSlug, $scopeType, $scopeId, $start);
            } catch (\Throwable $e) {
                log_message('warning', "Failed to render widget for module {$moduleSlug}: {$e->getMessage()}");
                $this->incrementMetric($moduleSlug, $scopeType, $scopeId, 'error_count');
                $this->recordFailure($moduleSlug, $scopeType, $scopeId, (int) (session('user_id') ?? 0), 'render', $e);
                $failureCount++;
            }
        }

        if ($failureCount > 0 && ENVIRONMENT === 'development') {
            $html = '<div class="alert alert-warning" role="alert">'
                . htmlspecialchars((string) lang('Module.widgetFailureDevWarning'), ENT_QUOTES, 'UTF-8')
                . '</div>' . $html;
        }

        cache()->save($htmlCacheKey, $html, self::WIDGET_HTML_CACHE_TTL);

        return $html;
    }

    /**
     * @param array<string, mixed> $module
     */
    public function canAccessModuleForActor(int $actorId, array $module, int $scopeId): bool
    {
        return $this->canAccessWidget($actorId, $module, $scopeId);
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
    public function directoryToSlug(string $module): string
    {
        $normalized = preg_replace('/[^A-Za-z0-9]+/', '_', $module) ?? '';
        $withWordBoundaries = preg_replace('/(?<=[a-z0-9])(?=[A-Z])/', '_', $normalized) ?? '';
        $withAcronymBoundaries = preg_replace('/(?<=[A-Z])(?=[A-Z][a-z])/', '_', $withWordBoundaries) ?? '';
        $withNumberBoundaries = preg_replace('/(?<=[A-Za-z])(?=[0-9])/', '_', $withAcronymBoundaries) ?? '';
        $slug = strtolower(trim($withNumberBoundaries, '_'));

        return preg_replace('/_{2,}/', '_', $slug) ?? '';
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
    private function canAccessWidget(int $actorId, array $module, int $scopeId): bool
    {
        $scopeType = (string) ($module['scope_type'] ?? '');
        $moduleSlug = (string) ($module['slug'] ?? '');

        if ($scopeType === '' || $moduleSlug === '') {
            return false;
        }

        $permission = (string) ($module['widget_permission'] ?? '');
        if ($permission === '') {
            return true;
        }

        if ((new RbacService())->hasPermission($actorId, $permission, $scopeType, $scopeType === 'system' ? null : $scopeId)) {
            return true;
        }

        if ($scopeType === 'system') {
            return false;
        }

        return (new ModuleApiAuthorizationService())->canRead($actorId, $scopeType, $scopeId);
    }

    /**
     * @param array<string, mixed> $module
     * @return array<string, mixed>
     */
    private function resolveWidgetConfig(array $module, ModuleWidgetInterface $widget): array
    {
        $defaultConfig = $widget->getDefaultConfig();
        $configured = json_decode((string) ($module['widget_config_json'] ?? ''), true);

        if (! is_array($configured)) {
            $configured = [];
        }

        return array_merge($defaultConfig, $configured);
    }

    private function incrementMetric(string $moduleSlug, string $scopeType, int $scopeId, string $field): void
    {
        $model = new ModuleWidgetMetricModel();
        $row = $model
            ->where('module_slug', $moduleSlug)
            ->where('scope_type', $scopeType)
            ->where('scope_id', $scopeId)
            ->first();

        if (! is_array($row)) {
            $model->insert([
                'module_slug' => $moduleSlug,
                'scope_type' => $scopeType,
                'scope_id' => $scopeId,
                'loaded_count' => $field === 'loaded_count' ? 1 : 0,
                'rendered_count' => $field === 'rendered_count' ? 1 : 0,
                'error_count' => $field === 'error_count' ? 1 : 0,
            ]);

            return;
        }

        $value = (int) ($row[$field] ?? 0);
        $model->update((int) $row['id'], [
            $field => $value + 1,
        ]);
    }

    private function touchMetricLastRenderedAt(string $moduleSlug, string $scopeType, int $scopeId, float $start): void
    {
        $model = new ModuleWidgetMetricModel();
        $row = $model
            ->where('module_slug', $moduleSlug)
            ->where('scope_type', $scopeType)
            ->where('scope_id', $scopeId)
            ->first();

        if (! is_array($row)) {
            return;
        }

        $model->update((int) $row['id'], [
            'last_rendered_at' => date('Y-m-d H:i:s', (int) $start),
        ]);
    }

    private function recordFailure(string $moduleSlug, string $scopeType, int $scopeId, int $actorId, string $phase, \Throwable $error): void
    {
        (new ModuleWidgetFailureModel())->insert([
            'module_slug' => $moduleSlug,
            'scope_type' => $scopeType,
            'scope_id' => $scopeId,
            'user_id' => $actorId > 0 ? $actorId : null,
            'phase' => $phase,
            'error_message' => $error->getMessage(),
            'trace' => ENVIRONMENT === 'development' ? $error->getTraceAsString() : null,
        ]);
    }
}
