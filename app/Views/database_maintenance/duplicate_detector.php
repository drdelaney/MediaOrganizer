<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('database-maintenance') ?>">Database Maintenance</a></li>
                <li class="breadcrumb-item active" aria-current="page">Duplicate Detector</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="bi bi-files"></i> Duplicate Detector
            </h1>
            <a href="<?= base_url('database-maintenance/duplicate-detector') ?>" class="btn btn-primary">
                <i class="bi bi-arrow-clockwise"></i> Rescan
            </a>
        </div>

        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> This tool scans your library for potential duplicates based on <strong>Barcode</strong>, <strong>Lookup ID</strong>, or <strong>Normalized Title + Year</strong>.
        </div>

        <?php if (empty($duplicates)): ?>
            <div class="card shadow-sm mt-4">
                <div class="card-body text-center py-5">
                    <i class="bi bi-check-circle text-success display-1"></i>
                    <h3 class="mt-3">No duplicates found!</h3>
                    <p class="text-muted">Your library looks clean.</p>
                    <a href="<?= base_url('database-maintenance') ?>" class="btn btn-primary mt-3">
                        <i class="bi bi-arrow-left"></i> Back to Maintenance
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="mb-3">
                <span class="badge bg-warning text-dark fs-6"><?= count($duplicates) ?> potential duplicate groups found</span>
            </div>

            <?php foreach ($duplicates as $index => $group): ?>
                <div class="card shadow-sm mb-4 border-warning">
                    <div class="card-header bg-warning bg-opacity-10 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Group #<?= $index + 1 ?></h5>
                        <span class="badge bg-secondary"><?= count($group) ?> items</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 80px;">ID</th>
                                    <th>Title</th>
                                    <th>Year</th>
                                    <th>Barcode</th>
                                    <th>Details</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($group as $media): ?>
                                    <tr>
                                        <td><code>#<?= $media['movie_id'] ?></code></td>
                                        <td>
                                            <strong><?= esc($media['title']) ?></strong>
                                            <?php if ($media['o_title'] && $media['o_title'] !== $media['title']): ?>
                                                <br><small class="text-muted"><?= esc($media['o_title']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= esc($media['year']) ?></td>
                                        <td><?= esc($media['barcode'] ?: '-') ?></td>
                                        <td>
                                            <?php 
                                            $imdb = get_imdb_id_from_notes($media['notes']);
                                            $tmdb = get_tmdb_id_from_notes($media['notes']);
                                            ?>
                                            <?php if ($imdb): ?>
                                                <span class="badge bg-info text-dark" title="IMDB ID">IMDB: <?= esc($imdb) ?></span>
                                            <?php endif; ?>
                                            <?php if ($tmdb): ?>
                                                <span class="badge bg-primary" title="TMDB ID">TMDB: <?= esc($tmdb) ?></span>
                                            <?php endif; ?>
                                            <?php if (!$imdb && !$tmdb): ?>
                                                <small class="text-muted">No external IDs</small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= base_url('media/view/' . $media['movie_id']) ?>" class="btn btn-outline-primary" target="_blank" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="<?= base_url('media/edit/' . $media['movie_id']) ?>" class="btn btn-outline-warning" target="_blank" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-success" onclick="exemptMedia(<?= $media['movie_id'] ?>, '<?= esc(addslashes($media['title'])) ?>')" title="Exempt from duplicates">
                                                    <i class="bi bi-shield-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" onclick="deleteMedia(<?= $media['movie_id'] ?>, '<?= esc(addslashes($media['title'])) ?>')" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete <strong id="deleteMediaTitle"></strong>? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a id="confirmDeleteBtn" href="#" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>

<script>
function deleteMedia(id, title) {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    document.getElementById('deleteMediaTitle').textContent = title;
    document.getElementById('confirmDeleteBtn').href = '<?= base_url('media/delete') ?>/' + id;
    modal.show();
}

function exemptMedia(id, title) {
    if (confirm('Are you sure you want to exempt "' + title + '" from future duplicate lookups?')) {
        fetch('<?= base_url('media/exempt') ?>/' + id, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the row or reload
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Network error: ' + error.message);
        });
    }
}
</script>
<?= $this->endSection() ?>
