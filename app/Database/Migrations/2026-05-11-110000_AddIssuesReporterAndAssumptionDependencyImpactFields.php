<?php

/**
 * File documentation for app/Database/Migrations/2026-05-11-110000_AddIssuesReporterAndAssumptionDependencyImpactFields.php.
 */

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIssuesReporterAndAssumptionDependencyImpactFields extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('module_raid_entries', [
            'date_reported' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'description',
            ],
            'reporter_user_id' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'null' => true,
                'after' => 'date_reported',
            ],
            'impact_level' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'after' => 'impact_if_not_valid',
            ],
        ]);

        $this->forge->addKey('date_reported');
        $this->forge->addKey('reporter_user_id');
        $this->forge->addKey('impact_level');
        $this->forge->addForeignKey('reporter_user_id', 'users', 'id', 'SET NULL', 'CASCADE');
    }

    public function down(): void
    {
        $this->forge->dropColumn('module_raid_entries', [
            'date_reported',
            'reporter_user_id',
            'impact_level',
        ]);
    }
}
