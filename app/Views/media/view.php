<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?php
$movie = isset($movie) && is_array($movie) ? $movie : [];
$mediaTags = isset($mediaTags) && is_array($mediaTags) ? $mediaTags : [];
$allTags = isset($allTags) && is_array($allTags) ? $allTags : [];
?>

<div class="row">
    <div class="col-12">
        <!-- Header with Back Button -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">
                <i class="bi bi-film"></i> Media Details
            </h1>
            <div class="d-flex gap-2">
                <a href="<?= base_url('media') ?>" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Library
                </a>
                <button type="button" class="btn btn-info me-2 text-white" data-bs-toggle="modal" data-bs-target="#loanHistoryModal">
                    <i class="bi bi-clock-history"></i> Loan History
                </button>
                <a href="<?= base_url('media/add') ?>" class="btn btn-success" title="Add Media">
                    <i class="bi bi-plus-circle"></i> Add
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Media Poster -->
            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <div id="poster-container">
                            <?php if ($movie['poster_md5']): ?>
                                <img src="<?= base_url('media/poster/' . $movie['movie_id']) . '?v=' . urlencode($movie['poster_md5']) ?>"
                                     class="img-fluid rounded movie-poster"
                                     alt="<?= esc($movie['title'] ?: $movie['o_title']) ?>"
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

            <!-- Media Information -->
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <?php 
                        $isWishlist = false;
                        foreach ($mediaTags as $tag) {
                            if (strtolower($tag['name']) === 'wishlist') {
                                $isWishlist = true;
                                break;
                            }
                        }
                        ?>
                        <h3 class="mb-0">
                            <?php if ($isWishlist): ?>
                                <i class="bi bi-heart-fill text-info me-1" title="Wishlist"></i>
                            <?php endif; ?>
                            <?= esc($movie['title'] ?: $movie['o_title'] ?: 'Untitled') ?>
                        </h3>
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
                                    $mediaModel = new \App\Models\MediaModel();
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

                                        <?php 
                                        $isWishlist = false;
                                        if (isset($mediaTags) && is_array($mediaTags)) {
                                            foreach ($mediaTags as $tag) {
                                                if (strtolower($tag['name']) === 'wishlist') {
                                                    $isWishlist = true;
                                                    break;
                                                }
                                            }
                                        }
                                        ?>
                                        
                                        <?php if ($movie['loaned']): ?>
                                            <span class="badge bg-danger"><i class="bi bi-person-check"></i> Loaned</span>
                                        <?php elseif ($isWishlist): ?>
                                            <span class="badge bg-info"><i class="bi bi-heart"></i> Wishlist</span>
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
                                    <?= $movie['created'] ? user_date($movie['created']) : '<span class="text-muted">-</span>' ?>
                                </p>
                                <p>
                                    <strong>Updated:</strong>
                                    <?= $movie['updated'] ? user_date($movie['updated']) : '<span class="text-muted">-</span>' ?>
                                </p>
                            </div>
                        </div>

                        <!-- Tags -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <h5><i class="bi bi-tags"></i> Tags</h5>
                                <div id="tags-container">
                                    <?php if (!empty($mediaTags)): ?>
                                        <?php foreach ($mediaTags as $tag): ?>
                                            <a href="<?= base_url('media?tag=' . $tag['tag_id']) ?>"
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
                <a href="<?= base_url('media') ?>" class="btn btn-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Back to Library
                </a>
                <a href="<?= base_url('media/edit/' . $movie['movie_id']) ?>" class="btn btn-primary me-2">
                    <i class="bi bi-pencil"></i> Edit Media
                </a>
                <button type="button" class="btn btn-info me-2 text-white" data-bs-toggle="modal" data-bs-target="#loanHistoryModal">
                    <i class="bi bi-clock-history"></i> Loan History
                </button>
                <button type="button" class="btn btn-danger" onclick="confirmDelete(<?= $movie['movie_id'] ?>, '<?= esc($movie['title'] ?: $movie['o_title'] ?: 'this media', 'js') ?>')">
                    <i class="bi bi-trash"></i> Delete Media
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
                            $selectedTagIds = array_column($mediaTags, 'tag_id');
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
                            <i class="bi bi-cloud-download"></i> Online Posters
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload-poster" type="button" role="tab">
                            <i class="bi bi-upload"></i> Upload Custom
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="posterTabContent">
                    <!-- Online Posters Tab -->
                    <div class="tab-pane fade show active" id="tmdb-posters" role="tabpanel">
                        <div class="row g-2 mb-3 align-items-end">
                            <div class="col-md-4">
                                <label for="posterSource" class="form-label small">Search Location</label>
                                <select class="form-select" id="posterSource">
                                    <option value="">Auto-detect</option>
                                    <?php if (isset($enabledLookups) && in_array('TMDB', $enabledLookups)): ?>
                                        <option value="TMDB">TMDB (Movies/TV)</option>
                                    <?php endif; ?>
                                    <?php if (isset($enabledLookups) && in_array('TVDB', $enabledLookups)): ?>
                                        <option value="TVDB">TVDB (TV Shows)</option>
                                    <?php endif; ?>
                                    <?php if (isset($enabledLookups) && in_array('IGDB', $enabledLookups)): ?>
                                        <option value="IGDB">IGDB (Games)</option>
                                    <?php endif; ?>
                                    <?php if (isset($enabledLookups) && in_array('MusicBrainz', $enabledLookups)): ?>
                                        <option value="MusicBrainz">MusicBrainz (Music)</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="posterSearchTerm" class="form-label small">Search Term (optional)</label>
                                <input type="text" class="form-control" id="posterSearchTerm" placeholder="Enter title to search..." value="<?= esc($movie['title'] ?: $movie['o_title'] ?: '') ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-primary w-100" id="fetchPostersBtn">
                                    <i class="bi bi-search"></i> Fetch
                                </button>
                            </div>
                        </div>
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
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label for="posterFile" class="form-label">Choose Image File</label>
                                <input type="file" class="form-control" id="posterFile" name="poster_file" accept="image/*">
                                <div class="form-text">Supported formats: JPG, PNG, GIF. Image will be converted to JPEG.</div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <hr class="flex-grow-1">
                                    <span class="mx-2 text-muted small">OR</span>
                                    <hr class="flex-grow-1">
                                </div>
                                <label for="posterUrlInput" class="form-label">Provide Image URL</label>
                                <input type="url" class="form-control" id="posterUrlInput" name="poster_url" placeholder="https://example.com/image.jpg">
                                <div class="form-text">Direct link to an image file.</div>
                            </div>

                            <div class="mb-3" id="uploadPreview" style="display: none;">
                                <label class="form-label">Preview</label>
                                <div class="text-center">
                                    <img id="uploadPreviewImg" src="" alt="Preview" class="img-fluid rounded" style="max-height: 300px;">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success" id="uploadPosterBtn">
                                <i class="bi bi-save"></i> Save Poster
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

<!-- Loan History Modal -->
<div class="modal fade" id="loanHistoryModal" tabindex="-1" aria-labelledby="loanHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loanHistoryModalLabel">
                    <i class="bi bi-clock-history"></i> Loan History: <?= esc($movie['title'] ?: $movie['o_title']) ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="loanHistoryLoading" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading loan history...</p>
                </div>
                <div id="loanHistoryContent" style="display: none;">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Person</th>
                                    <th>Loaned Date</th>
                                    <th>Returned Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="loanHistoryTableBody">
                                <!-- Data will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div id="loanHistoryEmpty" class="text-center py-4" style="display: none;">
                    <i class="bi bi-info-circle display-4 text-muted"></i>
                    <p class="mt-2 text-muted">No Loaned History for the Media</p>
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

            fetch(`<?= base_url('media/toggleSeen/') ?>${movieId}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                if (response.status === 401) return;
                if (!response.ok && response.status === 403) {
                    throw new Error('CSRF validation failed. Please refresh the page.');
                }
                return response.json();
            })
            .then(data => {
                if (!data) return;
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
                showMessage('An error occurred while updating the media status', 'error');
            })
            .finally(() => {
                this.disabled = false;
            });
        });
    }

    // Loan History Modal handling
    const loanHistoryModal = document.getElementById('loanHistoryModal');
    if (loanHistoryModal) {
        loanHistoryModal.addEventListener('show.bs.modal', function() {
            loadLoanHistory();
        });
    }

    function loadLoanHistory() {
        const loading = document.getElementById('loanHistoryLoading');
        const content = document.getElementById('loanHistoryContent');
        const empty = document.getElementById('loanHistoryEmpty');
        const tableBody = document.getElementById('loanHistoryTableBody');

        loading.style.display = 'block';
        content.style.display = 'none';
        empty.style.display = 'none';
        tableBody.innerHTML = '';

        fetch('<?= base_url('media/loanHistory/' . $movie['movie_id']) ?>', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            loading.style.display = 'none';
            if (data && data.length > 0) {
                data.forEach(loan => {
                    const row = document.createElement('tr');
                    
                    const statusBadge = loan.return_date 
                        ? '<span class="badge bg-success">Returned</span>' 
                        : '<span class="badge bg-warning text-dark">Currently Loaned</span>';
                    
                    let actionButton = '';
                    if (!loan.return_date) {
                        actionButton = `
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-outline-success" onclick="returnMediaFromHistory(${loan.movie_id})" title="Return">
                                    <i class="bi bi-arrow-left-circle"></i> Return
                                </button>
                                <button class="btn btn-sm btn-outline-primary" onclick="sendReminder(${loan.loan_id})" title="Email Reminder">
                                    <i class="bi bi-envelope"></i>
                                </button>
                            </div>
                        `;
                    }
                    
                    row.innerHTML = `
                        <td>
                            <div><strong>${escapeHtml(loan.person_name)}</strong></div>
                            <small class="text-muted">${escapeHtml(loan.email || '')}</small>
                        </td>
                        <td>${loan.date || 'N/A'}</td>
                        <td>${loan.return_date || '---'}</td>
                        <td>${statusBadge}</td>
                        <td>${actionButton}</td>
                    `;
                    tableBody.appendChild(row);
                });
                content.style.display = 'block';
            } else {
                empty.style.display = 'block';
            }
        })
        .catch(error => {
            loading.style.display = 'none';
            empty.style.display = 'block';
            empty.innerHTML = '<i class="bi bi-exclamation-triangle display-4 text-danger"></i><p class="mt-2 text-danger">Error loading history</p>';
            console.error('Error:', error);
        });
    }

    window.returnMediaFromHistory = function(mediaId) {
        if (!confirm('Are you sure you want to mark this media as returned?')) {
            return;
        }

        fetch('<?= base_url('media/returnLoan/') ?>' + mediaId, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showMessage(data.message, 'success');
                loadLoanHistory();
                // Optionally refresh the page or update UI parts that show loan status
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showMessage(data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An error occurred while returning the media.', 'danger');
        });
    }

    window.sendReminder = function(loanId) {
        if (!confirm('Send a reminder email to the borrower?')) {
            return;
        }

        const originalBtn = event.currentTarget;
        const originalHtml = originalBtn.innerHTML;
        originalBtn.disabled = true;
        originalBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

        fetch('<?= base_url('media/sendReminder/') ?>' + loanId, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showMessage(data.message, 'success');
            } else {
                showMessage(data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An error occurred while sending the reminder.', 'danger');
        })
        .finally(() => {
            originalBtn.disabled = false;
            originalBtn.innerHTML = originalHtml;
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
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

function confirmDelete(movieId, mediaTitle) {
    if (confirm('Are you sure you want to delete "' + mediaTitle + '"?\n\nThis action cannot be undone.')) {
        // Redirect to delete URL
        window.location.href = '<?= base_url('media/delete/') ?>' + movieId;
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

    const sourceSelect = document.getElementById('posterSource');
    const source = sourceSelect ? sourceSelect.value : '';
    const searchTermInput = document.getElementById('posterSearchTerm');
    const searchTerm = searchTermInput ? searchTermInput.value : '';

    fetch('<?= base_url('media/fetchPosters/') ?>' + movieId, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ source: source, searchTerm: searchTerm })
    })
    .then(response => {
        if (!response.ok && response.status === 403) {
            throw new Error('CSRF validation failed. Please refresh the page.');
        }
        return response.json();
    })
    .then(data => {
        document.getElementById('posterLoadingSpinner').style.display = 'none';

        if (data.success && data.posters && data.posters.length > 0) {
            // Update tab label if source is returned
            if (data.source) {
                const tab = document.getElementById('tmdb-tab');
                if (tab) {
                    tab.innerHTML = `<i class="bi bi-cloud-download"></i> ${data.source} Posters`;
                }
                const btn = document.getElementById('fetchPostersBtn');
                if (btn) {
                    btn.innerHTML = `<i class="bi bi-search"></i> Fetch Posters from ${data.source}`;
                }
            }
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
                        ${poster.width && poster.height ? poster.width + ' x ' + poster.height : 'invalid image'}
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
    fetch('<?= base_url('media/updatePoster/') ?>' + movieId, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'poster_url=' + encodeURIComponent(posterUrl)
    })
    .then(response => {
        if (!response.ok && response.status === 403) {
            throw new Error('CSRF validation failed. Please refresh the page.');
        }
        return response.json();
    })
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

    const fileInput = document.getElementById('posterFile');
    const urlInput = document.getElementById('posterUrlInput');

    if (!fileInput.files[0] && !urlInput.value.trim()) {
        showMessage('Please choose a file or provide a URL', 'error');
        return;
    }

    const formData = new FormData(this);
    const btn = document.getElementById('uploadPosterBtn');
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving...';

    fetch('<?= base_url('media/updatePoster/') ?>' + movieId, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
        },
        body: formData
    })
    .then(response => {
        if (response.status === 401) return;
        return response.json();
    })
    .then(data => {
        if (!data) return;
        if (data.success) {
            showMessage(data.message, 'success');
            updatePosterDisplay(data.poster_url);
            document.getElementById('uploadPosterForm').reset();
            document.getElementById('uploadPreview').style.display = 'none';
            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('updatePosterModal')).hide();
            }, 1000);
        } else {
            showMessage(data.message || 'Failed to update poster', 'error');
        }
    })
    .catch(error => {
        showMessage('Error updating poster: ' + error.message, 'error');
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
        document.getElementById('posterUrlInput').value = ''; // Clear URL if file chosen
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('uploadPreviewImg').src = event.target.result;
            document.getElementById('uploadPreview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});

// Preview URL image
document.getElementById('posterUrlInput').addEventListener('input', function(e) {
    const url = e.target.value.trim();
    if (url) {
        document.getElementById('posterFile').value = ''; // Clear file if URL provided
        document.getElementById('uploadPreviewImg').src = url;
        document.getElementById('uploadPreview').style.display = 'block';
    } else if (!document.getElementById('posterFile').files[0]) {
        document.getElementById('uploadPreview').style.display = 'none';
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

    fetch('<?= base_url('media/updatePoster/') ?>' + movieId, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'clear_poster=true'
    })
    .then(response => {
        if (response.status === 401) return;
        return response.json();
    })
    .then(data => {
        if (!data) return;
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
                 alt="<?= esc($movie['title'] ?: $movie['o_title']) ?>"
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

    fetch('<?= base_url('media/updateMediaTags/' . $movie['movie_id']) ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'tag_ids[]=' + tagIds.join('&tag_ids[]=')
    })
    .then(response => {
        if (response.status === 401) return;
        return response.json();
    })
    .then(data => {
        if (!data) return;
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
