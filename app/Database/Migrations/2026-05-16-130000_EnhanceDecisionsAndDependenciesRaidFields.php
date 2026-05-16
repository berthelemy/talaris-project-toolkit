<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnhanceDecisionsAndDependenciesRaidFields extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('module_raid_entries', [
            'decision_category' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'made_by_user_id',
            ],
            'decision_rationale' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'decision_category',
            ],
            'alternatives_considered' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'decision_rationale',
            ],
            'chosen_option' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'alternatives_considered',
            ],
            'approver_user_id' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'null' => true,
                'after' => 'chosen_option',
            ],
            'implementation_actions' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'approver_user_id',
            ],
            'superseded_by_entry_id' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'null' => true,
                'after' => 'implementation_actions',
            ],
            'dependency_type' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
                'null' => true,
                'after' => 'superseded_by_entry_id',
            ],
            'related_work_package' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'dependency_type',
            ],
            'depends_on' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'related_work_package',
            ],
            'escalation_required' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'depends_on',
            ],
        ]);

        $this->forge->addKey('decision_category');
        $this->forge->addKey('approver_user_id');
        $this->forge->addKey('superseded_by_entry_id');
        $this->forge->addKey('dependency_type');
        $this->forge->addKey('escalation_required');
        $this->forge->addForeignKey('approver_user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('superseded_by_entry_id', 'module_raid_entries', 'id', 'SET NULL', 'CASCADE');

        $now = date('Y-m-d H:i:s');

        $this->upsertModule([
            'slug' => 'decisions_register_project',
            'name' => 'Decisions Register (Project)',
            'scope_type' => 'project',
            'description' => 'Production module for key decision capture, implementation, and supersession traceability.',
            'display_order' => 60,
            'is_enabled' => 1,
            'version' => '1.1.0',
            'dependencies_json' => json_encode([], JSON_THROW_ON_ERROR),
            'widget_permission' => 'module.decisions_register_project.widget.read',
            'widget_config_json' => json_encode(['max_entries' => 5], JSON_THROW_ON_ERROR),
            'updated_at' => $now,
        ]);

        $this->upsertModule([
            'slug' => 'dependencies_register_project',
            'name' => 'Dependencies Register (Project)',
            'scope_type' => 'project',
            'description' => 'Production module for dependency tracking with risk, escalation, and fulfillment workflow.',
            'display_order' => 50,
            'is_enabled' => 1,
            'version' => '1.1.0',
            'dependencies_json' => json_encode([], JSON_THROW_ON_ERROR),
            'widget_permission' => 'module.dependencies_register_project.widget.read',
            'widget_config_json' => json_encode(['max_entries' => 5], JSON_THROW_ON_ERROR),
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('module_raid_entries', [
            'decision_category',
            'decision_rationale',
            'alternatives_considered',
            'chosen_option',
            'approver_user_id',
            'implementation_actions',
            'superseded_by_entry_id',
            'dependency_type',
            'related_work_package',
            'depends_on',
            'escalation_required',
        ]);
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

        $payload['created_at'] = date('Y-m-d H:i:s');
        $this->db->table('module_registry')->insert($payload);
    }
}
