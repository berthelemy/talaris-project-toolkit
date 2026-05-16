                    <div class="mt-3">
                        <div class="small text-muted mb-1">Related risks</div>
                        <?php $risks = (array) ($note['linked_risks'] ?? []); ?>
                        <?php if ($risks === []): ?>
                            <div class="text-muted small">No linked risk entries.</div>
                        <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($risks as $risk): ?>
                                    <li class="list-group-item px-0">
                                        <a href="<?= site_url('projects/' . (int) ($project['id'] ?? 0) . '/modules/risk-register') ?>#entry-<?= (int) ($risk['id'] ?? 0) ?>"><?= esc((string) ($risk['title'] ?? ('Risk #' . (int) ($risk['id'] ?? 0)))) ?></a>
                                        <span class="small text-muted">(<?= esc((string) ($risk['status'] ?? 'open')) ?>)</span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>

                    <div class="mt-3">
                        <div class="small text-muted mb-1">Related assumptions</div>
                        <?php $assumptions = (array) ($note['linked_assumptions'] ?? []); ?>
                        <?php if ($assumptions === []): ?>
                            <div class="text-muted small">No linked assumption entries.</div>
                        <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($assumptions as $assumption): ?>
                                    <li class="list-group-item px-0">
                                        <a href="<?= site_url('projects/' . (int) ($project['id'] ?? 0) . '/modules/assumptions-register') ?>#entry-<?= (int) ($assumption['id'] ?? 0) ?>"><?= esc((string) ($assumption['title'] ?? ('Assumption #' . (int) ($assumption['id'] ?? 0)))) ?></a>
                                        <span class="small text-muted">(<?= esc((string) ($assumption['status'] ?? 'open')) ?>)</span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>

                    <div class="mt-3">
                        <div class="small text-muted mb-1">Related issues</div>
                        <?php $issues = (array) ($note['linked_issues'] ?? []); ?>
                        <?php if ($issues === []): ?>
                            <div class="text-muted small">No linked issue entries.</div>
                        <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($issues as $issue): ?>
                                    <li class="list-group-item px-0">
                                        <a href="<?= site_url('projects/' . (int) ($project['id'] ?? 0) . '/modules/issue-tracker') ?>#entry-<?= (int) ($issue['id'] ?? 0) ?>"><?= esc((string) ($issue['title'] ?? ('Issue #' . (int) ($issue['id'] ?? 0)))) ?></a>
                                        <span class="small text-muted">(<?= esc((string) ($issue['status'] ?? 'open')) ?>)</span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>

                    <div class="mt-3">
                        <div class="small text-muted mb-1">Related dependencies</div>
                        <?php $dependencies = (array) ($note['linked_dependencies'] ?? []); ?>
                        <?php if ($dependencies === []): ?>
                            <div class="text-muted small">No linked dependency entries.</div>
                        <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($dependencies as $dependency): ?>
                                    <li class="list-group-item px-0">
                                        <a href="<?= site_url('projects/' . (int) ($project['id'] ?? 0) . '/modules/dependencies-register') ?>#entry-<?= (int) ($dependency['id'] ?? 0) ?>"><?= esc((string) ($dependency['title'] ?? ('Dependency #' . (int) ($dependency['id'] ?? 0)))) ?></a>
                                        <span class="small text-muted">(<?= esc((string) ($dependency['status'] ?? 'open')) ?>)</span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
<?php
/**
 * @var array<string,mixed> $project
 * @var int $scope_id
 * @var list<array<string,mixed>> $notes
 * @var list<array{id:int,username:string}> $owners
 * @var bool $tasks_module_enabled
 * @var bool $decisions_module_enabled
 * @var bool $is_read_only
 */

$pageTitle = 'Meeting Notes';
$active = 'projects';
?>
<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
    <div class="mb-3">
        <a class="btn btn-outline-secondary btn-sm" href="<?= site_url('projects/' . (int) ($project['id'] ?? 0)) ?>"><?= esc(lang('Module.backToProject')) ?></a>
    </div>

    <?php if (session('error') !== null): ?>
        <div class="alert alert-danger" role="alert"><?= esc((string) session('error')) ?></div>
    <?php endif; ?>
    <?php if (session('success') !== null): ?>
        <div class="alert alert-success" role="alert"><?= esc((string) session('success')) ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex justify-content-between align-items-center gap-2 flex-wrap">
            <div>
                <h2 class="h5 mb-1"><?= esc((string) ($project['name'] ?? '')) ?> - Meeting Notes</h2>
                <p class="mb-0 text-muted">Capture meeting records, decisions, and linked action items.</p>
            </div>
            <?php if (! $is_read_only): ?>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#meetingNotesModalAdd">Add meeting note</button>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($is_read_only): ?>
        <div class="alert alert-info" role="status"><?= esc(lang('Module.readOnlyNotice')) ?></div>
    <?php endif; ?>

    <?php if (empty($notes)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <p class="text-muted mb-0">No meeting notes yet.</p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($notes as $note): ?>
            <?php $noteId = (int) ($note['id'] ?? 0); ?>
            <?php $actions = (array) ($note['actions'] ?? []); ?>
            <?php $linkedDecisions = (array) ($note['linked_decisions'] ?? []); ?>
            <div class="card border-0 shadow-sm mb-3" id="note-<?= $noteId ?>">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <div>
                            <h3 class="h6 mb-1"><?= esc((string) ($note['title'] ?? '')) ?></h3>
                            <div class="small text-muted">
                                Date: <?= esc((string) ($note['meeting_date'] ?? '')) ?>
                                | Type: <?= esc((string) ($note['meeting_type'] ?? 'other')) ?>
                                | Status: <?= esc((string) ucfirst((string) ($note['status'] ?? 'draft'))) ?>
                            </div>
                        </div>
                        <?php if (! $is_read_only): ?>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#meeting-note-add-risk-<?= $noteId ?>">Add risk</button>
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#meeting-note-add-assumption-<?= $noteId ?>">Add assumption</button>
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#meeting-note-add-issue-<?= $noteId ?>">Add issue</button>
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#meeting-note-add-decision-<?= $noteId ?>">Add decision</button>
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#meeting-note-add-dependency-<?= $noteId ?>">Add dependency</button>
                                            <?php if (! $is_read_only): ?>
                                                <!-- Add Risk Modal -->
                                                <div class="modal fade" id="meeting-note-add-risk-<?= $noteId ?>" tabindex="-1">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Add Related Risk</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <form method="post" action="<?= site_url('projects/' . $scope_id . '/modules/meeting-notes/' . $noteId . '/risks') ?>">
                                                                <?= csrf_field() ?>
                                                                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                                                                    <div class="row g-3">
                                                                        <div class="col-12">
                                                                            <label class="form-label">Risk title</label>
                                                                            <input class="form-control" name="title" type="text" maxlength="200" required>
                                                                        </div>
                                                                        <div class="col-12">
                                                                            <label class="form-label">Risk description</label>
                                                                            <textarea class="form-control" name="description" rows="3" required></textarea>
                                                                        </div>
                                                                        <div class="col-12 col-md-6">
                                                                            <label class="form-label">Owner</label>
                                                                            <select class="form-select" name="owner_user_id">
                                                                                <option value=""></option>
                                                                                <?php foreach ($owners as $owner): ?>
                                                                                    <option value="<?= (int) ($owner['id'] ?? 0) ?>"><?= esc((string) ($owner['username'] ?? '')) ?></option>
                                                                                <?php endforeach; ?>
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-12 col-md-3">
                                                                            <label class="form-label">Status</label>
                                                                            <select class="form-select" name="status">
                                                                                <option value="open" selected>Open</option>
                                                                                <option value="in_progress">In progress</option>
                                                                                <option value="closed">Closed</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-12 col-md-3">
                                                                            <label class="form-label">Priority</label>
                                                                            <select class="form-select" name="priority">
                                                                                <option value="low">Low</option>
                                                                                <option value="medium" selected>Medium</option>
                                                                                <option value="high">High</option>
                                                                                <option value="critical">Critical</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-primary">Add risk</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Add Assumption Modal -->
                                                <div class="modal fade" id="meeting-note-add-assumption-<?= $noteId ?>" tabindex="-1">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Add Related Assumption</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <form method="post" action="<?= site_url('projects/' . $scope_id . '/modules/meeting-notes/' . $noteId . '/assumptions') ?>">
                                                                <?= csrf_field() ?>
                                                                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                                                                    <div class="row g-3">
                                                                        <div class="col-12">
                                                                            <label class="form-label">Assumption title</label>
                                                                            <input class="form-control" name="title" type="text" maxlength="200" required>
                                                                        </div>
                                                                        <div class="col-12">
                                                                            <label class="form-label">Assumption description</label>
                                                                            <textarea class="form-control" name="description" rows="3" required></textarea>
                                                                        </div>
                                                                        <div class="col-12 col-md-6">
                                                                            <label class="form-label">Owner</label>
                                                                            <select class="form-select" name="owner_user_id">
                                                                                <option value=""></option>
                                                                                <?php foreach ($owners as $owner): ?>
                                                                                    <option value="<?= (int) ($owner['id'] ?? 0) ?>"><?= esc((string) ($owner['username'] ?? '')) ?></option>
                                                                                <?php endforeach; ?>
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-12 col-md-3">
                                                                            <label class="form-label">Status</label>
                                                                            <select class="form-select" name="status">
                                                                                <option value="open" selected>Open</option>
                                                                                <option value="in_progress">In progress</option>
                                                                                <option value="closed">Closed</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-12 col-md-3">
                                                                            <label class="form-label">Priority</label>
                                                                            <select class="form-select" name="priority">
                                                                                <option value="low">Low</option>
                                                                                <option value="medium" selected>Medium</option>
                                                                                <option value="high">High</option>
                                                                                <option value="critical">Critical</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-primary">Add assumption</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Add Issue Modal -->
                                                <div class="modal fade" id="meeting-note-add-issue-<?= $noteId ?>" tabindex="-1">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Add Related Issue</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <form method="post" action="<?= site_url('projects/' . $scope_id . '/modules/meeting-notes/' . $noteId . '/issues') ?>">
                                                                <?= csrf_field() ?>
                                                                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                                                                    <div class="row g-3">
                                                                        <div class="col-12">
                                                                            <label class="form-label">Issue title</label>
                                                                            <input class="form-control" name="title" type="text" maxlength="200" required>
                                                                        </div>
                                                                        <div class="col-12">
                                                                            <label class="form-label">Issue description</label>
                                                                            <textarea class="form-control" name="description" rows="3" required></textarea>
                                                                        </div>
                                                                        <div class="col-12 col-md-6">
                                                                            <label class="form-label">Owner</label>
                                                                            <select class="form-select" name="owner_user_id">
                                                                                <option value=""></option>
                                                                                <?php foreach ($owners as $owner): ?>
                                                                                    <option value="<?= (int) ($owner['id'] ?? 0) ?>"><?= esc((string) ($owner['username'] ?? '')) ?></option>
                                                                                <?php endforeach; ?>
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-12 col-md-3">
                                                                            <label class="form-label">Status</label>
                                                                            <select class="form-select" name="status">
                                                                                <option value="open" selected>Open</option>
                                                                                <option value="in_progress">In progress</option>
                                                                                <option value="closed">Closed</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-12 col-md-3">
                                                                            <label class="form-label">Priority</label>
                                                                            <select class="form-select" name="priority">
                                                                                <option value="low">Low</option>
                                                                                <option value="medium" selected>Medium</option>
                                                                                <option value="high">High</option>
                                                                                <option value="critical">Critical</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-primary">Add issue</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Add Dependency Modal -->
                                                <div class="modal fade" id="meeting-note-add-dependency-<?= $noteId ?>" tabindex="-1">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Add Related Dependency</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <form method="post" action="<?= site_url('projects/' . $scope_id . '/modules/meeting-notes/' . $noteId . '/dependencies') ?>">
                                                                <?= csrf_field() ?>
                                                                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                                                                    <div class="row g-3">
                                                                        <div class="col-12">
                                                                            <label class="form-label">Dependency title</label>
                                                                            <input class="form-control" name="title" type="text" maxlength="200" required>
                                                                        </div>
                                                                        <div class="col-12">
                                                                            <label class="form-label">Dependency description</label>
                                                                            <textarea class="form-control" name="description" rows="3" required></textarea>
                                                                        </div>
                                                                        <div class="col-12 col-md-6">
                                                                            <label class="form-label">Owner</label>
                                                                            <select class="form-select" name="owner_user_id">
                                                                                <option value=""></option>
                                                                                <?php foreach ($owners as $owner): ?>
                                                                                    <option value="<?= (int) ($owner['id'] ?? 0) ?>"><?= esc((string) ($owner['username'] ?? '')) ?></option>
                                                                                <?php endforeach; ?>
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-12 col-md-3">
                                                                            <label class="form-label">Status</label>
                                                                            <select class="form-select" name="status">
                                                                                <option value="open" selected>Open</option>
                                                                                <option value="in_progress">In progress</option>
                                                                                <option value="closed">Closed</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-12 col-md-3">
                                                                            <label class="form-label">Priority</label>
                                                                            <select class="form-select" name="priority">
                                                                                <option value="low">Low</option>
                                                                                <option value="medium" selected>Medium</option>
                                                                                <option value="high">High</option>
                                                                                <option value="critical">Critical</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-primary">Add dependency</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#meeting-note-edit-<?= $noteId ?>">Edit</button>
                                <?php if ((string) ($note['status'] ?? '') !== 'closed'): ?>
                                    <form method="post" action="<?= site_url('projects/' . $scope_id . '/modules/meeting-notes/' . $noteId . '/close') ?>">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-outline-warning">Close</button>
                                    </form>
                                <?php endif; ?>
                                <form method="post" action="<?= site_url('projects/' . $scope_id . '/modules/meeting-notes/' . $noteId . '/delete') ?>" onsubmit="return confirm('Delete this meeting note?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-outline-danger">Delete</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ((string) ($note['purpose'] ?? '') !== ''): ?>
                        <p class="mt-3 mb-2"><strong>Purpose:</strong> <?= esc((string) ($note['purpose'] ?? '')) ?></p>
                    <?php endif; ?>

                    <div class="row g-3 mt-0">
                        <div class="col-12 col-md-6">
                            <div class="small text-muted">Chair</div>
                            <div><?= esc((string) ($note['chair_username'] ?? '')) ?></div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="small text-muted">Minute taker</div>
                            <div><?= esc((string) ($note['minute_taker_username'] ?? '')) ?></div>
                        </div>
                    </div>

                    <?php if ((string) ($note['attendees_text'] ?? '') !== ''): ?>
                        <div class="mt-3">
                            <div class="small text-muted">Attendees</div>
                            <div><?= nl2br(esc((string) ($note['attendees_text'] ?? ''))) ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ((string) ($note['agenda_text'] ?? '') !== ''): ?>
                        <div class="mt-3">
                            <div class="small text-muted">Agenda</div>
                            <div><?= nl2br(esc((string) ($note['agenda_text'] ?? ''))) ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ((string) ($note['discussion_text'] ?? '') !== ''): ?>
                        <div class="mt-3">
                            <div class="small text-muted">Discussion</div>
                            <div><?= nl2br(esc((string) ($note['discussion_text'] ?? ''))) ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ((string) ($note['decisions_text'] ?? '') !== ''): ?>
                        <div class="mt-3">
                            <div class="small text-muted">Decisions</div>
                            <div><?= nl2br(esc((string) ($note['decisions_text'] ?? ''))) ?></div>
                        </div>
                    <?php endif; ?>

                    <div class="mt-3">
                        <div class="small text-muted mb-1">Related decisions log entries</div>
                        <?php if ($linkedDecisions === []): ?>
                            <div class="text-muted small">No linked decision entries.</div>
                        <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($linkedDecisions as $linkedDecision): ?>
                                    <li class="list-group-item px-0">
                                        <a href="<?= site_url('projects/' . $scope_id . '/modules/decisions-register') ?>#entry-<?= (int) ($linkedDecision['id'] ?? 0) ?>"><?= esc((string) ($linkedDecision['title'] ?? ('Decision #' . (int) ($linkedDecision['id'] ?? 0)))) ?></a>
                                        <span class="small text-muted">(<?= esc((string) ($linkedDecision['status'] ?? 'draft')) ?>)</span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>

                    <div class="mt-3">
                        <div class="small text-muted mb-1">Action items</div>
                        <?php if ($actions === []): ?>
                            <div class="text-muted small">No action items.</div>
                        <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($actions as $action): ?>
                                    <li class="list-group-item px-0">
                                        <div class="fw-semibold"><?= esc((string) ($action['title'] ?? '')) ?></div>
                                        <div class="small text-muted">
                                            Status: <?= esc((string) ($action['status'] ?? 'open')) ?>
                                            <?php if ((string) ($action['owner_username'] ?? '') !== ''): ?>
                                                | Owner: <?= esc((string) ($action['owner_username'] ?? '')) ?>
                                            <?php endif; ?>
                                            <?php if ((string) ($action['due_date'] ?? '') !== ''): ?>
                                                | Due: <?= esc((string) ($action['due_date'] ?? '')) ?>
                                            <?php endif; ?>
                                            | <a href="<?= site_url('projects/' . $scope_id . '/modules/tasks-register') ?>#entry-<?= (int) ($action['id'] ?? 0) ?>">Open task</a>
                                        </div>
                                        <?php if ((string) ($action['description'] ?? '') !== ''): ?>
                                            <div class="small text-muted mt-1"><?= esc((string) ($action['description'] ?? '')) ?></div>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if (! $is_read_only): ?>
                <div class="modal fade" id="meeting-note-edit-<?= $noteId ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Meeting Note</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="post" action="<?= site_url('projects/' . $scope_id . '/modules/meeting-notes/' . $noteId . '/update') ?>">
                                <?= csrf_field() ?>
                                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label">Meeting title</label>
                                            <input class="form-control" name="title" type="text" maxlength="200" required value="<?= esc((string) ($note['title'] ?? '')) ?>">
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Meeting date</label>
                                            <input class="form-control" name="meeting_date" type="date" required value="<?= esc((string) ($note['meeting_date'] ?? '')) ?>">
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Meeting type</label>
                                            <select class="form-select" name="meeting_type">
                                                <?php foreach (['stand-up', 'planning', 'steering', 'review', 'retrospective', 'other'] as $meetingType): ?>
                                                    <option value="<?= esc($meetingType) ?>" <?= (string) ($note['meeting_type'] ?? 'other') === $meetingType ? 'selected' : '' ?>><?= esc(ucwords(str_replace('-', ' ', $meetingType))) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Status</label>
                                            <select class="form-select" name="status">
                                                <?php foreach (['draft', 'finalized', 'archived', 'closed'] as $status): ?>
                                                    <option value="<?= esc($status) ?>" <?= (string) ($note['status'] ?? 'draft') === $status ? 'selected' : '' ?>><?= esc(ucfirst($status)) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Follow-up date</label>
                                            <input class="form-control" name="follow_up_date" type="date" value="<?= esc((string) ($note['follow_up_date'] ?? '')) ?>">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Purpose</label>
                                            <textarea class="form-control" name="purpose" rows="2"><?= esc((string) ($note['purpose'] ?? '')) ?></textarea>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Chair</label>
                                            <select class="form-select" name="chair_user_id">
                                                <option value=""></option>
                                                <?php foreach ($owners as $owner): ?>
                                                    <option value="<?= (int) ($owner['id'] ?? 0) ?>" <?= (int) ($note['chair_user_id'] ?? 0) === (int) ($owner['id'] ?? 0) ? 'selected' : '' ?>><?= esc((string) ($owner['username'] ?? '')) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Minute taker</label>
                                            <select class="form-select" name="minute_taker_user_id">
                                                <option value=""></option>
                                                <?php foreach ($owners as $owner): ?>
                                                    <option value="<?= (int) ($owner['id'] ?? 0) ?>" <?= (int) ($note['minute_taker_user_id'] ?? 0) === (int) ($owner['id'] ?? 0) ? 'selected' : '' ?>><?= esc((string) ($owner['username'] ?? '')) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Attendees</label>
                                            <textarea class="form-control" name="attendees_text" rows="2"><?= esc((string) ($note['attendees_text'] ?? '')) ?></textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Absentees/apologies</label>
                                            <textarea class="form-control" name="absentees_text" rows="2"><?= esc((string) ($note['absentees_text'] ?? '')) ?></textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Agenda</label>
                                            <textarea class="form-control" name="agenda_text" rows="2"><?= esc((string) ($note['agenda_text'] ?? '')) ?></textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Discussion</label>
                                            <textarea class="form-control" name="discussion_text" rows="3"><?= esc((string) ($note['discussion_text'] ?? '')) ?></textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Decisions</label>
                                            <textarea class="form-control" name="decisions_text" rows="2"><?= esc((string) ($note['decisions_text'] ?? '')) ?></textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Risks/issues/dependencies raised</label>
                                            <textarea class="form-control" name="raised_links_text" rows="2"><?= esc((string) ($note['raised_links_text'] ?? '')) ?></textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Lessons learned</label>
                                            <textarea class="form-control" name="lessons_learned" rows="2"><?= esc((string) ($note['lessons_learned'] ?? '')) ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= esc(lang('Domain.cancelButton')) ?></button>
                                    <button type="submit" class="btn btn-primary">Save changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (! $is_read_only && $decisions_module_enabled): ?>
                <div class="modal fade" id="meeting-note-add-decision-<?= $noteId ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Add Related Decision</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="post" action="<?= site_url('projects/' . $scope_id . '/modules/meeting-notes/' . $noteId . '/decisions') ?>">
                                <?= csrf_field() ?>
                                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label">Decision title (optional)</label>
                                            <input class="form-control" name="title" type="text" maxlength="200">
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Decision date</label>
                                            <input class="form-control" name="decision_date" type="date" required value="<?= esc((string) ($note['meeting_date'] ?? '')) ?>">
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label">Status</label>
                                            <select class="form-select" name="status">
                                                <option value="draft" selected>Draft</option>
                                                <option value="proposed">Proposed</option>
                                                <option value="approved">Approved</option>
                                                <option value="implemented">Implemented</option>
                                                <option value="rejected">Rejected</option>
                                                <option value="superseded">Superseded</option>
                                                <option value="closed">Closed</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label">Priority</label>
                                            <select class="form-select" name="priority">
                                                <option value="low">Low</option>
                                                <option value="medium" selected>Medium</option>
                                                <option value="high">High</option>
                                                <option value="critical">Critical</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Owner</label>
                                            <select class="form-select" name="owner_user_id">
                                                <option value=""></option>
                                                <?php foreach ($owners as $owner): ?>
                                                    <option value="<?= (int) ($owner['id'] ?? 0) ?>"><?= esc((string) ($owner['username'] ?? '')) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Decision description</label>
                                            <textarea class="form-control" name="description" rows="3" required></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= esc(lang('Domain.cancelButton')) ?></button>
                                    <button type="submit" class="btn btn-primary">Add decision</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (! $is_read_only && $tasks_module_enabled): ?>
                <div class="modal fade" id="meeting-note-add-action-<?= $noteId ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Add Related Action</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="post" action="<?= site_url('projects/' . $scope_id . '/modules/meeting-notes/' . $noteId . '/actions') ?>">
                                <?= csrf_field() ?>
                                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label">Action title</label>
                                            <input class="form-control" name="title" type="text" maxlength="200" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Action description</label>
                                            <textarea class="form-control" name="description" rows="3"></textarea>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">Owner</label>
                                            <select class="form-select" name="owner_user_id">
                                                <option value=""></option>
                                                <?php foreach ($owners as $owner): ?>
                                                    <option value="<?= (int) ($owner['id'] ?? 0) ?>"><?= esc((string) ($owner['username'] ?? '')) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label">Status</label>
                                            <select class="form-select" name="status">
                                                <option value="open" selected>Open</option>
                                                <option value="in_progress">In progress</option>
                                                <option value="blocked">Blocked</option>
                                                <option value="in_review">In review</option>
                                                <option value="completed">Completed</option>
                                                <option value="cancelled">Cancelled</option>
                                                <option value="closed">Closed</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label">Priority</label>
                                            <select class="form-select" name="priority">
                                                <option value="low">Low</option>
                                                <option value="medium" selected>Medium</option>
                                                <option value="high">High</option>
                                                <option value="critical">Critical</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-2">
                                            <label class="form-label">Due date</label>
                                            <input class="form-control" name="due_date" type="date">
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= esc(lang('Domain.cancelButton')) ?></button>
                                    <button type="submit" class="btn btn-primary">Add action</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (! $is_read_only): ?>
        <?php include __DIR__ . '/_add_modal.php'; ?>
    <?php endif; ?>
<?= $this->endSection() ?>