<?php

namespace App\Modules\DecisionsRegisterProject\Controllers;

use App\Modules\RaidShared\Controllers\BaseProjectRaidController;

class DecisionsRegisterController extends BaseProjectRaidController
{
    protected function moduleSlug(): string
    {
        return 'decisions_register_project';
    }

    protected function moduleRouteSegment(): string
    {
        return 'decisions-register';
    }

    protected function moduleTitleLangKey(): string
    {
        return 'Module.decisionsRegisterTitle';
    }

    protected function moduleDescriptionLangKey(): string
    {
        return 'Module.decisionsRegisterDescription';
    }
}
