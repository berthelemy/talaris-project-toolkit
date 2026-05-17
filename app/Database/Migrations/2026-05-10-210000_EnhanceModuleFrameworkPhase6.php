<?php

/**
 * File documentation for app/Database/Migrations/2026-05-10-210000_EnhanceModuleFrameworkPhase6.php.
 */

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnhanceModuleFrameworkPhase6 extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('module_registry', [
            'display_order' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'default' => 0,
                'after' => 'description',
            ],
            'version' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'after' => 'is_enabled',
            ],
            'dependencies_json' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'version',
            ],
            'widget_permission' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
                'null' => true,
                'after' => 'dependencies_json',
            ],
            'widget_config_json' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'widget_permission',
            ],
        ]);

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'module_slug' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
            ],
            'scope_type' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
            'scope_id' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
            ],
            'loaded_count' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'default' => 0,
            ],
            'rendered_count' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'default' => 0,
            ],
            'error_count' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'default' => 0,
            ],
            'last_rendered_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['module_slug', 'scope_type', 'scope_id']);
        $this->forge->addKey('module_slug');
        $this->forge->createTable('module_widget_metrics');

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'module_slug' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
            ],
            'scope_type' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'scope_id' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'null' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'null' => true,
            ],
            'phase' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'load',
            ],
            'error_message' => [
                'type' => 'TEXT',
            ],
            'trace' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['module_slug', 'created_at']);
        $this->forge->addKey('user_id');
        $this->forge->createTable('module_widget_failures');

        $this->db->table('module_registry')
            ->where('slug', 'hello_world_programme')
            ->update([
                'display_order' => 10,
                'version' => '1.0.0',
                'dependencies_json' => json_encode([], JSON_THROW_ON_ERROR),
                'widget_permission' => 'module.hello_world_programme.widget.read',
                'widget_config_json' => json_encode(['max_entries' => 5], JSON_THROW_ON_ERROR),
            ]);

        $this->db->table('module_registry')
            ->where('slug', 'hello_world_project')
            ->update([
                'display_order' => 10,
                'version' => '1.0.0',
                'dependencies_json' => json_encode([], JSON_THROW_ON_ERROR),
                'widget_permission' => 'module.hello_world_project.widget.read',
                'widget_config_json' => json_encode(['max_entries' => 5], JSON_THROW_ON_ERROR),
            ]);

        $now = date('Y-m-d H:i:s');
        $riskExists = $this->db->table('module_registry')->where('slug', 'risk_register_project')->countAllResults();
        if ($riskExists === 0) {
            $this->db->table('module_registry')->insert([
                'slug' => 'risk_register_project',
                'name' => 'Risk Register (Project)',
                'scope_type' => 'project',
                'description' => 'Reference module scaffold for project risk tracking.',
                'display_order' => 20,
                'is_enabled' => 1,
                'version' => '0.1.0',
                'dependencies_json' => json_encode([], JSON_THROW_ON_ERROR),
                'widget_permission' => 'module.risk_register_project.widget.read',
                'widget_config_json' => json_encode(['max_entries' => 5], JSON_THROW_ON_ERROR),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $issueExists = $this->db->table('module_registry')->where('slug', 'issue_tracker_project')->countAllResults();
        if ($issueExists === 0) {
            $this->db->table('module_registry')->insert([
                'slug' => 'issue_tracker_project',
                'name' => 'Issue Tracker (Project)',
                'scope_type' => 'project',
                'description' => 'Reference module scaffold for project issue tracking.',
                'display_order' => 30,
                'is_enabled' => 1,
                'version' => '0.1.0',
                'dependencies_json' => json_encode([], JSON_THROW_ON_ERROR),
                'widget_permission' => 'module.issue_tracker_project.widget.read',
                'widget_config_json' => json_encode(['max_entries' => 5], JSON_THROW_ON_ERROR),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        $this->db->table('module_registry')
            ->whereIn('slug', ['risk_register_project', 'issue_tracker_project'])
            ->delete();

        $this->forge->dropTable('module_widget_failures', true);
        $this->forge->dropTable('module_widget_metrics', true);

        $this->forge->dropColumn('module_registry', [
            'display_order',
            'version',
            'dependencies_json',
            'widget_permission',
            'widget_config_json',
        ]);
    }
}
