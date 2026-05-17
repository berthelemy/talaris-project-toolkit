<?php

/**
 * File documentation for app/Database/Migrations/2026-05-11-081000_RegisterPhase9RaidModules.php.
 */

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RegisterPhase9RaidModules extends Migration
{
    public function up(): void
    {
        $now = date('Y-m-d H:i:s');

        $this->upsertModule([
            'slug' => 'risk_register_project',
            'name' => 'Risk Register (Project)',
            'scope_type' => 'project',
            'description' => 'Production module for project risk governance.',
            'display_order' => 20,
            'is_enabled' => 1,
            'version' => '1.0.0',
            'dependencies_json' => json_encode([], JSON_THROW_ON_ERROR),
            'widget_permission' => 'module.risk_register_project.widget.read',
            'widget_config_json' => json_encode(['max_entries' => 5], JSON_THROW_ON_ERROR),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->upsertModule([
            'slug' => 'issue_tracker_project',
            'name' => 'Issue Tracker (Project)',
            'scope_type' => 'project',
            'description' => 'Production module for project issue governance.',
            'display_order' => 30,
            'is_enabled' => 1,
            'version' => '1.0.0',
            'dependencies_json' => json_encode([], JSON_THROW_ON_ERROR),
            'widget_permission' => 'module.issue_tracker_project.widget.read',
            'widget_config_json' => json_encode(['max_entries' => 5], JSON_THROW_ON_ERROR),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->upsertModule([
            'slug' => 'assumptions_register_project',
            'name' => 'Assumptions Register (Project)',
            'scope_type' => 'project',
            'description' => 'Production module for tracking project assumptions and review cadence.',
            'display_order' => 40,
            'is_enabled' => 1,
            'version' => '1.0.0',
            'dependencies_json' => json_encode([], JSON_THROW_ON_ERROR),
            'widget_permission' => 'module.assumptions_register_project.widget.read',
            'widget_config_json' => json_encode(['max_entries' => 5], JSON_THROW_ON_ERROR),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->upsertModule([
            'slug' => 'dependencies_register_project',
            'name' => 'Dependencies Register (Project)',
            'scope_type' => 'project',
            'description' => 'Production module for tracking project dependencies and impacts.',
            'display_order' => 50,
            'is_enabled' => 1,
            'version' => '1.0.0',
            'dependencies_json' => json_encode([], JSON_THROW_ON_ERROR),
            'widget_permission' => 'module.dependencies_register_project.widget.read',
            'widget_config_json' => json_encode(['max_entries' => 5], JSON_THROW_ON_ERROR),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        $this->db->table('module_registry')
            ->whereIn('slug', [
                'risk_register_project',
                'issue_tracker_project',
                'assumptions_register_project',
                'dependencies_register_project',
            ])
            ->delete();
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function upsertModule(array $payload): void
    {
        $existing = $this->db->table('module_registry')
            ->where('slug', (string) $payload['slug'])
            ->get()
            ->getRowArray();

        if (is_array($existing)) {
            unset($payload['created_at']);
            $this->db->table('module_registry')
                ->where('id', (int) $existing['id'])
                ->update($payload);

            return;
        }

        $this->db->table('module_registry')->insert($payload);
    }
}
