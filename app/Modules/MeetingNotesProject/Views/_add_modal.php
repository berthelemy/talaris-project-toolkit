<?php
/**
 * @var int $scope_id
 * @var list<array{id:int,username:string}> $owners
 */
if (defined('MEETING_NOTES_WIDGET_ADD_MODAL_RENDERED')) {
    return;
}
define('MEETING_NOTES_WIDGET_ADD_MODAL_RENDERED', true);
?>
<div class="modal fade" id="meetingNotesModalAdd" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Meeting Note</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="<?= site_url('projects/' . $scope_id . '/modules/meeting-notes') ?>">
                <?= csrf_field() ?>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="meeting-note-title">Meeting title</label>
                            <input id="meeting-note-title" class="form-control" name="title" type="text" maxlength="200" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="meeting-note-date">Meeting date</label>
                            <input id="meeting-note-date" class="form-control" name="meeting_date" type="date" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="meeting-note-type">Meeting type</label>
                            <select id="meeting-note-type" class="form-select" name="meeting_type">
                                <option value="stand-up">Stand-up</option>
                                <option value="planning">Planning</option>
                                <option value="steering">Steering</option>
                                <option value="review">Review</option>
                                <option value="retrospective">Retrospective</option>
                                <option value="other" selected>Other</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="meeting-note-status">Status</label>
                            <select id="meeting-note-status" class="form-select" name="status">
                                <option value="draft" selected>Draft</option>
                                <option value="finalized">Finalized</option>
                                <option value="archived">Archived</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="meeting-note-follow-up-date">Follow-up date</label>
                            <input id="meeting-note-follow-up-date" class="form-control" name="follow_up_date" type="date">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="meeting-note-purpose">Purpose</label>
                            <textarea id="meeting-note-purpose" class="form-control" name="purpose" rows="2"></textarea>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="meeting-note-chair">Chair</label>
                            <select id="meeting-note-chair" class="form-select" name="chair_user_id">
                                <option value=""></option>
                                <?php foreach ($owners as $owner): ?>
                                    <option value="<?= (int) ($owner['id'] ?? 0) ?>"><?= esc((string) ($owner['username'] ?? '')) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="meeting-note-minute-taker">Minute taker</label>
                            <select id="meeting-note-minute-taker" class="form-select" name="minute_taker_user_id">
                                <option value=""></option>
                                <?php foreach ($owners as $owner): ?>
                                    <option value="<?= (int) ($owner['id'] ?? 0) ?>"><?= esc((string) ($owner['username'] ?? '')) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="meeting-note-attendees">Attendees</label>
                            <textarea id="meeting-note-attendees" class="form-control" name="attendees_text" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="meeting-note-agenda">Agenda items</label>
                            <textarea id="meeting-note-agenda" class="form-control" name="agenda_text" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="meeting-note-discussion">Discussion notes</label>
                            <textarea id="meeting-note-discussion" class="form-control" name="discussion_text" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="meeting-note-decisions">Decisions made</label>
                            <textarea id="meeting-note-decisions" class="form-control" name="decisions_text" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="meeting-note-raised-links">Risks/issues/dependencies raised</label>
                            <textarea id="meeting-note-raised-links" class="form-control" name="raised_links_text" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="meeting-note-lessons">Lessons learned</label>
                            <textarea id="meeting-note-lessons" class="form-control" name="lessons_learned" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= esc(lang('Domain.cancelButton')) ?></button>
                    <button type="submit" class="btn btn-primary">Create meeting note</button>
                </div>
            </form>
        </div>
    </div>
</div>