<?php

/**
 * AssumptionsRegisterProject module controller: AssumptionsRegisterController.
 */

namespace App\Modules\AssumptionsRegisterProject\Controllers;


/**
 * Project assumptions register controller.
 */
class AssumptionsRegisterController extends BaseProjectRaidController
{
    /**
     * @return string
     */
    protected function moduleSlug(): string
    {
        return 'assumptions_register_project';
    }

    /**
     * @return string
     */
    protected function moduleRouteSegment(): string
    {
        return 'assumptions-register';
    }

    /**
     * @return string
     */
    protected function moduleTitleLangKey(): string
    {
        return 'Module.assumptionsRegisterTitle';
    }

    /**
     * @return string
     */
    protected function moduleDescriptionLangKey(): string
    {
        return 'Module.assumptionsRegisterDescription';
    }
}
