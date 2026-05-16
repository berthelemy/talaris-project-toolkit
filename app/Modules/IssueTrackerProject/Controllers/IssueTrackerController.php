<?php

namespace App\Modules\IssueTrackerProject\Controllers;

use App\Modules\RaidShared\Controllers\BaseProjectRaidController;

/**
 * Project issue tracker controller.
 */
class IssueTrackerController extends BaseProjectRaidController
{
    /**
     * @return string
     */
    protected function moduleSlug(): string
    {
        return 'issue_tracker_project';
    }

    /**
     * @return string
     */
    protected function moduleRouteSegment(): string
    {
        return 'issue-tracker';
    }

    /**
     * @return string
     */
    protected function moduleTitleLangKey(): string
    {
        return 'Module.issueTrackerTitle';
    }

    /**
     * @return string
     */
    protected function moduleDescriptionLangKey(): string
    {
        return 'Module.issueTrackerDescription';
    }
}