<?php

namespace App\Modules\DependenciesRegisterProject\Controllers;

use App\Modules\RaidShared\Controllers\BaseProjectRaidController;

/**
 * Project dependencies register controller.
 */
class DependenciesRegisterController extends BaseProjectRaidController
{
    /**
     * @return string
     */
    protected function moduleSlug(): string
    {
        return 'dependencies_register_project';
    }

    /**
     * @return string
     */
    protected function moduleRouteSegment(): string
    {
        return 'dependencies-register';
    }

    /**
     * @return string
     */
    protected function moduleTitleLangKey(): string
    {
        return 'Module.dependenciesRegisterTitle';
    }

    /**
     * @return string
     */
    protected function moduleDescriptionLangKey(): string
    {
        return 'Module.dependenciesRegisterDescription';
    }
}
