<?php

/**
 * Database migration: Add Lessons Learned To Module Raid Entries.
 */

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLessonsLearnedToModuleRaidEntries extends Migration
{
    public function up(): void
    {
        if (! $this->hasField('module_raid_entries', 'lessons_learned')) {
            $this->forge->addColumn('module_raid_entries', [
                'lessons_learned' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'impact_if_not_valid',
                ],
            ]);
        }
    }

    public function down(): void
    {
        if ($this->hasField('module_raid_entries', 'lessons_learned')) {
            $this->forge->dropColumn('module_raid_entries', 'lessons_learned');
        }
    }

    private function hasField(string $table, string $field): bool
    {
        $physicalTable = $this->db->DBPrefix . $table;

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
