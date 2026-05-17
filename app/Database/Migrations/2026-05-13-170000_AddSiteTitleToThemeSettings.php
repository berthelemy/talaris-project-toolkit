<?php

/**
 * Database migration: Add Site Title To Theme Settings.
 */

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSiteTitleToThemeSettings extends Migration
{
    public function up(): void
    {
        $fields = [
            'site_title' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
                'default' => 'Talaris Project Toolkit',
                'after' => 'id',
            ],
        ];

        $this->forge->addColumn('theme_settings', $fields);
    }

    public function down(): void
    {
        $this->forge->dropColumn('theme_settings', 'site_title');
    }
}
