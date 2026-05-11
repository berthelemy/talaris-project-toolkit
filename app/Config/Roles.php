<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Predefined RBAC role-to-permission mappings.
 */
class Roles extends BaseConfig
{
    /**
     * @var list<array{slug: string, name: string, description: string}>
     */
    public array $predefinedRoles = [
        [
            'slug' => 'administrator',
            'name' => 'Administrator',
            'description' => 'Global administrator with full toolkit governance capabilities.',
        ],
        [
            'slug' => 'programme_manager',
            'name' => 'Programme manager',
            'description' => 'Manages programmes and programme-level project composition.',
        ],
        [
            'slug' => 'project_manager',
            'name' => 'Project manager',
            'description' => 'Manages owned projects and their editable project data.',
        ],
        [
            'slug' => 'team_member',
            'name' => 'Team member',
            'description' => 'Reads information within assigned project contexts.',
        ],
        [
            'slug' => 'stakeholder',
            'name' => 'Stakeholder',
            'description' => 'Reads reports that are shared with stakeholders.',
        ],
    ];

    /**
     * @var array<string, list<string>>
     */
    public array $predefinedPermissions = [
        'administrator' => [
            'system.users.invite',
            'system.roles.manage',
            'system.theme.manage',
            'system.modules.manage',
            'system.modules.add',
            'system.users.impersonate',
            'module.hello_world_programme.widget.read',
            'module.hello_world_project.widget.read',
            'module.risk_register_project.widget.read',
            'module.issue_tracker_project.widget.read',
            'module.assumptions_register_project.widget.read',
            'module.dependencies_register_project.widget.read',
        ],
        'programme_manager' => [
            'programme.create',
            'programme.read_own',
            'programme.update_own',
            'programme.delete_own',
            'programme.projects.attach',
            'module.hello_world_programme.widget.read',
        ],
        'project_manager' => [
            'project.create',
            'project.read_own',
            'project.update_own',
            'project.delete_own',
            'project.content.update',
            'module.hello_world_project.widget.read',
            'module.risk_register_project.widget.read',
            'module.issue_tracker_project.widget.read',
            'module.assumptions_register_project.widget.read',
            'module.dependencies_register_project.widget.read',
        ],
        'team_member' => [
            'project.read',
            'module.hello_world_project.widget.read',
            'module.risk_register_project.widget.read',
            'module.issue_tracker_project.widget.read',
            'module.assumptions_register_project.widget.read',
            'module.dependencies_register_project.widget.read',
        ],
        'stakeholder' => [
            'reports.read_stakeholder',
        ],
    ];
}