<?php

namespace App\Modules\IssueTrackerProject\Controllers;

use App\Modules\RaidShared\Controllers\BaseProjectRaidController;

class IssueTrackerController extends BaseProjectRaidController
{
    protected function moduleSlug(): string
    {
        return 'issue_tracker_project';
    }

    protected function moduleRouteSegment(): string
    {
        return 'issue-tracker';
    }

    protected function moduleTitleLangKey(): string
    {
        return 'Module.issueTrackerTitle';
    }

    protected function moduleDescriptionLangKey(): string
    {
        return 'Module.issueTrackerDescription';
    }
}