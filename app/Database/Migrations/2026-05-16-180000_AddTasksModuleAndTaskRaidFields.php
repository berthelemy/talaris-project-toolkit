<?php

/**
 * Database migration: Add Tasks Module And Task Raid Fields.
 */

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTasksModuleAndTaskRaidFields extends Migration
{
    public function up(): void
    {
        $columns = [
            'task_category' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'escalation_required',
            ],
            'related_objective' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'task_category',
            ],
            'related_module_entry_id' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'null' => true,
                'after' => 'related_objective',
            ],
            'collaborators' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'related_module_entry_id',
            ],
            'percent_complete' => [
                'type' => 'INT',
                'constraint' => 3,
                'null' => true,
                'after' => 'collaborators',
            ],
            'planned_start_date' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'percent_complete',
            ],
            'due_date' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'planned_start_date',
            ],
            'completed_date' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'due_date',
            ],
            'blocked_reason' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'completed_date',
            ],
            'next_action' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'blocked_reason',
            ],
        ];

        foreach ($columns as $name => $definition) {
            if (! $this->hasField('module_raid_entries', $name)) {
                $this->forge->addColumn('module_raid_entries', [$name => $definition]);
            }
        }

        $now = date('Y-m-d H:i:s');

        $this->upsertModule([
            'slug' => 'issue_tracker_project',
            'name' => 'Issue Tracker (Project)',
            'scope_type' => 'project',
            'description' => 'Production module for project issue governance.',
            'display_order' => 30,
            'is_enabled' => 1,
            'version' => '1.1.0',
            'dependencies_json' => json_encode([], JSON_THROW_ON_ERROR),
            'widget_permission' => 'module.issue_tracker_project.widget.read',
            'widget_config_json' => json_encode(['max_entries' => 5], JSON_THROW_ON_ERROR),
            'updated_at' => $now,
        ]);

        $this->upsertModule([
            'slug' => 'tasks_register_project',
            'name' => 'Tasks Register (Project)',
            'scope_type' => 'project',
            'description' => 'Production module for project task governance and delivery tracking.',
            'display_order' => 70,
            'is_enabled' => 1,
            'version' => '1.1.0',
            'dependencies_json' => json_encode([], JSON_THROW_ON_ERROR),
            'widget_permission' => 'module.tasks_register_project.widget.read',
            'widget_config_json' => json_encode(['max_entries' => 5], JSON_THROW_ON_ERROR),
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        $fields = [
            'task_category',
            'related_objective',
            'related_module_entry_id',
            'collaborators',
            'percent_complete',
            'planned_start_date',
            'due_date',
            'completed_date',
            'blocked_reason',
            'next_action',
        ];

        foreach ($fields as $field) {
            if ($this->hasField('module_raid_entries', $field)) {
                $this->forge->dropColumn('module_raid_entries', $field);
            }
        }

        $this->db->table('module_registry')
            ->where('slug', 'tasks_register_project')
            ->delete();
    }

    /**
     * @param array<string,mixed> $payload
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

        $payload['created_at'] = date('Y-m-d H:i:s');
        $this->db->table('module_registry')->insert($payload);
    }

    private function hasField(string $table, string $field): bool
    {
        $physicalTable = $this->db->DBPrefix . $table;

        if ($this->db->DBDriver === 'SQLite3') {
            $columns = $this->db->query('PRAGMA table_info(' . $physicalTable . ')')->getResultArray();
            foreach ($columns as $column) {
                if ((string) ($column['name'] ?? '') === $field) {
                    return true;
                }
            }

            return false;
        }

        $result = $this->db->query(
            'SELECT 1 FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name = ? LIMIT 1',
            [$this->db->database, $physicalTable, $field],
        )->getRowArray();

        return is_array($result);
    }
}
