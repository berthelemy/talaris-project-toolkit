<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateThemeSettingsTable extends Migration
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
            'logo_path' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'heading_font' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
                'default' => 'poppins',
            ],
            'body_font' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
                'default' => 'source_sans',
            ],
            'primary_color' => [
                'type' => 'VARCHAR',
                'constraint' => 7,
                'default' => '#0d6efd',
            ],
            'secondary_color' => [
                'type' => 'VARCHAR',
                'constraint' => 7,
                'default' => '#6c757d',
            ],
            'background_color' => [
                'type' => 'VARCHAR',
                'constraint' => 7,
                'default' => '#f8f9fa',
            ],
            'text_color' => [
                'type' => 'VARCHAR',
                'constraint' => 7,
                'default' => '#212529',
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
        $this->forge->createTable('theme_settings');
    }

    public function down(): void
    {
        $this->forge->dropTable('theme_settings', true);
    }
}
