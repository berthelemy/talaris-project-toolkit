<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProgrammeProjectDomainSchema extends Migration
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
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'owner_user_id' => [
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
        $this->forge->addKey('owner_user_id');
        $this->forge->addUniqueKey(['owner_user_id', 'name'], 'uniq_programme_owner_name');
        $this->forge->addForeignKey('owner_user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('programmes');

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'owner_user_id' => [
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
        $this->forge->addKey('owner_user_id');
        $this->forge->addUniqueKey(['owner_user_id', 'name'], 'uniq_project_owner_name');
        $this->forge->addForeignKey('owner_user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('projects');

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'programme_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'project_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'linked_by_user_id' => [
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
        $this->forge->addUniqueKey(['programme_id', 'project_id'], 'uniq_programme_project_link');
        $this->forge->addKey('project_id');
        $this->forge->addForeignKey('programme_id', 'programmes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('project_id', 'projects', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('linked_by_user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('programme_projects');
    }

    public function down(): void
    {
        $this->forge->dropTable('programme_projects', true);
        $this->forge->dropTable('projects', true);
        $this->forge->dropTable('programmes', true);
    }
}