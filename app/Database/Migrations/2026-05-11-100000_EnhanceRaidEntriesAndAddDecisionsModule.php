<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnhanceRaidEntriesAndAddDecisionsModule extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('module_raid_entries', [
            'mitigation_actions' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'description',
            ],
            'impact' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'after' => 'priority',
            ],
            'likelihood' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'after' => 'impact',
            ],
            'impact_if_not_valid' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'likelihood',
            ],
            'decision_date' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'review_date',
            ],
            'made_by_user_id' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'null' => true,
                'after' => 'decision_date',
            ],
        ]);

        $this->forge->addKey('impact');
        $this->forge->addKey('likelihood');
        $this->forge->addKey('decision_date');
        $this->forge->addKey('made_by_user_id');
        $this->forge->addForeignKey('made_by_user_id', 'users', 'id', 'SET NULL', 'CASCADE');

        $now = date('Y-m-d H:i:s');
        $existing = $this->db->table('module_registry')
            ->where('slug', 'decisions_register_project')
            ->get()
            ->getRowArray();

        $payload = [
            'slug' => 'decisions_register_project',
            'name' => 'Decisions Register (Project)',
            'scope_type' => 'project',
            'description' => 'Production module for key project decisions and accountability.',
            'display_order' => 60,
            'is_enabled' => 1,
            'version' => '1.0.0',
            'dependencies_json' => json_encode([], JSON_THROW_ON_ERROR),
            'widget_permission' => 'module.decisions_register_project.widget.read',
            'widget_config_json' => json_encode(['max_entries' => 5], JSON_THROW_ON_ERROR),
            'updated_at' => $now,
        ];

        if (is_array($existing)) {
            $this->db->table('module_registry')->where('id', (int) $existing['id'])->update($payload);
        } else {
            $payload['created_at'] = $now;
            $this->db->table('module_registry')->insert($payload);
        }
    }

    public function down(): void
    {
        $this->db->table('module_registry')
            ->where('slug', 'decisions_register_project')
            ->delete();

        $this->forge->dropColumn('module_raid_entries', [
            'mitigation_actions',
            'impact',
            'likelihood',
            'impact_if_not_valid',
            'decision_date',
            'made_by_user_id',
        ]);
    }
}
