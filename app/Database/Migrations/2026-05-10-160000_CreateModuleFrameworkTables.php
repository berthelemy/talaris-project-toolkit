<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateModuleFrameworkTables extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],
            'scope_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'is_enabled' => [
                'type'    => 'BOOLEAN',
                'default' => true,
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
        $this->forge->addUniqueKey('slug');
        $this->forge->addKey('scope_type');
        $this->forge->createTable('module_registry');

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'module_slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
            ],
            'scope_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'scope_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'message' => [
                'type' => 'TEXT',
            ],
            'created_by_user_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
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
        $this->forge->addKey('created_by_user_id');
        $this->forge->addForeignKey('created_by_user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('module_hello_world_entries');

        $now = date('Y-m-d H:i:s');

        $this->db->table('module_registry')->insertBatch([
            [
                'slug' => 'hello_world_programme',
                'name' => 'Hello World (Programme)',
                'scope_type' => 'programme',
                'description' => 'Reference module scaffold bound to programme scope.',
                'is_enabled' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'hello_world_project',
                'name' => 'Hello World (Project)',
                'scope_type' => 'project',
                'description' => 'Reference module scaffold bound to project scope.',
                'is_enabled' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('module_hello_world_entries', true);
        $this->forge->dropTable('module_registry', true);
    }
}
