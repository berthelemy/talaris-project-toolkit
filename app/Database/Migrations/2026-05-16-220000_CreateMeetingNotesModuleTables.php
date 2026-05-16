<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Create persistence and registry entry for the Meeting Notes project module.
 */
class CreateMeetingNotesModuleTables extends Migration
{
    /**
     * @return void
     */
    public function up(): void
    {
        if (! $this->db->tableExists('module_meeting_notes')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'module_slug' => [
                    'type' => 'VARCHAR',
                    'constraint' => 64,
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
                'purpose' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'meeting_date' => [
                    'type' => 'DATE',
                ],
                'meeting_type' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                ],
                'context_level' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'null' => true,
                ],
                'related_objective' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'chair_user_id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'null' => true,
                ],
                'minute_taker_user_id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'null' => true,
                ],
                'attendees_text' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'absentees_text' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'agenda_text' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'discussion_text' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'decisions_text' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'raised_links_text' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'follow_up_date' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'status' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'default' => 'draft',
                ],
                'lessons_learned' => [
                    'type' => 'TEXT',
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
            $this->forge->addKey('meeting_date');
            $this->forge->createTable('module_meeting_notes', true);
        }

        if (! $this->db->tableExists('module_meeting_actions')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'meeting_note_id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                ],
                'description' => [
                    'type' => 'TEXT',
                ],
                'owner_user_id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'null' => true,
                ],
                'due_date' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'status' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'default' => 'open',
                ],
                'linked_task_entry_id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
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
            $this->forge->addKey(['meeting_note_id', 'status']);
            $this->forge->addKey('due_date');
            $this->forge->addForeignKey('meeting_note_id', 'module_meeting_notes', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('module_meeting_actions', true);
        }

        $now = date('Y-m-d H:i:s');
        $this->upsertModule([
            'slug' => 'meeting_notes_project',
            'name' => 'Meeting Notes (Project)',
            'scope_type' => 'project',
            'description' => 'Capture structured meeting notes, actions, and follow-up tracking.',
            'display_order' => 80,
            'is_enabled' => 1,
            'version' => '0.1.0',
            'dependencies_json' => json_encode([], JSON_THROW_ON_ERROR),
            'widget_permission' => 'module.meeting_notes_project.widget.read',
            'widget_config_json' => json_encode(['max_entries' => 5], JSON_THROW_ON_ERROR),
            'updated_at' => $now,
        ]);
    }

    /**
     * @return void
     */
    public function down(): void
    {
        $this->db->table('module_registry')
            ->where('slug', 'meeting_notes_project')
            ->delete();

        $this->forge->dropTable('module_meeting_actions', true);
        $this->forge->dropTable('module_meeting_notes', true);
    }

    /**
     * @param array<string,mixed> $payload
     * @return void
     */
    private function upsertModule(array $payload): void
    {
        $existing = $this->db->table('module_registry')
            ->where('slug', (string) $payload['slug'])
            ->get()
            ->getRowArray();

        if (is_array($existing)) {
            unset($payload['created_at']);
            $this->db->table('module_registry')
                ->where('id', (int) $existing['id'])
                ->update($payload);

            return;
        }

        $payload['created_at'] = date('Y-m-d H:i:s');
        $this->db->table('module_registry')->insert($payload);
    }
}