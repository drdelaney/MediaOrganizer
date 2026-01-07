<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?php
$movie = isset($movie) && is_array($movie) ? $movie : [];
$movieTags = isset($movieTags) && is_array($movieTags) ? $movieTags : [];
$allTags = isset($allTags) && is_array($allTags) ? $allTags : [];
?>

<div class="row">
    <div class="col-12">
        <!-- Header with Back Button -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">
                <i class="bi bi-film"></i> Movie Details
            </h1>
            <a href="<?= base_url('movies') ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Library
            </a>
        </div>

        <div class="row">
            <!-- Movie Poster -->
            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <div id="poster-container">
                            <?php if ($movie['poster_md5']): ?>
                                <img src="<?= base_url('movies/poster/' . $movie['movie_id']) . '?v=' . urlencode($movie['poster_md5']) ?>"
                                     class="img-fluid rounded movie-poster"
                                     alt="<?= esc($movie['title']) ?>"
                                     style="max-height: 400px;">
                            <?php else: ?>
                                <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                     style="height: 400px;">
                                    <i class="bi bi-film display-1 text-muted"></i>
                                </div>
                                <p class="text-muted mt-2">No poster available</p>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2 w-100" data-bs-toggle="modal" data-bs-target="#updatePosterModal">
                            <i class="bi bi-image"></i> Update Poster
                        </button>
                    </div>
                </div>
            </div>

            <!-- Movie Information -->
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0"><?= esc($movie['title'] ?: $movie['o_title'] ?: 'Untitled') ?></h3>
                        <?php if ($movie['title'] && $movie['o_title'] && $movie['title'] !== $movie['o_title']): ?>
                            <h6 class="text-muted">Original Title: <?= esc($movie['o_title']) ?></h6>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <h5><i class="bi bi-info-circle"></i> Basic Information</h5>
                                <table class="table table-sm">
                                    <tr>
                                        <th style="width: 120px;">Number:</th>
                                        <td><?= $movie['number'] ? esc($movie['number']) : '<span class="text-muted">-</span>' ?></td>
                                    </tr>
                                    <tr>
                                        <th>Director:</th>
                                        <td><?= $movie['director'] ? esc($movie['director']) : '<span class="text-muted">-</span>' ?></td>
                                    </tr>
                                    <tr>
                                        <th>Year:</th>
                                        <td><?= $movie['year'] ? esc($movie['year']) : '<span class="text-muted">-</span>' ?></td>
                                    </tr>
                                    <tr>
                                        <th>Runtime:</th>
                                        <td><?= $movie['runtime'] ? esc($movie['runtime']) . ' min' : '<span class="text-muted">-</span>' ?></td>
                                    </tr>
                                    <tr>
                                        <th>Genre:</th>
                                        <td><?= $movie['genre'] ? esc($movie['genre']) : '<span class="text-muted">-</span>' ?></td>
                                    </tr>
                                    <tr>
                                        <th>Country:</th>
                                        <td><?= $movie['country'] ? esc($movie['country']) : '<span class="text-muted">-</span>' ?></td>
                                    </tr>
                                    <tr>
                                        <th>Studio:</th>
                                        <td><?= $movie['studio'] ? esc($movie['studio']) : '<span class="text-muted">-</span>' ?></td>
                                    </tr>
                                    <tr>
                                        <th>Classification (MPAA/TV):</th>
                                        <td><?= $movie['classification'] ? esc($movie['classification']) : '<span class="text-muted">-</span>' ?></td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Technical Information -->
                            <div class="col-md-6">
                                <h5><i class="bi bi-gear"></i> Technical Details</h5>
                                <?php 
                                // Get all medium formats from notes
                                $mediumIds = get_medium_ids_from_notes($movie['notes'] ?? null);
                                $mediumNames = [];
                                if (!empty($mediumIds)) {
                                    $mediaModel = new \App\Models\MovieModel();
                                    $allMedia = $mediaModel->getMediaTypes();
                                    foreach ($mediumIds as $mediumId) {
                                        foreach ($allMedia as $media) {
                                            if ($media['medium_id'] == $mediumId) {
                                                $mediumNames[] = $media['name'];
                                                break;
                                            }
                                        }
                                    }
                                }
                                // Fallback to single medium_name if no IDs in notes
                                if (empty($mediumNames) && !empty($movie['medium_name'])) {
                                    $mediumNames = [$movie['medium_name']];
                                }
                                $mediumDisplay = !empty($mediumNames) ? implode(', ', array_map('esc', $mediumNames)) : '<span class="text-muted">-</span>';
                                $mediumName = strtolower($movie['medium_name'] ?? ''); 
                                $isDigital = (strpos($mediumName, 'digital') !== false || strpos($mediumName, 'file') !== false); 
                                ?>
                                <table class="table table-sm">
                                    <tr>
                                        <th style="width: 120px;">Media Formats:</th>
                                        <td><?= $mediumDisplay ?></td>
                                    </tr>
                                    <?php if ($isDigital): ?>
                                    <tr>
                                        <th>Video Codec:</th>
                                        <td><?= $movie['vcodec_name'] ? esc($movie['vcodec_name']) : '<span class="text-muted">-</span>' ?></td>
                                    </tr>
                                    <tr>
                                        <th>Ratio:</th>
                                        <td><?= $movie['ratio_name'] ? esc($movie['ratio_name']) : '<span class="text-muted">-</span>' ?></td>
                                    </tr>
                                    <tr>
                                        <th>Resolution:</th>
                                        <td><?= ($movie['width'] && $movie['height']) ? esc($movie['width']) . ' x ' . esc($movie['height']) : '<span class="text-muted">-</span>' ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <th>Layers:</th>
                                        <td><?= $movie['layers'] ? esc($movie['layers']) : '<span class="text-muted">-</span>' ?></td>
                                    </tr>
                                    <tr>
                                        <th>Region:</th>
                                        <td><?= $movie['region'] ? esc($movie['region']) : '<span class="text-muted">-</span>' ?></td>
                                    </tr>
                                    <tr>
                                        <th>Media Number:</th>
                                        <td><?= $movie['media_num'] ? esc($movie['media_num']) : '<span class="text-muted">-</span>' ?></td>
                                    </tr>
                                    <tr>
                                        <th>Barcode:</th>
                                        <td><?= $movie['barcode'] ? esc($movie['barcode']) : '<span class="text-muted">-</span>' ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Status and Rating -->
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <h5><i class="bi bi-star"></i> Rating & Status</h5>
                                <p>
                                    <strong>Rating:</strong>
                                    <?php if ($movie['rating']): ?>
                                        <span class="rating-stars">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <?php if ($i <= $movie['rating']): ?>
                                                    <i class="bi bi-star-fill"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-star"></i>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                            (<?= $movie['rating'] ?>/5)
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Not rated</span>
                                    <?php endif; ?>
                                </p>
                                <p>
                                    <strong>Status:</strong>
                                    <span id="status-badges">
                                        <?php if ($movie['seen']): ?>
                                            <span class="badge bg-success"><i class="bi bi-eye"></i> Seen</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning"><i class="bi bi-eye-slash"></i> Unseen</span>
                                        <?php endif; ?>
                                        
                                        <?php if ($movie['loaned']): ?>
                                            <span class="badge bg-danger"><i class="bi bi-person-check"></i> Loaned</span>
                                        <?php endif; ?>
                                    </span>
                                    
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-<?= $movie['seen'] ? 'warning' : 'success' ?> ms-2" 
                                            id="toggle-seen-btn"
                                            data-movie-id="<?= $movie['movie_id'] ?>"
                                            data-seen="<?= $movie['seen'] ?>">
                                        <i class="bi bi-<?= $movie['seen'] ? 'eye-slash' : 'eye-fill' ?>"></i> 
                                        <?= $movie['seen'] ? 'Mark as Unseen' : 'Mark as Seen' ?>
                                    </button>
                                </p>
                                <p>
                                    <strong>Condition:</strong>
                                    <?= $movie['cond'] ? esc($movie['cond']) : '<span class="text-muted">-</span>' ?>
                                </p>
                                <p>
                                    <strong>Color:</strong>
                                    <?= $movie['color'] ? esc($movie['color']) : '<span class="text-muted">-</span>' ?>
                                </p>
                            </div>

                            <div class="col-md-6">
                                <h5><i class="bi bi-collection"></i> Collection Info</h5>
                                <p>
                                    <strong>Collection:</strong>
                                    <?= $movie['collection_name'] ? esc($movie['collection_name']) : '<span class="text-muted">None</span>' ?>
                                </p>
                                <p>
                                    <strong>Volume:</strong>
                                    <?= $movie['volume_name'] ? esc($movie['volume_name']) : '<span class="text-muted">None</span>' ?>
                                </p>
                                <p>
                                    <strong>Created:</strong>
                                    <?= $movie['created'] ? date('M d, Y H:i', strtotime($movie['created'])) : '<span class="text-muted">-</span>' ?>
                                </p>
                                <p>
                                    <strong>Updated:</strong>
                                    <?= $movie['updated'] ? date('M d, Y H:i', strtotime($movie['updated'])) : '<span class="text-muted">-</span>' ?>
                                </p>
                            </div>
                        </div>

                        <!-- Tags -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <h5><i class="bi bi-tags"></i> Tags</h5>
                                <div id="tags-container">
                                    <?php if (!empty($movieTags)): ?>
                                        <?php foreach ($movieTags as $tag): ?>
                                            <a href="<?= base_url('database-maintenance/tag/movies/' . $tag['tag_id']) ?>"
                                               class="badge bg-primary me-1 mb-1 text-decoration-none">
                                                <i class="bi bi-tag"></i> <?= esc($tag['name']) ?>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted">No tags assigned</span>
                                    <?php endif; ?>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2" data-bs-toggle="modal" data-bs-target="#manageTagsModal">
                                    <i class="bi bi-tags"></i> Manage Tags
                                </button>
                            </div>
                        </div>

                        <!-- Web Links -->
                        <?php if ($movie['site'] || $movie['o_site'] || $movie['trailer']): ?>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h5><i class="bi bi-link-45deg"></i> Web Links</h5>
                                    <?php if ($movie['site']): ?>
                                        <p><strong>Site:</strong> <a href="<?= esc($movie['site']) ?>" target="_blank" class="text-decoration-none"><?= esc($movie['site']) ?> <i class="bi bi-box-arrow-up-right"></i></a></p>
                                    <?php endif; ?>
                                    <?php if ($movie['o_site']): ?>
                                        <p><strong>Original Site:</strong> <a href="<?= esc($movie['o_site']) ?>" target="_blank" class="text-decoration-none"><?= esc($movie['o_site']) ?> <i class="bi bi-box-arrow-up-right"></i></a></p>
                                    <?php endif; ?>
                                    <?php if ($movie['trailer']): ?>
                                        <p><strong>Trailer:</strong> <a href="<?= esc($movie['trailer']) ?>" target="_blank" class="text-decoration-none"><?= esc($movie['trailer']) ?> <i class="bi bi-play-circle"></i></a></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Cast -->
                        <?php if ($movie['cast']): ?>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h5><i class="bi bi-people"></i> Cast</h5>
                                    <div class="bg-light p-3 rounded">
                                        <?= nl2br(esc($movie['cast'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Plot -->
                        <?php if ($movie['plot']): ?>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h5><i class="bi bi-journal-text"></i> Plot</h5>
                                    <div class="bg-light p-3 rounded">
                                        <?= nl2br(esc($movie['plot'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Notes -->
                        <?php if ($movie['notes']): ?>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h5><i class="bi bi-sticky"></i> Notes</h5>
                                    <div class="bg-light p-3 rounded">
                                        <?= nl2br(esc($movie['notes'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Additional Credits -->
                        <?php if ($movie['screenplay'] || $movie['cameraman']): ?>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h5><i class="bi bi-camera-reels"></i> Additional Credits</h5>
                                    <?php if ($movie['screenplay']): ?>
                                        <p><strong>Screenplay:</strong> <?= esc($movie['screenplay']) ?></p>
                                    <?php endif; ?>
                                    <?php if ($movie['cameraman']): ?>
                                        <p><strong>Cameraman:</strong> <?= esc($movie['cameraman']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mt-4">
            <div class="col-12 text-center">
                <a href="<?= base_url('movies') ?>" class="btn btn-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Back to Library
                </a>
                <a href="<?= base_url('movies/edit/' . $movie['movie_id']) ?>" class="btn btn-primary me-2">
                    <i class="bi bi-pencil"></i> Edit Movie
                </a>
                <button type="button" class="btn btn-danger" onclick="confirmDelete(<?= $movie['movie_id'] ?>, '<?= esc(addslashes($movie['title'] ?: $movie['o_title'] ?: 'this movie'), 'js') ?>')">
                    <i class="bi bi-trash"></i> Delete Movie
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Manage Tags Modal -->
<div class="modal fade" id="manageTagsModal" tabindex="-1" aria-labelledby="manageTagsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="manageTagsModalLabel"><i class="bi bi-tags"></i> Manage Tags</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Select Tags for this Movie</label>
                    <div id="tag-checkboxes">
                        <?php if (!empty($allTags)): ?>
                            <?php
                            $selectedTagIds = array_column($movieTags, 'tag_id');
                            foreach ($allTags as $tag):
                                $isChecked = in_array($tag['tag_id'], $selectedTagIds);
                            ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="tag_ids[]"
                                           value="<?= $tag['tag_id'] ?>" id="tag_<?= $tag['tag_id'] ?>"
                                           <?= $isChecked ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="tag_<?= $tag['tag_id'] ?>">
                                        <?= esc($tag['name']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No tags available. <a href="<?= base_url('tags') ?>" target="_blank">Create tags first</a>.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div id="tagMessage" class="alert" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveTagsBtn">
                    <i class="bi bi-save"></i> Save Tags
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Update Poster Modal -->
<div class="modal fade" id="updatePosterModal" tabindex="-1" aria-labelledby="updatePosterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updatePosterModalLabel">
                    <i class="bi bi-image"></i> Update Poster
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-3" id="posterTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tmdb-tab" data-bs-toggle="tab" data-bs-target="#tmdb-posters" type="button" role="tab">
                            <i class="bi bi-cloud-download"></i> TMDB Posters
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload-poster" type="button" role="tab">
                            <i class="bi bi-upload"></i> Upload Custom
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="posterTabContent">
                    <!-- TMDB Posters Tab -->
                    <div class="tab-pane fade show active" id="tmdb-posters" role="tabpanel">
                        <button type="button" class="btn btn-primary mb-3" id="fetchPostersBtn">
                            <i class="bi bi-search"></i> Fetch Posters from TMDB
                        </button>
                        <div id="posterLoadingSpinner" style="display: none;" class="text-center mb-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="text-muted mt-2">Fetching posters...</p>
                        </div>
                        <div id="posterMessage" class="alert" style="display: none;"></div>
                        <div id="posterGallery" class="row g-3"></div>
                    </div>

                    <!-- Upload Custom Poster Tab -->
                    <div class="tab-pane fade" id="upload-poster" role="tabpanel">
                        <form id="uploadPosterForm" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="posterFile" class="form-label">Choose Image File</label>
                                <input type="file" class="form-control" id="posterFile" name="poster_file" accept="image/*" required>
                                <div class="form-text">Supported formats: JPG, PNG, GIF. Image will be converted to JPEG.</div>
                            </div>
                            <div class="mb-3" id="uploadPreview" style="display: none;">
                                <label class="form-label">Preview</label>
                                <div class="text-center">
                                    <img id="uploadPreviewImg" src="" alt="Preview" class="img-fluid rounded" style="max-height: 300px;">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success" id="uploadPosterBtn">
                                <i class="bi bi-upload"></i> Upload and Save
                            </button>
                        </form>
                    </div>
                </div>

                <hr>
                <div class="text-center">
                    <button type="button" class="btn btn-outline-danger" id="clearPosterBtn">
                        <i class="bi bi-x-circle"></i> Remove Current Poster
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endsection() ?>

<?= $this->section('scripts') ?>
<style>
    .rating-stars {
        color: #ffc107;
    }
    .table th {
        border-top: none;
        font-weight: 600;
        color: #495057;
    }
    .table td {
        border-top: 1px solid #dee2e6;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle poster image error
    const posterImg = document.querySelector('.movie-poster');
    if (posterImg) {
        posterImg.addEventListener('error', function() {
            this.parentNode.innerHTML = '<div class=\'bg-light rounded d-flex align-items-center justify-content-center\' style=\'height: 400px;\'><i class=\'bi bi-film display-1 text-muted\'></i></div><p class=\'text-muted mt-2\'>No poster available</p>';
        });
    }

    const toggleBtn = document.getElementById('toggle-seen-btn');
    
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            const movieId = this.dataset.movieId;
            
            // Disable button during request
            this.disabled = true;
            const originalHtml = this.innerHTML;
            this.innerHTML = '<i class="bi bi-hourglass-split"></i> Updating...';

            fetch(`<?= base_url('movies/toggleSeen/') ?>${movieId}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update button
                    const newSeen = data.seen;
                    this.dataset.seen = newSeen ? '1' : '0';
                    this.className = `btn btn-sm btn-outline-${newSeen ? 'warning' : 'success'} ms-2`;
                    this.innerHTML = `<i class="bi bi-${newSeen ? 'eye-slash' : 'eye-fill'}"></i> ${newSeen ? 'Mark as Unseen' : 'Mark as Seen'}`;
                    
                    // Update status badge
                    const statusBadges = document.getElementById('status-badges');
                    const seenBadge = statusBadges.querySelector('.badge');
                    if (newSeen) {
                        seenBadge.className = 'badge bg-success';
                        seenBadge.innerHTML = '<i class="bi bi-eye"></i> Seen';
                    } else {
                        seenBadge.className = 'badge bg-warning';
                        seenBadge.innerHTML = '<i class="bi bi-eye-slash"></i> Unseen';
                    }
                    
                    // Show success message
                    showMessage(data.message, 'success');
                } else {
                    this.innerHTML = originalHtml;
                    showMessage(data.message, 'error');
                }
            })
            .catch(() => {
                this.innerHTML = originalHtml;
                showMessage('An error occurred while updating the movie status', 'error');
            })
            .finally(() => {
                this.disabled = false;
            });
        });
    }
});

function showMessage(message, type) {
    // Create a temporary alert
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.parentNode.removeChild(alertDiv);
        }
    }, 3000);
}

function confirmDelete(movieId, movieTitle) {
    if (confirm('Are you sure you want to delete "' + movieTitle + '"?\n\nThis action cannot be undone.')) {
        // Redirect to delete URL
        window.location.href = '<?= base_url('movies/delete/') ?>' + movieId;
    }
}

// Poster Update Functionality
const movieId = <?= $movie['movie_id'] ?>;
let selectedPosterUrl = null;

// Fetch posters from TMDB
document.getElementById('fetchPostersBtn').addEventListener('click', function() {
    const btn = this;
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Fetching...';

    document.getElementById('posterLoadingSpinner').style.display = 'block';
    document.getElementById('posterMessage').style.display = 'none';
    document.getElementById('posterGallery').innerHTML = '';

    fetch('<?= base_url('movies/fetchPosters/') ?>' + movieId, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('posterLoadingSpinner').style.display = 'none';

        if (data.success && data.posters && data.posters.length > 0) {
            displayPosterGallery(data.posters);
            showPosterMessage(data.message, 'success');
        } else {
            showPosterMessage(data.message || 'No posters found', 'warning');
        }
    })
    .catch(error => {
        document.getElementById('posterLoadingSpinner').style.display = 'none';
        showPosterMessage('Error fetching posters: ' + error.message, 'danger');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
});

// Display poster gallery
function displayPosterGallery(posters) {
    const gallery = document.getElementById('posterGallery');
    gallery.innerHTML = '';

    posters.forEach((poster, index) => {
        const col = document.createElement('div');
        col.className = 'col-md-4 col-sm-6';
        col.innerHTML = `
            <div class="card poster-option" style="cursor: pointer;" data-poster-url="${poster.url}">
                <img src="${poster.thumbnail}" class="card-img-top" alt="Poster ${index + 1}">
                <div class="card-body p-2 text-center">
                    <small class="text-muted">
                        ${poster.width} x ${poster.height}
                        ${poster.vote_average > 0 ? '⭐ ' + poster.vote_average.toFixed(1) : ''}
                    </small>
                </div>
            </div>
        `;
        gallery.appendChild(col);
    });

    // Add click handlers to poster options
    document.querySelectorAll('.poster-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.poster-option').forEach(o => o.classList.remove('border-primary', 'border-3'));
            this.classList.add('border-primary', 'border-3');
            selectedPosterUrl = this.dataset.posterUrl;
            savePoster(selectedPosterUrl);
        });
    });
}

// Save poster
function savePoster(posterUrl) {
    fetch('<?= base_url('movies/updatePoster/') ?>' + movieId, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'poster_url=' + encodeURIComponent(posterUrl)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            updatePosterDisplay(data.poster_url);
            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('updatePosterModal')).hide();
            }, 1000);
        } else {
            showMessage(data.message || 'Failed to update poster', 'error');
        }
    })
    .catch(error => {
        showMessage('Error updating poster: ' + error.message, 'error');
    });
}

// Upload custom poster
document.getElementById('uploadPosterForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const btn = document.getElementById('uploadPosterBtn');
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Uploading...';

    fetch('<?= base_url('movies/updatePoster/') ?>' + movieId, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            updatePosterDisplay(data.poster_url);
            document.getElementById('uploadPosterForm').reset();
            document.getElementById('uploadPreview').style.display = 'none';
            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('updatePosterModal')).hide();
            }, 1000);
        } else {
            showMessage(data.message || 'Failed to upload poster', 'error');
        }
    })
    .catch(error => {
        showMessage('Error uploading poster: ' + error.message, 'error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
});

// Preview uploaded file
document.getElementById('posterFile').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('uploadPreviewImg').src = event.target.result;
            document.getElementById('uploadPreview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});

// Clear poster
document.getElementById('clearPosterBtn').addEventListener('click', function() {
    if (!confirm('Are you sure you want to remove the current poster?')) {
        return;
    }

    const btn = this;
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Removing...';

    fetch('<?= base_url('movies/updatePoster/') ?>' + movieId, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'clear_poster=true'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            updatePosterDisplay(null);
            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('updatePosterModal')).hide();
            }, 1000);
        } else {
            showMessage(data.message || 'Failed to clear poster', 'error');
        }
    })
    .catch(error => {
        showMessage('Error clearing poster: ' + error.message, 'error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
});

// Update poster display on page
function updatePosterDisplay(posterUrl) {
    const container = document.getElementById('poster-container');
    if (posterUrl) {
        container.innerHTML = `
            <img src="${posterUrl}"
                 class="img-fluid rounded movie-poster"
                 alt="<?= esc($movie['title']) ?>"
                 style="max-height: 400px;">
        `;
    } else {
        container.innerHTML = `
            <div class="bg-light rounded d-flex align-items-center justify-content-center"
                 style="height: 400px;">
                <i class="bi bi-film display-1 text-muted"></i>
            </div>
            <p class="text-muted mt-2">No poster available</p>
        `;
    }
}

function showPosterMessage(message, type) {
    const msgDiv = document.getElementById('posterMessage');
    msgDiv.className = 'alert alert-' + type;
    msgDiv.textContent = message;
    msgDiv.style.display = 'block';
}

// Manage Tags
document.getElementById('saveTagsBtn')?.addEventListener('click', function() {
    const checkboxes = document.querySelectorAll('#tag-checkboxes input[type="checkbox"]:checked');
    const tagIds = Array.from(checkboxes).map(cb => cb.value);
    const btn = this;
    const originalHtml = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

    fetch('<?= base_url('movies/updateTags/' . $movie['movie_id']) ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'tag_ids[]=' + tagIds.join('&tag_ids[]=')
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showTagMessage(data.message, 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showTagMessage('Error: ' + data.message, 'error');
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    })
    .catch(error => {
        showTagMessage('Error: ' + error.message, 'error');
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
});

function showTagMessage(message, type) {
    const msgDiv = document.getElementById('tagMessage');
    msgDiv.className = 'alert alert-' + (type === 'success' ? 'success' : 'danger');
    msgDiv.textContent = message;
    msgDiv.style.display = 'block';
}
</script>
<?= $this->endsection() ?>
