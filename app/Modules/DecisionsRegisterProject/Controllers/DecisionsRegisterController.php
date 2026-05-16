<?php

namespace App\Modules\DecisionsRegisterProject\Controllers;


/**
 * Project decisions register controller.
 */
class DecisionsRegisterController extends BaseProjectRaidController
{
    /**
     * @return string
     */
    protected function moduleSlug(): string
    {
        return 'decisions_register_project';
    }

    /**
     * @return string
     */
    protected function moduleRouteSegment(): string
    {
        return 'decisions-register';
    }

    /**
     * @return string
     */
    protected function moduleTitleLangKey(): string
    {
        return 'Module.decisionsRegisterTitle';
    }

    /**
     * @return string
     */
    protected function moduleDescriptionLangKey(): string
    {
        return 'Module.decisionsRegisterDescription';
    }
}
