<?php

namespace App\Modules\DependenciesRegisterProject\Controllers;

use App\Modules\RaidShared\Controllers\BaseProjectRaidController;

class DependenciesRegisterController extends BaseProjectRaidController
{
    protected function moduleSlug(): string
    {
        return 'dependencies_register_project';
    }

    protected function moduleRouteSegment(): string
    {
        return 'dependencies-register';
    }

    protected function moduleTitleLangKey(): string
    {
        return 'Module.dependenciesRegisterTitle';
    }

    protected function moduleDescriptionLangKey(): string
    {
        return 'Module.dependenciesRegisterDescription';
    }
}
