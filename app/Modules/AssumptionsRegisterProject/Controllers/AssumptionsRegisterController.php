<?php

namespace App\Modules\AssumptionsRegisterProject\Controllers;

use App\Modules\RaidShared\Controllers\BaseProjectRaidController;

class AssumptionsRegisterController extends BaseProjectRaidController
{
    protected function moduleSlug(): string
    {
        return 'assumptions_register_project';
    }

    protected function moduleRouteSegment(): string
    {
        return 'assumptions-register';
    }

    protected function moduleTitleLangKey(): string
    {
        return 'Module.assumptionsRegisterTitle';
    }

    protected function moduleDescriptionLangKey(): string
    {
        return 'Module.assumptionsRegisterDescription';
    }
}
