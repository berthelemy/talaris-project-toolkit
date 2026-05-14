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
    private ModuleWidgetLayoutService $layoutService;

    /**
    * Build widget service with module registry dependency.
     */
    public function __construct()
    {
        $this->registryService = new ModuleRegistryService();
        $this->layoutService = new ModuleWidgetLayoutService();
    }

    /**
    * Discover enabled and accessible widgets for a scope record.
     *
    * @param string $scopeType Scope type, either 'programme' or 'project'.
    * @param int $scopeId Programme or project identifier.
    * @return array<string, array{widget: ModuleWidgetInterface, data: array<string, mixed>, view: string, module_slug: string}> Widget definitions keyed by widget key.
     */
    public function getAvailableWidgets(string $scopeType, int $scopeId): array
    {
        $widgets = [];
        $actorId = (int) (session('user_id') ?? 0);

        if ($actorId === 0) {
            return $widgets;
        }

        $orderedWidgets = $this->discoverWidgets($scopeType, $scopeId, $actorId, true);

        foreach ($orderedWidgets as $entry) {
            $widgetKey = (string) ($entry['widget_key'] ?? '');
            if ($widgetKey === '') {
                continue;
            }

            $widgets[$widgetKey] = [
                'widget' => $entry['widget'],
                'data' => (array) ($entry['data'] ?? []),
                'view' => (string) ($entry['view'] ?? ''),
                'module_slug' => (string) ($entry['module_slug'] ?? ''),
            ];
        }

        return $widgets;
    }

    /**
     * Build Manage Widgets options for the given actor and scope.
     *
     * @return list<array{widget_key: string, module_slug: string, name: string, is_visible: bool, display_order: int}>
     */
    public function getWidgetLayoutOptions(string $scopeType, int $scopeId, int $actorId): array
    {
        $discovered = $this->discoverWidgets($scopeType, $scopeId, $actorId, false);
        $options = [];

        foreach ($discovered as $entry) {
            $widgetKey = (string) ($entry['widget_key'] ?? '');
            if ($widgetKey === '') {
                continue;
            }

            $options[] = [
                'widget_key' => $widgetKey,
                'module_slug' => (string) ($entry['module_slug'] ?? ''),
                'name' => (string) ($entry['name'] ?? $widgetKey),
                'is_visible' => (bool) ($entry['is_visible'] ?? true),
                'display_order' => (int) ($entry['display_order'] ?? 0),
            ];
        }

        return $options;
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

        foreach ($widgets as $widgetKey => $widget) {
            try {
                $start = microtime(true);
                $widgetHtml = view($widget['view'], array_merge($widget['data'], [
                    'scope_id' => $scopeId,
                    'scope_type' => $scopeType,
                    'module_slug' => (string) ($widget['module_slug'] ?? ''),
                    'widget_key' => $widgetKey,
                ]));
                $html .= '<div class="col-12 col-md-6 col-lg-4">' . $widgetHtml . '</div>';
                $moduleSlug = (string) ($widget['module_slug'] ?? '');
                if ($moduleSlug !== '') {
                    $this->incrementMetric($moduleSlug, $scopeType, $scopeId, 'rendered_count');
                    $this->touchMetricLastRenderedAt($moduleSlug, $scopeType, $scopeId, $start);
                }
            } catch (\Throwable $e) {
                $moduleSlug = (string) ($widget['module_slug'] ?? '');
                log_message('warning', "Failed to render widget for widget {$widgetKey}: {$e->getMessage()}");
                if ($moduleSlug !== '') {
                    $this->incrementMetric($moduleSlug, $scopeType, $scopeId, 'error_count');
                    $this->recordFailure($moduleSlug, $scopeType, $scopeId, (int) (session('user_id') ?? 0), 'render', $e);
                }
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
     * @return list<array{widget_key: string, module_slug: string, name: string, display_order: int, is_visible: bool, widget: ModuleWidgetInterface, data?: array<string, mixed>, view: string}>
     */
    private function discoverWidgets(string $scopeType, int $scopeId, int $actorId, bool $includeData): array
    {
        $modules = $this->registryService->getEnabledModulesByType($scopeType);
        $defaultPreferences = $this->layoutService->getDefaultByScope($scopeType);
        $scopedPreferences = $this->layoutService->getScoped($scopeType, $scopeId);

        $orderedWidgets = [];

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

                $resolvedConfig = $this->resolveWidgetConfig($module, $widget);
                $definitions = $this->resolveDefinitions(
                    $moduleSlug,
                    (string) ($module['name'] ?? $moduleSlug),
                    $scopeType,
                    $scopeId,
                    $widget,
                    $resolvedConfig,
                    $includeData,
                );

                foreach ($definitions as $definition) {
                    $widgetKey = (string) ($definition['widget_key'] ?? '');
                    if ($widgetKey === '') {
                        continue;
                    }

                    $layout = $this->layoutService->resolveForWidget($widgetKey, $module, $defaultPreferences, $scopedPreferences);
                    if ($includeData && ! $layout['is_visible']) {
                        continue;
                    }

                    $orderedWidgets[] = [
                        'widget_key' => $widgetKey,
                        'module_slug' => $moduleSlug,
                        'name' => (string) ($definition['name'] ?? $widgetKey),
                        'display_order' => $layout['display_order'],
                        'is_visible' => (bool) $layout['is_visible'],
                        'widget' => $widget,
                        'data' => (array) ($definition['data'] ?? []),
                        'view' => (string) ($definition['view'] ?? ''),
                    ];
                }
            } catch (\Throwable $e) {
                log_message('warning', "Failed to load widget for module {$moduleSlug}: {$e->getMessage()}");
                $this->incrementMetric($moduleSlug, $scopeType, $scopeId, 'error_count');
                $this->recordFailure($moduleSlug, $scopeType, $scopeId, $actorId, 'load', $e);
            }
        }

        usort($orderedWidgets, static function (array $a, array $b): int {
            $orderCompare = ((int) $a['display_order']) <=> ((int) $b['display_order']);
            if ($orderCompare !== 0) {
                return $orderCompare;
            }

            return strcasecmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
        });

        return $orderedWidgets;
    }

    /**
     * @param array<string, mixed> $config
     * @return list<array{widget_key: string, name: string, view: string, data: array<string, mixed>}>
     */
    private function resolveDefinitions(
        string $moduleSlug,
        string $moduleName,
        string $scopeType,
        int $scopeId,
        ModuleWidgetInterface $widget,
        array $config,
        bool $includeData,
    ): array {
        $definitions = [];

        if (method_exists($widget, 'getWidgetDefinitions')) {
            /** @var mixed $rawDefinitions */
            $rawDefinitions = $widget->getWidgetDefinitions($scopeId, $config);
            if (is_array($rawDefinitions)) {
                foreach ($rawDefinitions as $rawDefinition) {
                    if (! is_array($rawDefinition)) {
                        continue;
                    }

                    $localKey = trim((string) ($rawDefinition['key'] ?? ''));
                    $view = trim((string) ($rawDefinition['view'] ?? ''));
                    if ($localKey === '' || $view === '') {
                        continue;
                    }

                    $widgetKey = $this->buildWidgetKey($moduleSlug, $localKey);
                    $cacheKey = 'widgets_' . $scopeType . '_' . $scopeId . '_' . $widgetKey;
                    $data = [];

                    if ($includeData) {
                        /** @var array<string, mixed>|null $cached */
                        $cached = cache($cacheKey);
                        if (! is_array($cached)) {
                            $rawData = $rawDefinition['data'] ?? [];
                            $cached = is_array($rawData) ? $rawData : [];
                            cache()->save($cacheKey, $cached, self::WIDGET_DATA_CACHE_TTL);
                        }

                        $data = $cached;
                    }

                    $definitions[] = [
                        'widget_key' => $widgetKey,
                        'name' => trim((string) ($rawDefinition['name'] ?? $localKey)),
                        'view' => $view,
                        'data' => $data,
                    ];
                }
            }

            return $definitions;
        }

        $view = $widget->getWidgetView($scopeId);
        if ($view === null) {
            return [];
        }

        $cacheKey = 'widgets_' . $scopeType . '_' . $scopeId . '_' . $moduleSlug;
        $data = [];

        if ($includeData) {
            /** @var array<string, mixed>|null $cached */
            $cached = cache($cacheKey);
            if (! is_array($cached)) {
                $cached = $widget->getWidgetData($scopeId, $config);
                cache()->save($cacheKey, $cached, self::WIDGET_DATA_CACHE_TTL);
            }

            $data = $cached;
        }

        return [[
            'widget_key' => $moduleSlug,
            'name' => $moduleName,
            'view' => $view,
            'data' => $data,
        ]];
    }

    private function buildWidgetKey(string $moduleSlug, string $localKey): string
    {
        if ($localKey === $moduleSlug) {
            return $moduleSlug;
        }

        return $moduleSlug . '__' . $localKey;
    }

    /**
     * @param array<string, mixed> $module
     */
    public function canAccessModuleForActor(int $actorId, array $module, int $scopeId): bool
    {
        return $this->canAccessWidget($actorId, $module, $scopeId);
    }

    public function invalidateScopeCaches(string $scopeType, int $scopeId): void
    {
        $cache = cache();

        if (method_exists($cache, 'deleteMatching')) {
            $cache->deleteMatching('widgets_' . $scopeType . '_' . $scopeId . '_*');
            $cache->deleteMatching('widgets_html_' . $scopeType . '_' . $scopeId . '*');

            return;
        }

        $cache->clean();
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
