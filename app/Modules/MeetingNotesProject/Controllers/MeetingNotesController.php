<?php

/**
 * MeetingNotesProject module controller: MeetingNotesController.
 */

namespace App\Modules\MeetingNotesProject\Controllers;

use App\Controllers\BaseController;
use App\Libraries\Auth\AuditLogger;
use App\Libraries\Modules\ModuleApiAuthorizationService;
use App\Libraries\Modules\ModuleRegistryService;
use App\Libraries\Modules\ModuleWidgetService;
use App\Models\ProjectModel;
use App\Models\UserModel;
use App\Modules\MeetingNotesProject\Models\MeetingNoteModel;
use App\Modules\MeetingNotesProject\Models\MeetingNotesRaidEntryModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Project meeting notes controller.
 */
class MeetingNotesController extends BaseController
{
    private const ACTION_TASK_CATEGORY = 'meeting_action';

    /**
     * @return RedirectResponse
     */
    public function createRelatedRisk(int $projectId, int $noteId): RedirectResponse
    {
        $actorId = $this->sessionUserId();
        if (! $this->canWrite($actorId, $projectId)) {
            return $this->redirectModule($projectId)->with('error', lang('Domain.notAuthorized'));
        }

        if (! (new ModuleRegistryService())->isEnabled('risk_register_project', 'project')) {
            return $this->redirectModule($projectId)->with('error', lang('Module.disabledForScope'));
        }

        $note = (new MeetingNoteModel())->find($noteId);
        if (! is_array($note) || ! $this->matchesScope($note, $projectId)) {
            return $this->redirectModule($projectId)->with('error', 'Meeting note not found.');
        }

        $title = trim((string) $this->request->getPost('title'));
        $description = $this->nullableString((string) $this->request->getPost('description'));
        $ownerId = $this->nullableInt((string) $this->request->getPost('owner_user_id')) ?: (int) $actorId;
        $status = trim((string) ($this->request->getPost('status') ?: 'open'));
        $priority = trim((string) ($this->request->getPost('priority') ?: 'medium'));

        (new MeetingNotesRaidEntryModel())->insert([
            'module_slug' => 'risk_register_project',
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'title' => mb_substr($title, 0, 200),
            'description' => $description,
            'owner_user_id' => $ownerId,
            'status' => $status,
            'priority' => $priority,
            'related_module_entry_id' => $noteId,
            'created_by_user_id' => (int) $actorId,
            'updated_by_user_id' => (int) $actorId,
        ]);

        (new ModuleWidgetService())->invalidateScopeCaches('project', $projectId);

        return redirect()->to('/projects/' . $projectId . '/modules/meeting-notes#note-' . $noteId)
            ->with('success', 'Related risk added successfully.');
    }

    /**
     * @return RedirectResponse
     */
    public function createRelatedAssumption(int $projectId, int $noteId): RedirectResponse
    {
        $actorId = $this->sessionUserId();
        if (! $this->canWrite($actorId, $projectId)) {
            return $this->redirectModule($projectId)->with('error', lang('Domain.notAuthorized'));
        }

        if (! (new ModuleRegistryService())->isEnabled('assumptions_register_project', 'project')) {
            return $this->redirectModule($projectId)->with('error', lang('Module.disabledForScope'));
        }

        $note = (new MeetingNoteModel())->find($noteId);
        if (! is_array($note) || ! $this->matchesScope($note, $projectId)) {
            return $this->redirectModule($projectId)->with('error', 'Meeting note not found.');
        }

        $title = trim((string) $this->request->getPost('title'));
        $description = $this->nullableString((string) $this->request->getPost('description'));
        $ownerId = $this->nullableInt((string) $this->request->getPost('owner_user_id')) ?: (int) $actorId;
        $status = trim((string) ($this->request->getPost('status') ?: 'open'));
        $priority = trim((string) ($this->request->getPost('priority') ?: 'medium'));

        (new MeetingNotesRaidEntryModel())->insert([
            'module_slug' => 'assumptions_register_project',
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'title' => mb_substr($title, 0, 200),
            'description' => $description,
            'owner_user_id' => $ownerId,
            'status' => $status,
            'priority' => $priority,
            'related_module_entry_id' => $noteId,
            'created_by_user_id' => (int) $actorId,
            'updated_by_user_id' => (int) $actorId,
        ]);

        (new ModuleWidgetService())->invalidateScopeCaches('project', $projectId);

        return redirect()->to('/projects/' . $projectId . '/modules/meeting-notes#note-' . $noteId)
            ->with('success', 'Related assumption added successfully.');
    }

    /**
     * @return RedirectResponse
     */
    public function createRelatedIssue(int $projectId, int $noteId): RedirectResponse
    {
        $actorId = $this->sessionUserId();
        if (! $this->canWrite($actorId, $projectId)) {
            return $this->redirectModule($projectId)->with('error', lang('Domain.notAuthorized'));
        }

        if (! (new ModuleRegistryService())->isEnabled('issue_tracker_project', 'project')) {
            return $this->redirectModule($projectId)->with('error', lang('Module.disabledForScope'));
        }

        $note = (new MeetingNoteModel())->find($noteId);
        if (! is_array($note) || ! $this->matchesScope($note, $projectId)) {
            return $this->redirectModule($projectId)->with('error', 'Meeting note not found.');
        }

        $title = trim((string) $this->request->getPost('title'));
        $description = $this->nullableString((string) $this->request->getPost('description'));
        $ownerId = $this->nullableInt((string) $this->request->getPost('owner_user_id')) ?: (int) $actorId;
        $status = trim((string) ($this->request->getPost('status') ?: 'open'));
        $priority = trim((string) ($this->request->getPost('priority') ?: 'medium'));

        (new MeetingNotesRaidEntryModel())->insert([
            'module_slug' => 'issue_tracker_project',
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'title' => mb_substr($title, 0, 200),
            'description' => $description,
            'owner_user_id' => $ownerId,
            'status' => $status,
            'priority' => $priority,
            'related_module_entry_id' => $noteId,
            'created_by_user_id' => (int) $actorId,
            'updated_by_user_id' => (int) $actorId,
        ]);

        (new ModuleWidgetService())->invalidateScopeCaches('project', $projectId);

        return redirect()->to('/projects/' . $projectId . '/modules/meeting-notes#note-' . $noteId)
            ->with('success', 'Related issue added successfully.');
    }

    /**
     * @return RedirectResponse
     */
    public function createRelatedDependency(int $projectId, int $noteId): RedirectResponse
    {
        $actorId = $this->sessionUserId();
        if (! $this->canWrite($actorId, $projectId)) {
            return $this->redirectModule($projectId)->with('error', lang('Domain.notAuthorized'));
        }

        if (! (new ModuleRegistryService())->isEnabled('dependencies_register_project', 'project')) {
            return $this->redirectModule($projectId)->with('error', lang('Module.disabledForScope'));
        }

        $note = (new MeetingNoteModel())->find($noteId);
        if (! is_array($note) || ! $this->matchesScope($note, $projectId)) {
            return $this->redirectModule($projectId)->with('error', 'Meeting note not found.');
        }

        $title = trim((string) $this->request->getPost('title'));
        $description = $this->nullableString((string) $this->request->getPost('description'));
        $ownerId = $this->nullableInt((string) $this->request->getPost('owner_user_id')) ?: (int) $actorId;
        $status = trim((string) ($this->request->getPost('status') ?: 'open'));
        $priority = trim((string) ($this->request->getPost('priority') ?: 'medium'));

        (new MeetingNotesRaidEntryModel())->insert([
            'module_slug' => 'dependencies_register_project',
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'title' => mb_substr($title, 0, 200),
            'description' => $description,
            'owner_user_id' => $ownerId,
            'status' => $status,
            'priority' => $priority,
            'related_module_entry_id' => $noteId,
            'created_by_user_id' => (int) $actorId,
            'updated_by_user_id' => (int) $actorId,
        ]);

        (new ModuleWidgetService())->invalidateScopeCaches('project', $projectId);

        return redirect()->to('/projects/' . $projectId . '/modules/meeting-notes#note-' . $noteId)
            ->with('success', 'Related dependency added successfully.');
    }

    /**
     * @return string|RedirectResponse
     */
    public function index(int $projectId): string|RedirectResponse
    {
        $actorId = $this->sessionUserId();

        if ($actorId === null) {
            return redirect()->to('/login')->with('error', lang('Auth.loginRequired'));
        }

        $project = (new ProjectModel())->find($projectId);
        if (! is_array($project) || ! $this->canRead($actorId, $projectId)) {
            return redirect()->to('/projects')->with('error', lang('Domain.notAuthorized'));
        }

        if (! (new ModuleRegistryService())->isEnabled('meeting_notes_project', 'project')) {
            return redirect()->to('/projects/' . $projectId)->with('error', lang('Module.disabledForScope'));
        }

        return view('App\Modules\MeetingNotesProject\Views\index', [
            'project' => $project,
            'scope_id' => $projectId,
            'notes' => $this->notesWithRelations($projectId),
            'owners' => $this->ownerOptions(),
            'tasks_module_enabled' => (new ModuleRegistryService())->isEnabled('tasks_register_project', 'project'),
            'decisions_module_enabled' => (new ModuleRegistryService())->isEnabled('decisions_register_project', 'project'),
            'is_read_only' => ! $this->canWrite($actorId, $projectId),
        ]);
    }

    /**
     * @return RedirectResponse
     */
    public function create(int $projectId): RedirectResponse
    {
        $actorId = $this->sessionUserId();

        if (! $this->canWrite($actorId, $projectId)) {
            return $this->redirectModule($projectId)->with('error', lang('Domain.notAuthorized'));
        }

        if (! $this->validateData($this->request->getPost(), $this->validationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $noteModel = new MeetingNoteModel();
        $noteId = $noteModel->insert([
            'module_slug' => 'meeting_notes_project',
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'title' => trim((string) $this->request->getPost('title')),
            'purpose' => $this->nullableString((string) $this->request->getPost('purpose')),
            'meeting_date' => (string) $this->request->getPost('meeting_date'),
            'meeting_type' => $this->nullableString((string) $this->request->getPost('meeting_type')),
            'context_level' => 'project',
            'related_objective' => $this->nullableString((string) $this->request->getPost('related_objective')),
            'chair_user_id' => $this->nullableInt((string) $this->request->getPost('chair_user_id')),
            'minute_taker_user_id' => $this->nullableInt((string) $this->request->getPost('minute_taker_user_id')),
            'attendees_text' => $this->nullableString((string) $this->request->getPost('attendees_text')),
            'absentees_text' => $this->nullableString((string) $this->request->getPost('absentees_text')),
            'agenda_text' => $this->nullableString((string) $this->request->getPost('agenda_text')),
            'discussion_text' => $this->nullableString((string) $this->request->getPost('discussion_text')),
            'decisions_text' => $this->nullableString((string) $this->request->getPost('decisions_text')),
            'raised_links_text' => $this->nullableString((string) $this->request->getPost('raised_links_text')),
            'follow_up_date' => $this->nullableDate((string) $this->request->getPost('follow_up_date')),
            'status' => trim((string) ($this->request->getPost('status') ?: 'draft')),
            'lessons_learned' => $this->nullableString((string) $this->request->getPost('lessons_learned')),
            'created_by_user_id' => (int) $actorId,
            'updated_by_user_id' => (int) $actorId,
        ], true);

        (new AuditLogger())->log('meeting_note_created', 'success', (int) $actorId, [
            'module_slug' => 'meeting_notes_project',
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'note_id' => $noteId,
        ]);

        (new ModuleWidgetService())->invalidateScopeCaches('project', $projectId);

        return $this->redirectModule($projectId)->with('success', 'Meeting note created successfully.');
    }

    /**
     * @return RedirectResponse
     */
    public function update(int $projectId, int $noteId): RedirectResponse
    {
        $actorId = $this->sessionUserId();

        if (! $this->canWrite($actorId, $projectId)) {
            return $this->redirectModule($projectId)->with('error', lang('Domain.notAuthorized'));
        }

        $noteModel = new MeetingNoteModel();
        $note = $noteModel->find($noteId);

        if (! is_array($note) || ! $this->matchesScope($note, $projectId)) {
            return $this->redirectModule($projectId)->with('error', 'Meeting note not found.');
        }

        if (! $this->validateData($this->request->getPost(), $this->validationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $status = trim((string) ($this->request->getPost('status') ?: 'draft'));
        $noteModel->update($noteId, [
            'title' => trim((string) $this->request->getPost('title')),
            'purpose' => $this->nullableString((string) $this->request->getPost('purpose')),
            'meeting_date' => (string) $this->request->getPost('meeting_date'),
            'meeting_type' => $this->nullableString((string) $this->request->getPost('meeting_type')),
            'related_objective' => $this->nullableString((string) $this->request->getPost('related_objective')),
            'chair_user_id' => $this->nullableInt((string) $this->request->getPost('chair_user_id')),
            'minute_taker_user_id' => $this->nullableInt((string) $this->request->getPost('minute_taker_user_id')),
            'attendees_text' => $this->nullableString((string) $this->request->getPost('attendees_text')),
            'absentees_text' => $this->nullableString((string) $this->request->getPost('absentees_text')),
            'agenda_text' => $this->nullableString((string) $this->request->getPost('agenda_text')),
            'discussion_text' => $this->nullableString((string) $this->request->getPost('discussion_text')),
            'decisions_text' => $this->nullableString((string) $this->request->getPost('decisions_text')),
            'raised_links_text' => $this->nullableString((string) $this->request->getPost('raised_links_text')),
            'follow_up_date' => $this->nullableDate((string) $this->request->getPost('follow_up_date')),
            'status' => $status,
            'lessons_learned' => $this->nullableString((string) $this->request->getPost('lessons_learned')),
            'closed_at' => null,
            'updated_by_user_id' => (int) $actorId,
        ]);

        (new AuditLogger())->log('meeting_note_updated', 'success', (int) $actorId, [
            'module_slug' => 'meeting_notes_project',
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'note_id' => $noteId,
        ]);

        (new ModuleWidgetService())->invalidateScopeCaches('project', $projectId);

        return $this->redirectModule($projectId)->with('success', 'Meeting note updated successfully.');
    }

    /**
     * @return RedirectResponse
     */
    public function createRelatedDecision(int $projectId, int $noteId): RedirectResponse
    {
        $actorId = $this->sessionUserId();
        if (! $this->canWrite($actorId, $projectId)) {
            return $this->redirectModule($projectId)->with('error', lang('Domain.notAuthorized'));
        }

        if (! (new ModuleRegistryService())->isEnabled('decisions_register_project', 'project')) {
            return $this->redirectModule($projectId)->with('error', lang('Module.disabledForScope'));
        }

        $note = (new MeetingNoteModel())->find($noteId);
        if (! is_array($note) || ! $this->matchesScope($note, $projectId)) {
            return $this->redirectModule($projectId)->with('error', 'Meeting note not found.');
        }

        $description = trim((string) $this->request->getPost('description'));
        $decisionDate = trim((string) $this->request->getPost('decision_date'));
        if ($description === '' || $decisionDate === '') {
            return redirect()->back()->withInput()->with('error', 'Decision description and date are required.');
        }

        $ownerId = $this->nullableInt((string) $this->request->getPost('owner_user_id')) ?: (int) $actorId;
        $status = trim((string) ($this->request->getPost('status') ?: 'draft'));
        if (! in_array($status, ['draft', 'proposed', 'approved', 'implemented', 'rejected', 'superseded', 'closed'], true)) {
            $status = 'draft';
        }

        $priority = trim((string) ($this->request->getPost('priority') ?: 'medium'));
        if (! in_array($priority, ['low', 'medium', 'high', 'critical'], true)) {
            $priority = 'medium';
        }

        $title = trim((string) $this->request->getPost('title'));
        if ($title === '') {
            $title = mb_substr($description, 0, 200);
        }

        (new MeetingNotesRaidEntryModel())->insert([
            'module_slug' => 'decisions_register_project',
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'title' => mb_substr($title, 0, 200),
            'description' => $description,
            'owner_user_id' => $ownerId,
            'status' => $status,
            'priority' => $priority,
            'decision_date' => $decisionDate,
            'made_by_user_id' => (int) $actorId,
            'decision_rationale' => $this->decisionLinkTag($noteId),
            'created_by_user_id' => (int) $actorId,
            'updated_by_user_id' => (int) $actorId,
        ]);

        (new ModuleWidgetService())->invalidateScopeCaches('project', $projectId);

        return redirect()->to('/projects/' . $projectId . '/modules/meeting-notes#note-' . $noteId)
            ->with('success', 'Related decision added successfully.');
    }

    /**
     * @return RedirectResponse
     */
    public function createRelatedAction(int $projectId, int $noteId): RedirectResponse
    {
        $actorId = $this->sessionUserId();
        if (! $this->canWrite($actorId, $projectId)) {
            return $this->redirectModule($projectId)->with('error', lang('Domain.notAuthorized'));
        }

        if (! (new ModuleRegistryService())->isEnabled('tasks_register_project', 'project')) {
            return $this->redirectModule($projectId)->with('error', lang('Module.disabledForScope'));
        }

        $note = (new MeetingNoteModel())->find($noteId);
        if (! is_array($note) || ! $this->matchesScope($note, $projectId)) {
            return $this->redirectModule($projectId)->with('error', 'Meeting note not found.');
        }

        $title = trim((string) $this->request->getPost('title'));
        if ($title === '') {
            return redirect()->back()->withInput()->with('error', 'Action title is required.');
        }

        $description = $this->nullableString((string) $this->request->getPost('description'));
        $ownerId = $this->nullableInt((string) $this->request->getPost('owner_user_id')) ?: (int) $actorId;
        $status = trim((string) ($this->request->getPost('status') ?: 'open'));
        if (! in_array($status, ['open', 'in_progress', 'blocked', 'in_review', 'completed', 'cancelled', 'closed'], true)) {
            $status = 'open';
        }

        $priority = trim((string) ($this->request->getPost('priority') ?: 'medium'));
        if (! in_array($priority, ['low', 'medium', 'high', 'critical'], true)) {
            $priority = 'medium';
        }

        (new MeetingNotesRaidEntryModel())->insert([
            'module_slug' => 'tasks_register_project',
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'title' => mb_substr($title, 0, 200),
            'description' => $description,
            'owner_user_id' => $ownerId,
            'status' => $status,
            'priority' => $priority,
            'task_category' => self::ACTION_TASK_CATEGORY,
            'related_module_entry_id' => $noteId,
            'related_objective' => 'Meeting note #' . $noteId,
            'due_date' => $this->nullableDate((string) $this->request->getPost('due_date')),
            'created_by_user_id' => (int) $actorId,
            'updated_by_user_id' => (int) $actorId,
        ]);

        (new ModuleWidgetService())->invalidateScopeCaches('project', $projectId);

        return redirect()->to('/projects/' . $projectId . '/modules/meeting-notes#note-' . $noteId)
            ->with('success', 'Related action added successfully.');
    }

    /**
     * @return RedirectResponse
     */
    public function delete(int $projectId, int $noteId): RedirectResponse
    {
        $actorId = $this->sessionUserId();

        if (! $this->canWrite($actorId, $projectId)) {
            return $this->redirectModule($projectId)->with('error', lang('Domain.notAuthorized'));
        }

        $noteModel = new MeetingNoteModel();
        $note = $noteModel->find($noteId);

        if (! is_array($note) || ! $this->matchesScope($note, $projectId)) {
            return $this->redirectModule($projectId)->with('error', 'Meeting note not found.');
        }

        $noteModel->delete($noteId);
        (new ModuleWidgetService())->invalidateScopeCaches('project', $projectId);

        return $this->redirectModule($projectId)->with('success', 'Meeting note deleted successfully.');
    }

    /**
     * @return list<array{id:int,username:string}>
     */
    private function ownerOptions(): array
    {
        $rows = (new UserModel())
            ->select('id, username')
            ->where('is_active', 1)
            ->orderBy('username', 'ASC')
            ->findAll();

        return array_map(static fn (array $row): array => [
            'id' => (int) ($row['id'] ?? 0),
            'username' => (string) ($row['username'] ?? ''),
        ], $rows);
    }

    /**
     * @return list<array<string,mixed>>
     */
    private function notesWithRelations(int $projectId): array
    {
        $notes = (new MeetingNoteModel())
            ->select('module_meeting_notes.*, chair.username AS chair_username, minute.username AS minute_taker_username, creator.username AS created_by_username')
            ->join('users AS chair', 'chair.id = module_meeting_notes.chair_user_id', 'left')
            ->join('users AS minute', 'minute.id = module_meeting_notes.minute_taker_user_id', 'left')
            ->join('users AS creator', 'creator.id = module_meeting_notes.created_by_user_id', 'left')
            ->where('module_meeting_notes.module_slug', 'meeting_notes_project')
            ->where('module_meeting_notes.scope_type', 'project')
            ->where('module_meeting_notes.scope_id', $projectId)
            ->orderBy('module_meeting_notes.meeting_date', 'DESC')
            ->orderBy('module_meeting_notes.updated_at', 'DESC')
            ->findAll();

        if ($notes === []) {
            return [];
        }

        $noteIds = array_map(static fn (array $note): int => (int) ($note['id'] ?? 0), $notes);
        $tasksByNote = [];
        $tasks = (new MeetingNotesRaidEntryModel())
            ->select('module_raid_entries.*, users.username AS owner_username')
            ->join('users', 'users.id = module_raid_entries.owner_user_id', 'left')
            ->where('module_raid_entries.module_slug', 'tasks_register_project')
            ->where('module_raid_entries.scope_type', 'project')
            ->where('module_raid_entries.scope_id', $projectId)
            ->where('module_raid_entries.task_category', self::ACTION_TASK_CATEGORY)
            ->whereIn('module_raid_entries.related_module_entry_id', $noteIds)
            ->orderBy('module_raid_entries.updated_at', 'DESC')
            ->findAll();

        foreach ($tasks as $task) {
            $tasksByNote[(int) ($task['related_module_entry_id'] ?? 0)][] = $task;
        }

        foreach ($notes as &$note) {
            $id = (int) ($note['id'] ?? 0);
            $note['actions'] = $tasksByNote[$id] ?? [];

            $decisionLinkTag = $this->decisionLinkTag($id);
            $note['linked_decisions'] = (new MeetingNotesRaidEntryModel())
                ->select('id, title, status')
                ->where('module_slug', 'decisions_register_project')
                ->where('scope_type', 'project')
                ->where('scope_id', $projectId)
                ->where('decision_rationale', $decisionLinkTag)
                ->orderBy('id', 'ASC')
                ->findAll();

            // Related risks
            $note['linked_risks'] = (new MeetingNotesRaidEntryModel())
                ->select('id, title, status')
                ->where('module_slug', 'risk_register_project')
                ->where('scope_type', 'project')
                ->where('scope_id', $projectId)
                ->where('related_module_entry_id', $id)
                ->orderBy('id', 'ASC')
                ->findAll();

            // Related assumptions
            $note['linked_assumptions'] = (new MeetingNotesRaidEntryModel())
                ->select('id, title, status')
                ->where('module_slug', 'assumptions_register_project')
                ->where('scope_type', 'project')
                ->where('scope_id', $projectId)
                ->where('related_module_entry_id', $id)
                ->orderBy('id', 'ASC')
                ->findAll();

            // Related issues
            $note['linked_issues'] = (new MeetingNotesRaidEntryModel())
                ->select('id, title, status')
                ->where('module_slug', 'issue_tracker_project')
                ->where('scope_type', 'project')
                ->where('scope_id', $projectId)
                ->where('related_module_entry_id', $id)
                ->orderBy('id', 'ASC')
                ->findAll();

            // Related dependencies
            $note['linked_dependencies'] = (new MeetingNotesRaidEntryModel())
                ->select('id, title, status')
                ->where('module_slug', 'dependencies_register_project')
                ->where('scope_type', 'project')
                ->where('scope_id', $projectId)
                ->where('related_module_entry_id', $id)
                ->orderBy('id', 'ASC')
                ->findAll();
        }
        unset($note);

        return $notes;
    }

    /**
     * @return string
     */
    private function decisionLinkTag(int $noteId): string
    {
        return 'MeetingNote#' . $noteId;
    }

    /**
     * @return bool
     */
    private function canRead(?int $actorId, int $projectId): bool
    {
        return $actorId !== null
            && (new ModuleRegistryService())->isEnabled('meeting_notes_project', 'project')
            && (new ModuleApiAuthorizationService())->canRead($actorId, 'project', $projectId);
    }

    /**
     * @return bool
     */
    private function canWrite(?int $actorId, int $projectId): bool
    {
        return $actorId !== null
            && (new ModuleRegistryService())->isEnabled('meeting_notes_project', 'project')
            && (new ModuleApiAuthorizationService())->canWrite($actorId, 'project', $projectId);
    }

    /**
     * @param array<string,mixed> $note
     * @return bool
     */
    private function matchesScope(array $note, int $projectId): bool
    {
        return (string) ($note['module_slug'] ?? '') === 'meeting_notes_project'
            && (string) ($note['scope_type'] ?? '') === 'project'
            && (int) ($note['scope_id'] ?? 0) === $projectId;
    }

    /**
     * @return array<string,string>
     */
    private function validationRules(): array
    {
        return [
            'title' => 'required|max_length[200]',
            'purpose' => 'permit_empty|max_length[5000]',
            'meeting_date' => 'required|valid_date[Y-m-d]',
            'meeting_type' => 'permit_empty|in_list[stand-up,planning,steering,review,retrospective,other]',
            'related_objective' => 'permit_empty|max_length[255]',
            'chair_user_id' => 'permit_empty|is_natural_no_zero',
            'minute_taker_user_id' => 'permit_empty|is_natural_no_zero',
            'attendees_text' => 'permit_empty|max_length[5000]',
            'absentees_text' => 'permit_empty|max_length[5000]',
            'agenda_text' => 'permit_empty|max_length[5000]',
            'discussion_text' => 'permit_empty|max_length[10000]',
            'decisions_text' => 'permit_empty|max_length[5000]',
            'raised_links_text' => 'permit_empty|max_length[5000]',
            'follow_up_date' => 'permit_empty|valid_date[Y-m-d]',
            'status' => 'permit_empty|in_list[draft,finalized,archived]',
            'lessons_learned' => 'permit_empty|max_length[5000]',
        ];
    }

    /**
     * @return int|null
     */
    private function sessionUserId(): ?int
    {
        $userId = session('user_id');
        if (! is_int($userId) && ! ctype_digit((string) $userId)) {
            return null;
        }

        return (int) $userId;
    }

    /**
     * @return string|null
     */
    private function nullableString(string $value): ?string
    {
        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    /**
     * @return string|null
     */
    private function nullableDate(string $value): ?string
    {
        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    /**
     * @return int|null
     */
    private function nullableInt(string $value): ?int
    {
        $trimmed = trim($value);
        if ($trimmed === '' || ! ctype_digit($trimmed)) {
            return null;
        }

        $int = (int) $trimmed;

        return $int > 0 ? $int : null;
    }

    /**
     * @return RedirectResponse
     */
    private function redirectModule(int $projectId): RedirectResponse
    {
        return redirect()->to('/projects/' . $projectId . '/modules/meeting-notes');
    }
}
