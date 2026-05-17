<?php

/**
 * Database migration: Add Project Status And Widget Layout Preferences.
 */

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProjectStatusAndWidgetLayoutPreferences extends Migration
{
    public function up(): void
    {
        if (! $this->hasField('projects', 'status')) {
            $this->forge->addColumn('projects', [
                'status' => [
                    'type' => 'VARCHAR',
                    'constraint' => 32,
                    'null' => false,
                    'default' => 'not_started',
                    'after' => 'description',
                ],
            ]);
        }

        if (! $this->hasTable('module_widget_layout_preferences')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'scope_type' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                ],
                'scope_id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'default' => 0,
                ],
                'module_slug' => [
                    'type' => 'VARCHAR',
                    'constraint' => 120,
                ],
                'is_visible' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 1,
                ],
                'display_order' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'null' => true,
                ],
                'updated_by_user_id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
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
            $this->forge->addKey(['scope_type', 'scope_id']);
            $this->forge->addKey('module_slug');
            $this->forge->addUniqueKey(['scope_type', 'scope_id', 'module_slug'], 'uniq_widget_layout_scope_module');
            $this->forge->addForeignKey('updated_by_user_id', 'users', 'id', 'SET NULL', 'CASCADE');
            $this->forge->createTable('module_widget_layout_preferences');
        }
    }

    public function down(): void
    {
        if ($this->hasTable('module_widget_layout_preferences')) {
            $this->forge->dropTable('module_widget_layout_preferences', true);
        }

        if ($this->hasField('projects', 'status')) {
            $this->forge->dropColumn('projects', 'status');
        }
    }

    private function hasTable(string $table): bool
    {
        $physicalTable = $this->db->prefixTable($table);

        if ($this->db->DBDriver === 'SQLite3') {
            $result = $this->db->query(
                'SELECT name FROM sqlite_master WHERE type = ? AND name = ? LIMIT 1',
                ['table', $physicalTable],
            )->getRowArray();

            return is_array($result);
        }

        $result = $this->db->query(
            'SELECT 1 FROM information_schema.tables WHERE table_schema = ? AND table_name = ? LIMIT 1',
            [$this->db->database, $physicalTable],
        )->getRowArray();

        return is_array($result);
    }

    private function hasField(string $table, string $field): bool
    {
        if (! $this->hasTable($table)) {
            return false;
        }

        $physicalTable = $this->db->prefixTable($table);

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
