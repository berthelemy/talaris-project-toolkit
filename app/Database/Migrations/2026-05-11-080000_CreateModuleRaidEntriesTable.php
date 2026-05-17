<?php

/**
 * File documentation for app/Database/Migrations/2026-05-11-080000_CreateModuleRaidEntriesTable.php.
 */

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateModuleRaidEntriesTable extends Migration
{
    public function up(): void
    {
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
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'owner_user_id' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'open',
            ],
            'priority' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'medium',
            ],
            'target_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'review_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'closed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_by_user_id' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
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
        $this->forge->addKey(['module_slug', 'scope_type', 'scope_id']);
        $this->forge->addKey('status');
        $this->forge->addKey('priority');
        $this->forge->addKey('owner_user_id');
        $this->forge->addKey('target_date');
        $this->forge->addForeignKey('owner_user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('created_by_user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('updated_by_user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('module_raid_entries');
    }

    public function down(): void
    {
        $this->forge->dropTable('module_raid_entries', true);
    }
}
