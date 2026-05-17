<?php

namespace App\Modules\TasksRegisterProject\Tests\System;

use App\Libraries\Auth\RbacService;
use App\Models\RaidEntryStorageModel;
use App\Modules\TestSupport\Testing\ModuleSystemTestCase;

/**
 * Tasks Register module system coverage.
 *
 * @internal
 */
final class TasksRegisterProjectSystemTest extends ModuleSystemTestCase
{
    public function testTasksModuleSupportsExtendedFieldsLifecycleAndDelete(): void
    {
        $manager = $this->createUser('tasks_ext_user', 'tasks_ext_user@example.com');
        $projectId = $this->createProject((int) $manager['id'], 'Tasks Module Project 1');

        (new RbacService())->assignRoleToUser((int) $manager['id'], 'project_manager', 'project', $projectId, (int) $manager['id']);

        $create = $this->withSession($this->authSession($manager))
            ->withBodyFormat('form')
            ->post('/projects/' . $projectId . '/modules/tasks-register', [
                'title' => 'Prepare release runbook',
                'description' => 'Compile release steps and rollback checks.',
                'owner_user_id' => (int) $manager['id'],
                'status' => 'in_progress',
                'priority' => 'high',
                'task_category' => 'Delivery',
                'related_objective' => 'Release readiness',
                'collaborators' => 'Ops, QA',
                'percent_complete' => 45,
                'planned_start_date' => '2026-05-10',
                'due_date' => '2026-05-25',
                'blocked_reason' => 'Waiting on security sign-off.',
                'next_action' => 'Schedule sign-off meeting.',
                'lessons_learned' => 'Checklist draft should start earlier.',
            ]);

        $create->assertRedirectTo('/projects/' . $projectId . '/modules/tasks-register');

        $entry = (new RaidEntryStorageModel())
            ->where('module_slug', 'tasks_register_project')
            ->where('scope_id', $projectId)
            ->first();

        $this->assertIsArray($entry);
        $this->assertSame('Delivery', (string) ($entry['task_category'] ?? ''));

        $entryId = (int) ($entry['id'] ?? 0);

        $update = $this->withSession($this->authSession($manager))
            ->withBodyFormat('form')
            ->post('/projects/' . $projectId . '/modules/tasks-register/' . $entryId . '/update', [
                'title' => 'Prepare release runbook',
                'description' => 'Runbook finalized.',
                'owner_user_id' => (int) $manager['id'],
                'status' => 'completed',
                'priority' => 'medium',
                'task_category' => 'Delivery',
                'related_objective' => 'Release readiness',
                'collaborators' => 'Ops, QA',
                'percent_complete' => 100,
                'planned_start_date' => '2026-05-10',
                'due_date' => '2026-05-25',
                'completed_date' => '2026-05-20',
                'blocked_reason' => '',
                'next_action' => 'Publish runbook to project space.',
                'lessons_learned' => 'Coordinate sign-off earlier in cycle.',
            ]);

        $update->assertRedirectTo('/projects/' . $projectId . '/modules/tasks-register');

        $updated = (new RaidEntryStorageModel())->find($entryId);
        $this->assertIsArray($updated);
        $this->assertSame('completed', (string) ($updated['status'] ?? ''));

        $delete = $this->withSession($this->authSession($manager))
            ->withBodyFormat('form')
            ->post('/projects/' . $projectId . '/modules/tasks-register/' . $entryId . '/delete');

        $delete->assertRedirectTo('/projects/' . $projectId . '/modules/tasks-register');
        $this->assertNull((new RaidEntryStorageModel())->find($entryId));
    }
}
