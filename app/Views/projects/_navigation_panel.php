<?php
/**
 * @var array<string,mixed> $project
 * @var list<array{slug:string,name:string,url:string,route_segment?:string}>|null $projectModules
 * @var string|null $activeProjectNav
 * @var bool|null $canManageWidgetLayout
 */

$project = is_array($project ?? null) ? $project : [];
$projectId = (int) ($project['id'] ?? 0);
$projectName = (string) ($project['name'] ?? '');
$canManageWidgetLayout = $canManageWidgetLayout ?? null;
$activeProjectNav = trim((string) ($activeProjectNav ?? ''));

$normalizedModules = [];
$inputModules = $projectModules ?? null;

if (is_array($inputModules) && $inputModules !== []) {
    foreach ($inputModules as $module) {
        $moduleUrl = (string) ($module['url'] ?? '');
        if ($moduleUrl === '') {
            continue;
        }

        $path = (string) parse_url($moduleUrl, PHP_URL_PATH);
        $parts = array_values(array_filter(explode('/', trim($path, '/')), static fn (string $part): bool => $part !== ''));
        $routeSegment = (string) end($parts);

        $normalizedModules[] = [
            'slug' => (string) ($module['slug'] ?? ''),
            'name' => (string) ($module['name'] ?? ''),
            'url' => $moduleUrl,
            'route_segment' => $routeSegment,
        ];
    }
}

if ($normalizedModules === [] && $projectId > 0) {
    $actorId = session('user_id');
    $actorId = (is_int($actorId) || ctype_digit((string) $actorId)) ? (int) $actorId : 0;

    if ($actorId > 0) {
        $moduleRegistry = new \App\Libraries\Modules\ModuleRegistryService();
        $widgetService = new \App\Libraries\Modules\ModuleWidgetService();
        $modules = $moduleRegistry->getEnabledModulesByType('project');

        foreach ($modules as $module) {
            $slug = (string) ($module['slug'] ?? '');
            if ($slug === '') {
                continue;
            }

            if (! $widgetService->canAccessModuleForActor($actorId, $module, $projectId)) {
                continue;
            }

            $suffix = '_project';
            if (! str_ends_with($slug, $suffix)) {
                continue;
            }

            $base = substr($slug, 0, -strlen($suffix));
            if (! is_string($base) || $base === '') {
                continue;
            }

            $routeSegment = str_replace('_', '-', $base);

            $normalizedModules[] = [
                'slug' => $slug,
                'name' => (string) ($module['name'] ?? $slug),
                'url' => site_url('projects/' . $projectId . '/modules/' . $routeSegment),
                'route_segment' => $routeSegment,
                'display_order' => (int) ($module['display_order'] ?? 0),
            ];
        }

        usort($normalizedModules, static function (array $a, array $b): int {
            $orderCompare = ((int) ($a['display_order'] ?? 0)) <=> ((int) ($b['display_order'] ?? 0));
            if ($orderCompare !== 0) {
                return $orderCompare;
            }

            return strcasecmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
        });
    }
}

if ($canManageWidgetLayout === null && $projectId > 0) {
    $actorId = session('user_id');
    $actorId = (is_int($actorId) || ctype_digit((string) $actorId)) ? (int) $actorId : 0;

    if ($actorId > 0) {
        $rbac = new \App\Libraries\Auth\RbacService();
        $isOwner = (int) ($project['owner_user_id'] ?? 0) === $actorId;
        $canManageWidgetLayout = $isOwner
            || $rbac->hasPermission($actorId, 'project.update_own', 'project', $projectId)
            || $rbac->hasPermission($actorId, 'project.delete_own', 'project', $projectId)
            || $rbac->hasPermission($actorId, 'project.content.update', 'project', $projectId)
            || $rbac->hasPermission($actorId, 'system.users.impersonate', 'system', null);
    }
}

$canManageWidgetLayout = (bool) $canManageWidgetLayout;

if ($activeProjectNav === '') {
    $uri = service('uri');
    $currentRouteSegment = trim((string) $uri->getSegment(4));
    $activeProjectNav = $currentRouteSegment !== '' ? 'module:' . $currentRouteSegment : 'overview';
}
?>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h2 class="h6 mb-3"><?= esc($projectName) ?></h2>
        <nav aria-label="<?= esc(lang('Domain.projectModulesLabel')) ?>">
            <ul class="nav nav-pills flex-column gap-1">
                <li class="nav-item">
                    <a class="nav-link <?= $activeProjectNav === 'overview' ? 'active' : '' ?>" href="<?= site_url('projects/' . $projectId) ?>" <?= $activeProjectNav === 'overview' ? 'aria-current="page"' : '' ?>>
                        <?= esc(lang('Domain.overviewLabel')) ?>
                    </a>
                </li>
                <?php if ($canManageWidgetLayout): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $activeProjectNav === 'widget-layout' ? 'active' : '' ?>" href="<?= site_url('projects/' . $projectId . '/widgets/layout') ?>" <?= $activeProjectNav === 'widget-layout' ? 'aria-current="page"' : '' ?>>
                            <?= esc(lang('Module.projectLayoutManageWidgets')) ?>
                        </a>
                    </li>
                <?php endif; ?>
                <?php foreach ($normalizedModules as $module): ?>
                    <?php $isActiveModule = $activeProjectNav === 'module:' . (string) ($module['route_segment'] ?? ''); ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $isActiveModule ? 'active' : '' ?>" href="<?= esc((string) ($module['url'] ?? '#')) ?>" <?= $isActiveModule ? 'aria-current="page"' : '' ?>>
                            <?= esc((string) ($module['name'] ?? '')) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                <?php if ($normalizedModules === []): ?>
                    <li class="nav-item small text-muted px-2 py-1" role="status">
                        <?= esc(lang('Domain.projectModulesNone')) ?>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</div>
