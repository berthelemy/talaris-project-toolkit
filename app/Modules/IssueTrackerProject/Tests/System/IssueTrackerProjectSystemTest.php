<?php

/**
 * File documentation for app/Modules/IssueTrackerProject/Tests/System/IssueTrackerProjectSystemTest.php.
 */

namespace App\Modules\IssueTrackerProject\Tests\System;

use App\Libraries\Auth\RbacService;
use App\Models\RaidEntryStorageModel;
use App\Modules\TestSupport\Testing\ModuleSystemTestCase;

/**
 * Issue Tracker module system coverage.
 *
 * @internal
 */
final class IssueTrackerProjectSystemTest extends ModuleSystemTestCase
{
    public function testTeamMemberHasReadOnlyAccessAndCannotCreateIssueEntry(): void
    {
        $owner = $this->createUser('issue_owner', 'issue_owner@example.com');
        $member = $this->createUser('issue_member', 'issue_member@example.com');
        $projectId = $this->createProject((int) $owner['id'], 'Issue Module Project 1');

        (new RbacService())->assignRoleToUser((int) $member['id'], 'team_member', 'project', $projectId, (int) $owner['id']);

        $page = $this->withSession($this->authSession($member))
            ->get('/projects/' . $projectId . '/modules/issue-tracker');

        $page->assertOK();
        $this->assertStringContainsString(strtolower((string) lang('Module.readOnlyNotice')), strtolower($page->getBody()));

        $post = $this->withSession($this->authSession($member))
            ->withBodyFormat('form')
            ->post('/projects/' . $projectId . '/modules/issue-tracker', [
                'title' => 'Blocked by dependency',
                'description' => 'Cannot proceed',
                'owner_user_id' => (int) $member['id'],
                'status' => 'open',
                'priority' => 'medium',
                'target_date' => '2026-06-01',
                'review_date' => '2026-05-21',
            ]);

        $post->assertRedirectTo('/projects/' . $projectId . '/modules/issue-tracker');

        $entry = (new RaidEntryStorageModel())
            ->where('module_slug', 'issue_tracker_project')
            ->where('scope_id', $projectId)
            ->first();

        $this->assertNull($entry);
    }

    public function testIssueModuleFilterAndSortWorkForOperationalUsage(): void
    {
        $manager = $this->createUser('issue_filter_user', 'issue_filter_user@example.com');
        $projectId = $this->createProject((int) $manager['id'], 'Issue Module Project 2');

        (new RbacService())->assignRoleToUser((int) $manager['id'], 'project_manager', 'project', $projectId, (int) $manager['id']);

        (new RaidEntryStorageModel())->insert([
            'module_slug' => 'issue_tracker_project',
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'title' => 'Open incident',
            'description' => 'Needs response',
            'owner_user_id' => (int) $manager['id'],
            'status' => 'open',
            'priority' => 'high',
            'target_date' => '2026-05-15',
            'review_date' => '2026-05-14',
            'created_by_user_id' => (int) $manager['id'],
            'updated_by_user_id' => (int) $manager['id'],
        ]);

        (new RaidEntryStorageModel())->insert([
            'module_slug' => 'issue_tracker_project',
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'title' => 'Closed incident',
            'description' => 'Completed',
            'owner_user_id' => (int) $manager['id'],
            'status' => 'closed',
            'priority' => 'low',
            'target_date' => '2026-05-10',
            'review_date' => '2026-05-10',
            'closed_at' => date('Y-m-d H:i:s'),
            'created_by_user_id' => (int) $manager['id'],
            'updated_by_user_id' => (int) $manager['id'],
        ]);

        $result = $this->withSession($this->authSession($manager))
            ->get('/projects/' . $projectId . '/modules/issue-tracker?status=open&q=Open&sort=priority_desc');

        $result->assertOK();
        $body = $result->getBody();

        $this->assertStringContainsString('Open incident', $body);
        $this->assertStringNotContainsString('Closed incident', $body);
    }

    public function testIssueModuleSupportsExtendedFieldsLifecycleAndDelete(): void
    {
        $manager = $this->createUser('issue_ext_user', 'issue_ext_user@example.com');
        $reporter = $this->createUser('issue_reporter_user', 'issue_reporter_user@example.com');
        $projectId = $this->createProject((int) $manager['id'], 'Issue Module Project 3');

        (new RbacService())->assignRoleToUser((int) $manager['id'], 'project_manager', 'project', $projectId, (int) $manager['id']);

        $create = $this->withSession($this->authSession($manager))
            ->withBodyFormat('form')
            ->post('/projects/' . $projectId . '/modules/issue-tracker', [
                'title' => 'Critical integration issue',
                'description' => 'Issue needs urgent resolution.',
                'owner_user_id' => (int) $manager['id'],
                'status' => 'open',
                'priority' => 'high',
                'date_reported' => '2026-05-16',
                'reporter_user_id' => (int) $reporter['id'],
                'impact_level' => 'high',
                'mitigation_actions' => 'Run hotfix and regression checks.',
                'lessons_learned' => 'Track incident timeline centrally.',
                'target_date' => '2026-05-20',
                'review_date' => '2026-05-18',
            ]);

        $create->assertRedirectTo('/projects/' . $projectId . '/modules/issue-tracker');

        $entry = (new RaidEntryStorageModel())
            ->where('module_slug', 'issue_tracker_project')
            ->where('scope_id', $projectId)
            ->first();

        $this->assertIsArray($entry);
        $this->assertSame('high', (string) ($entry['impact_level'] ?? ''));
        $this->assertSame((int) $reporter['id'], (int) ($entry['reporter_user_id'] ?? 0));

        $entryId = (int) ($entry['id'] ?? 0);

        $update = $this->withSession($this->authSession($manager))
            ->withBodyFormat('form')
            ->post('/projects/' . $projectId . '/modules/issue-tracker/' . $entryId . '/update', [
                'title' => 'Critical integration issue',
                'description' => 'Issue resolved after fix deployment.',
                'owner_user_id' => (int) $manager['id'],
                'status' => 'resolved',
                'priority' => 'medium',
                'date_reported' => '2026-05-16',
                'reporter_user_id' => (int) $reporter['id'],
                'impact_level' => 'medium',
                'mitigation_actions' => 'Fix applied and monitored.',
                'lessons_learned' => 'Add pre-release integration checklist.',
                'target_date' => '2026-05-20',
                'review_date' => '2026-05-19',
            ]);

        $update->assertRedirectTo('/projects/' . $projectId . '/modules/issue-tracker');

        $updated = (new RaidEntryStorageModel())->find($entryId);
        $this->assertIsArray($updated);
        $this->assertSame('resolved', (string) ($updated['status'] ?? ''));
        $this->assertSame('medium', (string) ($updated['impact_level'] ?? ''));

        $delete = $this->withSession($this->authSession($manager))
            ->withBodyFormat('form')
            ->post('/projects/' . $projectId . '/modules/issue-tracker/' . $entryId . '/delete');

        $delete->assertRedirectTo('/projects/' . $projectId . '/modules/issue-tracker');
        $this->assertNull((new RaidEntryStorageModel())->find($entryId));
    }

    public function testIssueAndTasksWidgetsExposeCardsAndActions(): void
    {
        $manager = $this->createUser('issue_task_widgets_user', 'issue_task_widgets_user@example.com');
        $projectId = $this->createProject((int) $manager['id'], 'Issue Module Project 4');

        (new RbacService())->assignRoleToUser((int) $manager['id'], 'project_manager', 'project', $projectId, (int) $manager['id']);

        (new RaidEntryStorageModel())->insert([
            'module_slug' => 'issue_tracker_project',
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'title' => 'Issue widget entry',
            'description' => 'Issue to display widgets',
            'owner_user_id' => (int) $manager['id'],
            'status' => 'open',
            'priority' => 'high',
            'target_date' => '2026-05-01',
            'created_by_user_id' => (int) $manager['id'],
            'updated_by_user_id' => (int) $manager['id'],
        ]);

        (new RaidEntryStorageModel())->insert([
            'module_slug' => 'tasks_register_project',
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'title' => 'Task widget entry',
            'description' => 'Task to display widgets',
            'owner_user_id' => (int) $manager['id'],
            'status' => 'open',
            'priority' => 'medium',
            'due_date' => '2026-05-01',
            'created_by_user_id' => (int) $manager['id'],
            'updated_by_user_id' => (int) $manager['id'],
        ]);

        $overview = $this->withSession($this->authSession($manager))
            ->get('/projects/' . $projectId);

        $overview->assertOK();
        $body = $overview->getBody();

        $this->assertStringContainsString((string) lang('Module.issuesWidgetOverviewTitle'), $body);
        $this->assertStringContainsString((string) lang('Module.issuesWidgetHighPriorityTitle'), $body);
        $this->assertStringContainsString((string) lang('Module.issuesWidgetOverdueTitle'), $body);
        $this->assertStringContainsString((string) lang('Module.tasksWidgetOverviewTitle'), $body);
        $this->assertStringContainsString((string) lang('Module.tasksWidgetMyOpenTitle'), $body);
        $this->assertStringContainsString((string) lang('Module.tasksWidgetOverdueTitle'), $body);
        $this->assertStringContainsString('/projects/' . $projectId . '/modules/issue-tracker', $body);
        $this->assertStringContainsString('/projects/' . $projectId . '/modules/tasks-register', $body);
        $this->assertStringContainsString('data-bs-target="#issueModalAdd"', $body);
        $this->assertStringContainsString('data-bs-target="#taskModalAdd"', $body);
    }
}
