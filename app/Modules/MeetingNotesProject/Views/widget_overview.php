<?php
/**
 * @var array{draft:int,finalized:int,archived:int} $status_counts
 * @var array{stand-up:int,planning:int,steering:int,review:int,retrospective:int,other:int} $type_counts
 * @var int $scope_id
 * @var list<array{id:int,username:string}> $owners
 */
?>
<div class="card mb-4">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Meetings Overview</h5>
        <div class="btn-group btn-group-sm" role="group">
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#meetingNotesModalAdd">Add New</button>
            <a class="btn btn-outline-primary" href="<?= site_url('projects/' . $scope_id . '/modules/meeting-notes') ?>">Open Module</a>
        </div>
    </div>
    <div class="card-body">
        <h6 class="small text-muted text-uppercase mb-2">Status</h6>
        <div class="row g-2 text-center mb-3">
            <?php foreach ($status_counts as $status => $count): ?>
                <div class="col-6 col-md-3">
                    <div class="border rounded p-2 h-100">
                        <div class="small text-muted"><?= esc(ucfirst($status)) ?></div>
                        <div class="h5 mb-0"><?= (int) $count ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <h6 class="small text-muted text-uppercase mb-2">Meeting Type</h6>
        <div class="row g-2 text-center">
            <?php foreach ($type_counts as $type => $count): ?>
                <div class="col-6 col-md-4">
                    <div class="border rounded p-2 h-100">
                        <div class="small text-muted"><?= esc(ucwords(str_replace('-', ' ', $type))) ?></div>
                        <div class="h5 mb-0"><?= (int) $count ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/_add_modal.php'; ?>