<?php

/**
 * Database migration: Create Module Edit Locks Table.
 */

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateModuleEditLocksTable extends Migration
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
            'locked_by_user_id' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
            ],
            'acquired_at' => [
                'type' => 'DATETIME',
            ],
            'expires_at' => [
                'type' => 'DATETIME',
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
        $this->forge->addKey('locked_by_user_id');
        $this->forge->addKey('expires_at');
        $this->forge->createTable('module_edit_locks');
    }

    public function down(): void
    {
        $this->forge->dropTable('module_edit_locks', true);
    }
}
