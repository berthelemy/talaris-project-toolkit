<?php

namespace App\Modules\RiskRegisterProject\Controllers;

use App\Modules\RaidShared\Controllers\BaseProjectRaidController;

/**
 * Project risk register controller.
 */
class RiskRegisterController extends BaseProjectRaidController
{
    /**
     * @return string
     */
    protected function moduleSlug(): string
    {
        return 'risk_register_project';
    }

    /**
     * @return string
     */
    protected function moduleRouteSegment(): string
    {
        return 'risk-register';
    }

    /**
     * @return string
     */
    protected function moduleTitleLangKey(): string
    {
        return 'Module.riskRegisterTitle';
    }

    /**
     * @return string
     */
    protected function moduleDescriptionLangKey(): string
    {
        return 'Module.riskRegisterDescription';
    }
}
