<?php

/**
 * Database migration: Add Rbac And User Profile Schema.
 */

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Roles;

class AddRbacAndUserProfileSchema extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('users', [
            'language_preference' => [
                'type'       => 'VARCHAR',
                'constraint' => 5,
                'null'       => true,
            ],
            'profile_description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'avatar_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
        ]);

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'is_predefined' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'permissions_json' => [
                'type' => 'TEXT',
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
        $this->forge->addUniqueKey('slug');
        $this->forge->addUniqueKey('name');
        $this->forge->createTable('roles');

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'role_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'scope_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'scope_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
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
        $this->forge->addUniqueKey(['user_id', 'role_id', 'scope_type', 'scope_id'], 'uniq_user_role_scope');
        $this->forge->addKey(['scope_type', 'scope_id']);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('role_id', 'roles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('user_role_assignments');

        $roles = config(Roles::class);
        $now = date('Y-m-d H:i:s');

        foreach ($roles->predefinedRoles as $role) {
            $this->db->table('roles')->insert([
                'slug' => $role['slug'],
                'name' => $role['name'],
                'description' => $role['description'],
                'is_predefined' => 1,
                'permissions_json' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('user_role_assignments', true);
        $this->forge->dropTable('roles', true);
        $this->forge->dropColumn('users', ['language_preference', 'profile_description', 'avatar_path']);
    }
}
