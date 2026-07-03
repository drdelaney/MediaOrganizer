<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?php
$media = isset($media) && is_array($media) ? $media : [];
$lookupOptions = isset($lookupOptions) ? $lookupOptions : [];
$mediaTypes = isset($mediaTypes) && is_array($mediaTypes) ? $mediaTypes : [];
$collections = isset($collections) && is_array($collections) ? $collections : [];
$volumes = isset($volumes) && is_array($volumes) ? $volumes : [];
$videoCodecs = isset($videoCodecs) && is_array($videoCodecs) ? $videoCodecs : [];
$ratios = isset($ratios) && is_array($ratios) ? $ratios : [];
$allTags = isset($allTags) && is_array($allTags) ? $allTags : [];
?>

<div class="row">
    <div class="col-12">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">
                <i class="bi bi-plus-circle"></i> Add Media Entry
            </h1>
            <a href="<?= base_url('media') ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Library
            </a>
        </div>

        <!-- Success/Error Messages -->
        <?php if (empty($lookupOptions)): ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>No Lookups Available!</strong> It seems no media lookup services are enabled or configured with required API keys. 
                Please visit <a href="<?= base_url('database-maintenance/manage-lookups') ?>" class="alert-link">Application Settings</a> to configure them.
            </div>
        <?php endif; ?>

        <div id="duplicateWarning" class="alert alert-warning alert-dismissible fade show" style="display:none;">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-4"></i>
                <div>
                    <strong>Potential duplicate detected!</strong> It looks like this media may already exist in your library.
                    <div id="duplicateLinks" class="mt-1"></div>
                </div>
            </div>
            <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'"></button>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="alert alert-danger">
                <h5>Please fix the following errors:</h5>
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Add Form -->
        <form action="<?= base_url('media/store') ?>" method="post" id="addMediaForm" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" id="selected_poster_url" name="selected_poster_url" value="">
            <input type="hidden" id="lookup_id_for_posters" name="lookup_id_for_posters" value="">
            <input type="hidden" id="media_type_for_posters" name="media_type_for_posters" value="">
            <!-- Lookup Section (Title / External IDs) -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="bi bi-search"></i> Media Lookup</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label for="lookup_type" class="form-label">Type</label>
                            <select class="form-select" id="lookup_type" name="lookup_type">
                                <?php foreach ($lookupOptions as $key => $option): ?>
                                    <?php if (in_array($key, $enabledLookups ?? [])): ?>
                                        <option value="<?= $key ?>" <?= old('lookup_type', array_key_first($lookupOptions)) === $key ? 'selected' : '' ?>><?= esc($option['label']) ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="lookup_title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="lookup_title" name="lookup_title" placeholder="Enter title to search" value="<?= esc(old('lookup_title')) ?>">
                        </div>
                        <div class="col-md-1">
                            <label for="lookup_year" class="form-label">Year</label>
                            <input type="number" class="form-control" id="lookup_year" name="lookup_year" min="1800" max="2099" placeholder="YYYY" value="<?= esc(old('lookup_year')) ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="lookup_id" class="form-label" id="lookup_id_label">ID</label>
                            <input type="text" class="form-control" id="lookup_id" name="lookup_id" placeholder="Select type first" value="">
                            <input type="hidden" id="imdb_id" name="imdb_id" value="<?= esc(old('imdb_id')) ?>">
                            <input type="hidden" id="tvdb_id" name="tvdb_id" value="<?= esc(old('tvdb_id')) ?>">
                            <input type="hidden" id="igdb_id" name="igdb_id" value="<?= esc(old('igdb_id')) ?>">
                            <input type="hidden" id="mbid" name="mbid" value="<?= esc(old('mbid')) ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="lookup_barcode" class="form-label">Barcode (UPC/EAN)</label>
                            <input type="text" class="form-control" id="lookup_barcode" name="lookup_barcode" placeholder="e.g. 883929401120" value="<?= esc(old('lookup_barcode')) ?>">
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2" id="lookup_help_text">
                        Search by Title, ID, or Barcode to auto-fill details.
                    </small>
                    <div class="mt-3 d-flex gap-2">
                        <button type="button" id="lookupPreviewBtn" class="btn btn-info">
                            <i class="bi bi-search"></i> Search & Preview
                        </button>
                        <small class="text-muted align-self-center">Preview details before applying to the form.</small>
                    </div>
                </div>
            </div>

            <!-- Preview Card (hidden until search) -->
            <div id="previewCard" class="card mb-4" style="display:none;">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-eye"></i> Preview from TMDB (Not Saved)</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3 text-center">
                            <div id="selectedPosterPreview">
                                <img id="previewPoster" src="" alt="Poster" class="img-fluid rounded shadow-sm" style="max-height:300px; display:none;">
                                <div id="previewPosterPlaceholder" class="bg-light rounded d-flex align-items-center justify-content-center" style="height:300px;">
                                    <span class="text-muted">No poster</span>
                                </div>
                            </div>
                            <button type="button" id="choosePosterBtn" class="btn btn-sm btn-outline-primary mt-2 w-100" style="display:none;">
                                <i class="bi bi-images"></i> Choose Different Poster
                            </button>
                        </div>
                        <div class="col-md-9">
                            <h4 id="previewTitle" class="mb-1"></h4>
                            <p id="previewSubtitle" class="text-muted"></p>
                            <p id="previewPlot" class="mt-3"></p>
                            <div class="mt-3">
                                <button type="button" id="applyPreviewBtn" class="btn btn-primary">
                                    <i class="bi bi-clipboard-check"></i> Apply to Form
                                </button>
                                <small class="text-muted ms-2">You can still edit any fields before saving.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Basic Information -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5><i class="bi bi-info-circle"></i> Basic Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title" value="<?= esc(old('title')) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="o_title" class="form-label">Original Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="o_title" name="o_title" value="<?= esc(old('o_title')) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="director" class="form-label">Director</label>
                                <input type="text" class="form-control" id="director" name="director" value="<?= esc(old('director')) ?>">
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="year" class="form-label">Year</label>
                                        <input type="number" class="form-control" id="year" name="year" min="1800" max="2099" value="<?= esc(old('year')) ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="runtime" class="form-label">Runtime (minutes)</label>
                                        <input type="number" class="form-control" id="runtime" name="runtime" min="0" value="<?= esc(old('runtime')) ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="rating" class="form-label">Rating (1-5)</label>
                                        <select class="form-select" id="rating" name="rating">
                                            <option value="">No Rating</option>
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <option value="<?= $i ?>" <?= old('rating') == $i ? 'selected' : '' ?>><?= $i ?> Star<?= $i > 1 ? 's' : '' ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="genre" class="form-label">Genre</label>
                                <input type="text" class="form-control" id="genre" name="genre" value="<?= esc(old('genre')) ?>">
                            </div>
                            <div class="mb-3">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" class="form-control" id="country" name="country" value="<?= esc(old('country')) ?>">
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="studio" class="form-label">Studio</label>
                                        <input type="text" class="form-control" id="studio" name="studio" value="<?= esc(old('studio')) ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="classification" class="form-label">Classification (MPAA/TV rating)</label>
                                        <input type="text" class="form-control" id="classification" name="classification" value="<?= esc(old('classification')) ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Technical & Collection Info -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5><i class="bi bi-gear"></i> Technical & Collection Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Media Formats <span class="text-danger">*</span> <small class="text-muted">(select at least one)</small></label>
                                <div class="border rounded p-2 <?= empty(old('medium_ids')) ? 'border-danger' : '' ?>" style="max-height: 200px; overflow-y: auto;" id="mediaFormatsContainer">
                                    <?php foreach ($mediaTypes as $medium): ?>
                                        <div class="form-check">
                                            <input class="form-check-input medium-checkbox" type="checkbox" 
                                                   id="medium_<?= $medium['medium_id'] ?>" 
                                                   name="medium_ids[]" 
                                                   value="<?= $medium['medium_id'] ?>"
                                                   <?= is_array(old('medium_ids')) && in_array($medium['medium_id'], old('medium_ids')) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="medium_<?= $medium['medium_id'] ?>">
                                                <?= esc($medium['name']) ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <small class="text-muted">The highest format will be stored as the primary medium.</small>
                                <div id="mediaFormatError" class="invalid-feedback <?= empty(old('medium_ids')) ? 'd-block' : '' ?>">
                                    Please select at least one media format.
                                </div>
                            </div>
                            <script>
                                // Initialize validation state if redirected back with errors
                                document.addEventListener('DOMContentLoaded', function() {
                                    const form = document.getElementById('addMediaForm');
                                    if (<?= !empty($errors) ? 'true' : 'false' ?>) {
                                        form.dataset.validated = 'true';
                                    }
                                });
                            </script>
                            <div class="mb-3">
                                <label for="collection_id" class="form-label">Collection</label>
                                <div class="input-group">
                                    <select class="form-select" id="collection_id" name="collection_id">
                                        <option value="">No Collection</option>
                                        <?php foreach ($collections as $collection): ?>
                                            <option value="<?= $collection['collection_id'] ?>" <?= old('collection_id') == $collection['collection_id'] ? 'selected' : '' ?>><?= esc($collection['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="btn btn-outline-secondary" type="button" id="addNewCollectionBtn" title="Add New Collection">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="volume_id" class="form-label">Volume</label>
                                <div class="input-group">
                                    <select class="form-select" id="volume_id" name="volume_id">
                                        <option value="">No Volume</option>
                                        <?php foreach ($volumes as $volume): ?>
                                            <option value="<?= $volume['volume_id'] ?>" <?= old('volume_id') == $volume['volume_id'] ? 'selected' : '' ?>><?= esc($volume['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="btn btn-outline-secondary" type="button" id="addNewVolumeBtn" title="Add New Volume">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="vcodec_id" class="form-label">Video Codec</label>
                                <select class="form-select" id="vcodec_id" name="vcodec_id">
                                    <option value="">Select Video Codec</option>
                                    <?php foreach ($videoCodecs as $codec): ?>
                                        <option value="<?= $codec['vcodec_id'] ?>" <?= old('vcodec_id') == $codec['vcodec_id'] ? 'selected' : '' ?>><?= esc($codec['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="ratio_id" class="form-label">Aspect Ratio</label>
                                <select class="form-select" id="ratio_id" name="ratio_id">
                                    <option value="">Select Aspect Ratio</option>
                                    <?php foreach ($ratios as $ratio): ?>
                                        <option value="<?= $ratio['ratio_id'] ?>" <?= old('ratio_id') == $ratio['ratio_id'] ? 'selected' : '' ?>><?= esc($ratio['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="width" class="form-label">Width (pixels)</label>
                                        <input type="number" class="form-control" id="width" name="width" min="0" value="<?= esc(old('width')) ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="height" class="form-label">Height (pixels)</label>
                                        <input type="number" class="form-control" id="height" name="height" min="0" value="<?= esc(old('height')) ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="barcode" class="form-label">Barcode</label>
                                <input type="text" class="form-control" id="barcode" name="barcode" value="<?= esc(old('barcode')) ?>">
                            </div>

                            <!-- Tags -->
                            <div class="mb-3">
                                <label class="form-label"><i class="bi bi-tags"></i> Tags</label>
                                <?php if (!empty($allTags)): ?>
                                    <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                                        <?php foreach ($allTags as $tag): ?>
                                            <div class="form-check">
                                                <input class="form-check-input tag-checkbox" type="checkbox" name="tag_ids[]"
                                                       value="<?= $tag['tag_id'] ?>" id="add_tag_<?= $tag['tag_id'] ?>"
                                                       data-name="<?= esc(strtolower($tag['name'])) ?>">
                                                <label class="form-check-label" for="add_tag_<?= $tag['tag_id'] ?>">
                                                    <?= esc($tag['name']) ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-muted small">No tags available. <a href="<?= base_url('database-maintenance/manage-lookups') ?>" target="_blank">Create tags in Database Maintenance</a>.</div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="seen" name="seen" value="1" <?= old('seen') ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="seen"><i class="bi bi-eye"></i> Seen</label>
                                </div>
                                <div class="form-check d-flex align-items-center gap-2">
                                    <input class="form-check-input" type="checkbox" id="loaned" name="loaned" value="1" <?= old('loaned') ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="loaned"><i class="bi bi-person-check"></i> Loaned <small id="loaned-wishlist-warning" class="text-muted d-none">(Wishlist items cannot be loaned)</small></label>
                                    <span id="loaned_person_name" class="badge bg-info text-dark <?= old('loaned') ? '' : 'd-none' ?>"><?= esc(old('loaned_person_name')) ?></span>
                                    <input type="hidden" id="loan_person_id" name="loan_person_id" value="<?= esc(old('loan_person_id')) ?>">
                                    <input type="hidden" id="loaned_person_name_input" name="loaned_person_name" value="<?= esc(old('loaned_person_name')) ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Web Links -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="bi bi-link-45deg"></i> Web Links</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="site" class="form-label">Site URL</label>
                                <input type="url" class="form-control" id="site" name="site" value="<?= esc(old('site')) ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="o_site" class="form-label">Original Site URL</label>
                                <input type="url" class="form-control" id="o_site" name="o_site" value="<?= esc(old('o_site')) ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="trailer" class="form-label">Trailer URL</label>
                                <input type="url" class="form-control" id="trailer" name="trailer" value="<?= esc(old('trailer')) ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Credits -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="bi bi-camera-reels"></i> Additional Credits</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="screenplay" class="form-label">Screenplay</label>
                                <input type="text" class="form-control" id="screenplay" name="screenplay" value="<?= esc(old('screenplay')) ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cameraman" class="form-label">Cameraman</label>
                                <input type="text" class="form-control" id="cameraman" name="cameraman" value="<?= esc(old('cameraman')) ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Text Areas -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="bi bi-journal-text"></i> Detailed Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="cast" class="form-label">Cast</label>
                        <textarea class="form-control" id="cast" name="cast" rows="4"><?= esc(old('cast')) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="plot" class="form-label">Plot</label>
                        <textarea class="form-control" id="plot" name="plot" rows="6"><?= esc(old('plot')) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="4"><?= esc(old('notes')) ?></textarea>
                        <div class="form-text">
                            Use <code>&lt;!skipduplicate&gt;</code> to exempt this entry from duplicate lookups.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="card">
                <div class="card-body text-center">
                    <button type="submit" class="btn btn-success btn-lg me-3">
                        <i class="bi bi-check-lg"></i> Save
                    </button>
                    <a href="<?= base_url('media') ?>" class="btn btn-secondary btn-lg">
                        <i class="bi bi-x-lg"></i> Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Poster Selection Modal -->
<div class="modal fade" id="posterSelectionModal" tabindex="-1" aria-labelledby="posterSelectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="posterSelectionModalLabel">
                    <i class="bi bi-images"></i> Choose Poster
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-3" id="addPosterTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="add-tmdb-tab" data-bs-toggle="tab" data-bs-target="#add-tmdb-posters" type="button" role="tab">
                            <i class="bi bi-cloud-download"></i> Online Posters
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="add-upload-tab" data-bs-toggle="tab" data-bs-target="#add-upload-poster" type="button" role="tab">
                            <i class="bi bi-upload"></i> Upload Custom
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="addPosterTabContent">
                    <!-- Online Posters Tab -->
                    <div class="tab-pane fade show active" id="add-tmdb-posters" role="tabpanel">
                        <div class="row g-2 mb-3 align-items-end">
                            <div class="col-md-4">
                                <label for="addPosterSource" class="form-label small">Search Location</label>
                                <select class="form-select" id="addPosterSource">
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
                                <label for="addPosterSearchTerm" class="form-label small">Search Term (optional)</label>
                                <input type="text" class="form-control" id="addPosterSearchTerm" placeholder="Enter title to search...">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-primary w-100" id="addFetchPostersBtn">
                                    <i class="bi bi-search"></i> Fetch
                                </button>
                            </div>
                        </div>
                        <div id="addPosterLoadingSpinner" style="display: none;" class="text-center mb-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="text-muted mt-2">Fetching posters...</p>
                        </div>
                        <div id="addPosterMessage" class="alert" style="display: none;"></div>
                        <div id="addPosterGallery" class="row g-3"></div>
                    </div>

                    <!-- Upload Custom Poster Tab -->
                    <div class="tab-pane fade" id="add-upload-poster" role="tabpanel">
                        <div class="mb-3">
                            <label for="addPosterFile" class="form-label">Choose Image File</label>
                            <input type="file" class="form-control" id="addPosterFile" name="poster_upload_file" accept="image/*">
                            <div class="form-text">Supported formats: JPG, PNG, GIF. Image will be converted to JPEG.</div>
                        </div>
                        <div class="mb-3" id="addUploadPreview" style="display: none;">
                            <label class="form-label">Preview</label>
                            <div class="text-center">
                                <img id="addUploadPreviewImg" src="" alt="Preview" class="img-fluid rounded" style="max-height: 300px;">
                            </div>
                        </div>
                        <button type="button" class="btn btn-success" id="selectUploadedPosterBtn">
                            <i class="bi bi-check-lg"></i> Use This Poster
                        </button>
                    </div>
                </div>

                <hr>
                <div class="text-center">
                    <button type="button" class="btn btn-outline-secondary" id="clearSelectedPosterBtn">
                        <i class="bi bi-x-circle"></i> No Poster
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?= $this->include('media/_quick_add_modals') ?>

<?= $this->endsection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Loaned checkbox logic
        const loanedCheckbox = document.getElementById('loaned');
        const loanedPersonBadge = document.getElementById('loaned_person_name');
        const loanPersonIdInput = document.getElementById('loan_person_id');
        const loanedPersonNameInput = document.getElementById('loaned_person_name_input');

        if (loanedCheckbox) {
            loanedCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    showLoanModal(function(personId, personName) {
                        loanPersonIdInput.value = personId;
                        loanedPersonNameInput.value = personName;
                        loanedPersonBadge.textContent = personName;
                        loanedPersonBadge.classList.remove('d-none');
                    }, function() {
                        // If modal was closed without confirming, uncheck the checkbox
                        if (!loanPersonIdInput.value) {
                            loanedCheckbox.checked = false;
                        }
                    });
                } else {
                    loanPersonIdInput.value = '';
                    loanedPersonNameInput.value = '';
                    loanedPersonBadge.classList.add('d-none');
                    loanedPersonBadge.textContent = '';
                }
            });
        }

        // Lookup type change logic
        const lookupTypeSelect = document.getElementById('lookup_type');
        const lookupIdInput = document.getElementById('lookup_id');
        const lookupIdLabel = document.getElementById('lookup_id_label');
        const lookupOptions = <?= json_encode($lookupOptions) ?>;

        function updateLookupFields() {
            const type = lookupTypeSelect.value;
            const opt = lookupOptions[type];
            if (opt) {
                lookupIdLabel.textContent = opt.id_label;
                lookupIdInput.placeholder = 'e.g. ' + opt.id_placeholder;
                lookupIdInput.dataset.field = opt.id_field;
                
                // Clear and sync values
                const hiddenId = document.getElementById(opt.id_field);
                lookupIdInput.value = hiddenId ? hiddenId.value : '';
            }
        }

        if (lookupTypeSelect) {
            lookupTypeSelect.addEventListener('change', updateLookupFields);
            updateLookupFields(); // Initial call
        }

        lookupIdInput.addEventListener('input', function() {
            const type = lookupTypeSelect.value;
            const opt = lookupOptions[type];
            if (opt) {
                const hiddenId = document.getElementById(opt.id_field);
                if (hiddenId) hiddenId.value = this.value;
            }
        });

        const btn = document.getElementById('lookupPreviewBtn');
        const applyBtn = document.getElementById('applyPreviewBtn');
        const previewCard = document.getElementById('previewCard');
        const previewPoster = document.getElementById('previewPoster');
        const previewPosterPlaceholder = document.getElementById('previewPosterPlaceholder');
        const previewTitle = document.getElementById('previewTitle');
        const previewSubtitle = document.getElementById('previewSubtitle');
        const previewPlot = document.getElementById('previewPlot');
        const form = document.getElementById('addMediaForm');
        
        // Handle Enter key in search fields - trigger search instead of form submit
        const searchFields = ['lookup_type', 'lookup_title', 'lookup_year', 'lookup_id', 'lookup_barcode'];
        searchFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        btn.click();
                        return false;
                    }
                });
            }
        });
        
        // Form validation before submit
        if (form) {
            form.addEventListener('submit', function(e) {
                let isValid = true;
                let errorMessage = '';
                
                // Track that validation has been attempted
                form.dataset.validated = 'true';
                
                // Check original title
                const oTitleField = document.getElementById('o_title');
                if (!oTitleField.value.trim()) {
                    isValid = false;
                    errorMessage += '• Original Title is required\n';
                    oTitleField.classList.add('is-invalid');
                } else {
                    oTitleField.classList.remove('is-invalid');
                }
                
                // Check at least one media format is selected
                const mediaCheckboxes = document.querySelectorAll('.medium-checkbox:checked');
                const mediaFormatError = document.getElementById('mediaFormatError');
                const mediaContainer = document.getElementById('mediaFormatsContainer');
                
                if (mediaCheckboxes.length === 0) {
                    isValid = false;
                    errorMessage += '• At least one media format must be selected\n';
                    mediaFormatError.classList.add('d-block');
                    mediaContainer.classList.add('border-danger');
                } else {
                    mediaFormatError.classList.remove('d-block');
                    mediaContainer.classList.remove('border-danger');
                }
                
                if (!isValid) {
                    e.preventDefault();
                    showToast('Please fix the following errors:\n' + errorMessage, 'error');
                    
                    // Scroll to first error
                    const firstError = document.querySelector('.is-invalid, .border-danger');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    
                    return false;
                }

                // Check for duplicates before submitting as a last resort warning
                const title = document.getElementById('title')?.value || '';
                const oTitle = document.getElementById('o_title')?.value || '';
                const year = document.getElementById('year')?.value || '';
                const barcode = document.getElementById('barcode')?.value || '';
                
                checkDuplicates(title, oTitle, year, barcode);
            });
        }
        
        // Real-time validation feedback for media formats
        const mediaCheckboxes = document.querySelectorAll('.medium-checkbox');
        mediaCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const checkedCount = document.querySelectorAll('.medium-checkbox:checked').length;
                const mediaFormatError = document.getElementById('mediaFormatError');
                const mediaContainer = document.getElementById('mediaFormatsContainer');
                
                // Only show/hide error if the user has already attempted to submit or if they are fixing an existing error
                if (checkedCount > 0) {
                    mediaFormatError.classList.remove('d-block');
                    mediaContainer.classList.remove('border-danger');
                } else {
                    mediaFormatError.classList.add('d-block');
                    mediaContainer.classList.add('border-danger');
                }
            });
        });

        // Trigger duplicate check when title or barcode changes manually
        ['title', 'o_title', 'barcode'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('blur', function() {
                    const title = document.getElementById('title')?.value || '';
                    const oTitle = document.getElementById('o_title')?.value || '';
                    const year = document.getElementById('year')?.value || '';
                    const barcode = document.getElementById('barcode')?.value || '';
                    if (title || oTitle || barcode) {
                        checkDuplicates(title, oTitle, year, barcode);
                    }
                });
            }
        });

        function setPreviewVisible(visible) {
            if (previewCard) previewCard.style.display = visible ? '' : 'none';
        }

        function checkDuplicates(title, oTitle, year, barcode) {
            const warningDiv = document.getElementById('duplicateWarning');
            const linksDiv = document.getElementById('duplicateLinks');
            
            if (!warningDiv || !linksDiv) return;
            
            // Clear previous results and hide
            warningDiv.style.display = 'none';
            linksDiv.innerHTML = '';

            const payload = {
                title: title,
                o_title: oTitle,
                year: year,
                barcode: barcode
            };

            // Set small delay to ensure UI updates first
            setTimeout(() => {
                fetch('<?= base_url('media/checkDuplicate') ?>', {
                    method: 'POST',
                    headers: { 
                        'X-Requested-With': 'XMLHttpRequest', 
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success && res.duplicates && res.duplicates.length > 0) {
                        warningDiv.style.display = 'block';
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        
                        linksDiv.innerHTML = res.duplicates.map(d => {
                            return `<div class="mb-1">
                                <span class="badge bg-secondary">#${d.movie_id}</span> 
                                <strong>${d.title}</strong> (${d.year || 'N/A'})
                                <a href="${d.url}" class="btn btn-sm btn-outline-primary ms-2" target="_blank">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                <a href="${d.edit_url}" class="btn btn-sm btn-primary ms-1" target="_blank">
                                    <i class="bi bi-pencil"></i> Edit Existing
                                </a>
                            </div>`;
                        }).join('');
                    }
                })
                .catch(err => console.error('Error checking duplicates:', err));
            }, 100);
        }

        function showToast(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
            alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 500px;';
            alertDiv.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
            document.body.appendChild(alertDiv);
            setTimeout(() => { if (alertDiv.parentNode) alertDiv.parentNode.removeChild(alertDiv); }, 5000);
        }

        function displayPreview(d, lookupType) {
            // Check for duplicates
            checkDuplicates(d.title, d.o_title, d.year, (document.getElementById('lookup_barcode')?.value || '').trim());

            // Title/subtitle
            previewTitle.textContent = d.title || d.o_title || 'Untitled';
            const bits = [];
            if (d.o_title && d.o_title !== d.title) bits.push('Original: ' + d.o_title);
            if (d.year) bits.push('Year: ' + d.year);
            if (d.runtime) bits.push('Runtime: ' + d.runtime + ' min');
            if (d.genre) bits.push('Genre: ' + d.genre);
            if (d.country) bits.push('Country: ' + d.country);
            if (d.studio) bits.push('Studio: ' + d.studio);
            previewSubtitle.textContent = bits.join(' • ');
            previewPlot.textContent = d.plot || '';

            // Poster
            if (d.poster_url) {
                previewPoster.src = d.poster_url;
                previewPoster.style.display = '';
                previewPosterPlaceholder.style.display = 'none';
                document.getElementById('selected_poster_url').value = d.poster_url;
            } else {
                previewPoster.src = '';
                previewPoster.style.display = 'none';
                previewPosterPlaceholder.style.display = '';
                document.getElementById('selected_poster_url').value = '';
            }

            const idField = lookupOptions[lookupType]?.id_field || (Object.keys(lookupOptions).length > 0 ? lookupOptions[Object.keys(lookupOptions)[0]].id_field : 'tmdb_id');
            const fetchBody = { type: lookupType };
            fetchBody[idField] = d.tmdb_id || d.tvdb_id || d.igdb_id || d.mbid || d.imdb_id;
            // Also include imdb_id and tmdb_id if available as fallbacks
            if (d.imdb_id) fetchBody['imdb_id'] = d.imdb_id;
            if (d.tmdb_id) fetchBody['tmdb_id'] = d.tmdb_id;

            // Store ID and type for fetching more posters
            if (fetchBody[idField]) {
                document.getElementById('lookup_id_for_posters').value = fetchBody[idField];
                document.getElementById('media_type_for_posters').value = lookupType;
                document.getElementById('choosePosterBtn').style.display = '';
            }

            setPreviewVisible(true);

            // Attach to apply button
            if (applyBtn) {
                applyBtn.onclick = function() {
                    function setVal(id, val) { const el = document.getElementById(id); if (el && val !== undefined && val !== null) el.value = val; }
                    setVal('title', d.title || d.o_title);
                    setVal('o_title', d.o_title || d.title);
                    setVal('director', d.director);
                    setVal('year', d.year);
                    setVal('runtime', d.runtime);
                    setVal('genre', d.genre);
                    setVal('country', d.country);
                    setVal('studio', d.studio);
                    setVal('classification', d.classification);
                    setVal('cast', d.cast);
                    setVal('plot', d.plot);
                    setVal('notes', d.notes);
                    setVal('site', d.site);
                    setVal('o_site', d.o_site);
                    if (d.rating) { const ratingSel = document.getElementById('rating'); if (ratingSel) ratingSel.value = d.rating; }
                    showToast('Applied preview data to the form. Review and click Save to create the entry.', 'success');
                };
            }
        }

        let searchFilterTimeout;
        function showMultipleResults(results, lookupType, page = 1, totalPages = 1, totalResults = 0, searchParams = {}, message = '') {
            const title = message || `Select a Match (${totalResults} results found)`;
            const existingModalEl = document.getElementById('selectMediaModal');
            let modal;

            if (existingModalEl) {
                // Update existing modal
                modal = bootstrap.Modal.getInstance(existingModalEl) || new bootstrap.Modal(existingModalEl);
                existingModalEl.querySelector('.modal-title').textContent = title;
                
                // Update results list
                const listContainer = existingModalEl.querySelector('#mediaResultsList');
                listContainer.innerHTML = results.map((result, index) => `
                    <a href="#" class="list-group-item list-group-item-action media-result-item" data-index="${index}">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                ${result.poster_url ?
                                    `<img src="${result.poster_url}" alt="Poster" style="width: 60px; height: 90px; object-fit: cover;" class="rounded">` :
                                    `<div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 90px;"><small class="text-muted">No poster</small></div>`
                                }
                            </div>
                            <div class="col">
                                <h6 class="mb-1">${result.title || result.original_title || 'Untitled'}</h6>
                                ${result.original_title && result.original_title !== result.title ? `<small class="text-muted d-block">Original: ${result.original_title}</small>` : ''}
                                ${result.year || result.platforms || result.artist ? `
                                    <div class="mt-1">
                                        ${result.artist ? `<span class="badge bg-primary me-1 clickable-tag" data-tag="artist" data-value="${result.artist}">Artist: ${result.artist}</span>` : ''}
                                        ${result.year ? `<span class="badge bg-secondary me-1 clickable-tag" data-tag="year" data-value="${result.year}">Year: ${result.year}</span>` : ''}
                                        ${result.platforms ? `<span class="badge bg-info text-dark clickable-tag" data-tag="system" data-value="${result.platforms}">System: ${result.platforms}</span>` : ''}
                                    </div>
                                ` : ''}
                                ${result.overview ? `<p class="mb-0 mt-1 small text-muted" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">${result.overview}</p>` : ''}
                            </div>
                        </div>
                    </a>
                `).join('');

                // Update footer/pagination
                let footer = existingModalEl.querySelector('.modal-footer');
                if (totalPages > 1) {
                    if (!footer) {
                        footer = document.createElement('div');
                        footer.className = 'modal-footer';
                        existingModalEl.querySelector('.modal-content').appendChild(footer);
                    }
                    footer.innerHTML = `
                        <div class="d-flex justify-content-between w-100 align-items-center">
                            <button type="button" class="btn btn-secondary" id="prevPageBtn" ${page <= 1 ? 'disabled' : ''}>
                                <i class="bi bi-chevron-left"></i> Previous
                            </button>
                            <span>Page ${page} of ${totalPages}</span>
                            <button type="button" class="btn btn-secondary" id="nextPageBtn" ${page >= totalPages ? 'disabled' : ''}>
                                Next <i class="bi bi-chevron-right"></i>
                            </button>
                        </div>
                    `;
                } else if (footer) {
                    footer.remove();
                }
                
                // Scroll to top of results
                existingModalEl.querySelector('.modal-body').scrollTop = 0;

                // Handle badge clicks
                existingModalEl.querySelectorAll('.clickable-tag').forEach(tag => {
                    tag.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        const filterInput = document.getElementById('modalResultsFilter');
                        if (filterInput) {
                            const tagName = this.dataset.tag;
                            const tagValue = this.dataset.value;
                            // Check if it's multiple platforms, just take the first one for better search
                            const cleanValue = tagValue.includes(',') ? tagValue.split(',')[0].trim() : tagValue;
                            filterInput.value = `${tagName}:"${cleanValue}"`;
                            filterInput.dispatchEvent(new Event('input'));
                        }
                    });
                });

                // Ensure modal is shown
                modal.show();
            } else {
                // Create modal HTML for selection
                const modalHtml = `
                    <div class="modal fade" id="selectMediaModal" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">${title}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body" style="max-height: 600px; overflow-y: auto;">
                                    <div class="mb-3">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-filter"></i></span>
                                            <input type="text" id="modalResultsFilter" class="form-control" placeholder="Search within all results...">
                                        </div>
                                    </div>
                                    <div class="list-group" id="mediaResultsList">
                                        ${results.map((result, index) => `
                                            <a href="#" class="list-group-item list-group-item-action media-result-item" data-index="${index}">
                                                <div class="row align-items-center">
                                                    <div class="col-auto">
                                                        ${result.poster_url ?
                    `<img src="${result.poster_url}" alt="Poster" style="width: 60px; height: 90px; object-fit: cover;" class="rounded">` :
                    `<div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 90px;"><small class="text-muted">No poster</small></div>`
                }
                                                    </div>
                                                    <div class="col">
                                                        <h6 class="mb-1">${result.title || result.original_title || 'Untitled'}</h6>
                                                        ${result.original_title && result.original_title !== result.title ? `<small class="text-muted d-block">Original: ${result.original_title}</small>` : ''}
                                                        ${result.year || result.platforms || result.artist ? `
                                                            <div class="mt-1">
                                                                ${result.artist ? `<span class="badge bg-primary me-1 clickable-tag" data-tag="artist" data-value="${result.artist}">Artist: ${result.artist}</span>` : ''}
                                                                ${result.year ? `<span class="badge bg-secondary me-1 clickable-tag" data-tag="year" data-value="${result.year}">Year: ${result.year}</span>` : ''}
                                                                ${result.platforms ? `<span class="badge bg-info text-dark clickable-tag" data-tag="system" data-value="${result.platforms}">System: ${result.platforms}</span>` : ''}
                                                            </div>
                                                        ` : ''}
                                                        ${result.overview ? `<p class="mb-0 mt-1 small text-muted" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">${result.overview}</p>` : ''}
                                                    </div>
                                                </div>
                                            </a>
                                        `).join('')}
                                    </div>
                                </div>
                                ${totalPages > 1 ? `
                                <div class="modal-footer">
                                    <div class="d-flex justify-content-between w-100 align-items-center">
                                        <button type="button" class="btn btn-secondary" id="prevPageBtn" ${page <= 1 ? 'disabled' : ''}>
                                            <i class="bi bi-chevron-left"></i> Previous
                                        </button>
                                        <span>Page ${page} of ${totalPages}</span>
                                        <button type="button" class="btn btn-secondary" id="nextPageBtn" ${page >= totalPages ? 'disabled' : ''}>
                                            Next <i class="bi bi-chevron-right"></i>
                                        </button>
                                    </div>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                `;

                // Add modal to page
                document.body.insertAdjacentHTML('beforeend', modalHtml);

                // Show modal
                modal = new bootstrap.Modal(document.getElementById('selectMediaModal'));
                modal.show();
            }

            // Re-bind event listeners (they are lost when innerHTML is updated or it's a new modal)

            // Handle pagination
            const prevBtn = document.getElementById('prevPageBtn');
            const nextBtn = document.getElementById('nextPageBtn');
            const filterInput = document.getElementById('modalResultsFilter');

            if (prevBtn) {
                prevBtn.addEventListener('click', function() {
                    loadPage(page - 1, searchParams, lookupType, filterInput?.value || '');
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', function() {
                    loadPage(page + 1, searchParams, lookupType, filterInput?.value || '');
                });
            }

            // Handle badge clicks for new modal
            document.querySelectorAll('.clickable-tag').forEach(tag => {
                tag.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const filterInput = document.getElementById('modalResultsFilter');
                    if (filterInput) {
                        const tagName = this.dataset.tag;
                        const tagValue = this.dataset.value;
                        const cleanValue = tagValue.includes(',') ? tagValue.split(',')[0].trim() : tagValue;
                        filterInput.value = `${tagName}:"${cleanValue}"`;
                        filterInput.dispatchEvent(new Event('input'));
                    }
                });
            });

            // Handle selection
            document.querySelectorAll('.media-result-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const index = parseInt(this.dataset.index);
                    const selected = results[index];

                    modal.hide();

                    // Fetch full details for the selected item
                    btn.disabled = true;
                    const oldHtml = btn.innerHTML;
                    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Loading...';

                    const idField = lookupOptions[selected.type || lookupType]?.id_field || (Object.keys(lookupOptions).length > 0 ? lookupOptions[Object.keys(lookupOptions)[0]].id_field : 'tmdb_id');
                    const fetchBody = { type: selected.type || lookupType };
                    fetchBody[idField] = selected.tmdb_id || selected.tvdb_id || selected.igdb_id || selected.mbid || selected.imdb_id;
                    if (selected.imdb_id) fetchBody['imdb_id'] = selected.imdb_id;
                    if (selected.tvdb_id) fetchBody['tvdb_id'] = selected.tvdb_id;
                    if (selected.tmdb_id) fetchBody['tmdb_id'] = selected.tmdb_id;

                    fetch('<?= base_url('media/fetchDetails') ?>', {
                        method: 'POST',
                        headers: { 
                            'X-Requested-With': 'XMLHttpRequest', 
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(fetchBody)
                    })
                    .then(r => {
                        if (!r.ok && r.status === 403) {
                            throw new Error('CSRF validation failed. Please refresh the page.');
                        }
                        return r.json();
                    })
                    .then(res => {
                        if (res.success && res.data) {
                            displayPreview(res.data, lookupType);
                            showToast(res.message, 'success');
                        } else {
                            showToast(res.message || 'Failed to fetch details', 'error');
                        }
                    })
                    .catch(() => {
                        showToast('An error occurred while fetching details.', 'error');
                    })
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML = oldHtml;
                    });
                });
            });

            // Handle filtering
            if (filterInput) {
                filterInput.addEventListener('input', function() {
                    const filterText = this.value.trim();
                    
                    clearTimeout(searchFilterTimeout);
                    searchFilterTimeout = setTimeout(() => {
                        loadPage(1, searchParams, lookupType, filterText);
                    }, 500); // 500ms debounce
                });
                
                // Focus the filter input after modal is shown
                if (!existingModalEl) {
                    const modalEl = document.getElementById('selectMediaModal');
                    modalEl.addEventListener('shown.bs.modal', function () {
                        filterInput.focus();
                    });
                }
            }
        }

        function loadPage(page, searchParams, lookupType, filterText = '') {
            btn.disabled = true;
            const oldHtml = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Loading...';

            // Refine the search if filterText is present
            let refinedTitle = searchParams.title || '';
            if (filterText) {
                refinedTitle += ' ' + filterText;
            }

            const payload = { ...searchParams, title: refinedTitle, page: page };

            fetch('<?= base_url('media/lookup') ?>', {
                method: 'POST',
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest', 
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            })
        .then(r => {
            if (!r.ok && r.status === 403) {
                throw new Error('CSRF validation failed. Please refresh the page.');
            }
            return r.json();
        })
        .then(res => {
                if (res.success && res.results) {
                    showMultipleResults(
                        res.results, 
                        lookupType, 
                        res.page || 1, 
                        res.total_pages || 1, 
                        res.total_results || res.results.length, 
                        searchParams,
                        res.message
                    );
                } else {
                    showToast(res.message || 'No more results', 'error');
                }
            })
            .catch(() => {
                showToast('An error occurred while loading more results.', 'error');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = oldHtml;
            });
        }

        if (btn) {
            btn.addEventListener('click', function() {
                const payload = {
                    lookup_type: (document.getElementById('lookup_type')?.value || "<?= !empty($enabledLookups) ? $enabledLookups[0] : '' ?>"),
                    title: (document.getElementById('lookup_title')?.value || '').trim(),
                    year: (document.getElementById('lookup_year')?.value || '').trim(),
                    barcode: (document.getElementById('lookup_barcode')?.value || '').trim(),
                };
                
                // Add the specific ID based on type
                const opt = lookupOptions[payload.lookup_type];
                if (opt) {
                    const idVal = (document.getElementById('lookup_id')?.value || '').trim();
                    if (idVal) payload[opt.id_field] = idVal;
                }

                // basic guard
                if (!payload.title && !payload.barcode && !payload[opt?.id_field]) {
                    showToast('Enter a Title, ID, or Barcode to search.', 'error');
                    return;
                }

                btn.disabled = true;
                const oldHtml = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Searching...';

                fetch('<?= base_url('media/lookup') ?>', {
                    method: 'POST',
                    headers: { 
                        'X-Requested-With': 'XMLHttpRequest', 
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                }).then(r => {
                    if (!r.ok && r.status === 403) {
                        throw new Error('CSRF validation failed. Please refresh the page.');
                    }
                    return r.json();
                })
                  .then(res => {
                      if (res.success) {
                          if (res.multiple && res.results) {
                              // Show selection modal with pagination
                              showMultipleResults(
                                  res.results,
                                  payload.lookup_type,
                                  res.page || 1,
                                  res.total_pages || 1,
                                  res.total_results || res.results.length,
                                  payload,
                                  res.message
                              );
                              showToast(res.message, 'success');
                          } else {
                              // Single result - display directly
                              const d = res.data || {};
                              displayPreview(d, payload.lookup_type);
                              showToast(res.message, 'success');
                          }
                      } else {
                          setPreviewVisible(false);
                          showToast(res.message || 'No results.', 'error');
                      }
                  })
                  .catch(() => {
                      setPreviewVisible(false);
                      showToast('An error occurred while searching.', 'error');
                  })
                  .finally(() => { btn.disabled = false; btn.innerHTML = oldHtml; });
            });
        }

        // Poster Selection Functionality
        let availablePosters = [];
        let selectedPosterData = null;

        // Choose poster button click
        const choosePosterBtn = document.getElementById('choosePosterBtn');
        const addFetchPostersBtn = document.getElementById('addFetchPostersBtn');
        if (addFetchPostersBtn) {
            addFetchPostersBtn.addEventListener('click', function() {
                const lookupId = document.getElementById('lookup_id_for_posters').value;
                const mediaType = document.getElementById('media_type_for_posters').value || "<?= !empty($enabledLookups) ? $enabledLookups[0] : '' ?>";

                if (!lookupId) {
                    showToast('No ID available. Please search first.', 'error');
                    return;
                }

                fetchPostersForSelection(lookupId, mediaType);
            });
        }

        if (choosePosterBtn) {
            choosePosterBtn.addEventListener('click', function() {
                const lookupId = document.getElementById('lookup_id_for_posters').value;
                const mediaType = document.getElementById('media_type_for_posters').value || "<?= !empty($enabledLookups) ? $enabledLookups[0] : '' ?>";

                if (!lookupId) {
                    showToast('No ID available. Please search first.', 'error');
                    return;
                }

                // Pre-populate search term with the media title
                const titleInput = document.getElementById('title');
                const oTitleInput = document.getElementById('o_title');
                const posterSearchTermInput = document.getElementById('addPosterSearchTerm');
                if (posterSearchTermInput) {
                    posterSearchTermInput.value = titleInput.value || oTitleInput.value || '';
                }

                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('posterSelectionModal'));
                modal.show();

                // Fetch posters automatically the first time
                fetchPostersForSelection(lookupId, mediaType);
            });
        }

        function fetchPostersForSelection(lookupId, mediaType) {
            const btn = document.getElementById('addFetchPostersBtn');
            const originalHtml = btn ? btn.innerHTML : '';
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Fetching...';
            }

            document.getElementById('addPosterLoadingSpinner').style.display = 'block';
            document.getElementById('addPosterMessage').style.display = 'none';
            document.getElementById('addPosterGallery').innerHTML = '';

            const sourceSelect = document.getElementById('addPosterSource');
            const selectedSource = sourceSelect ? sourceSelect.value : '';
            const searchTermInput = document.getElementById('addPosterSearchTerm');
            const searchTerm = searchTermInput ? searchTermInput.value : '';

            const idField = lookupOptions[mediaType]?.id_field || (Object.keys(lookupOptions).length > 0 ? lookupOptions[Object.keys(lookupOptions)[0]].id_field : 'tmdb_id');
            const fetchBody = { type: mediaType, source: selectedSource, searchTerm: searchTerm };
            fetchBody[idField] = lookupId;

            fetch('<?= base_url('media/fetchPostersForNew') ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(fetchBody)
            })
            .then(r => {
                if (!r.ok && r.status === 403) {
                    throw new Error('CSRF validation failed. Please refresh the page.');
                }
                return r.json();
            })
            .then(data => {
                document.getElementById('addPosterLoadingSpinner').style.display = 'none';

                if (data.success && data.posters && data.posters.length > 0) {
                    // Update tab label if source is returned
                    if (data.source) {
                        const tab = document.getElementById('add-tmdb-tab');
                        if (tab) {
                            tab.innerHTML = `<i class="bi bi-cloud-download"></i> ${data.source} Posters`;
                        }
                    }
                    availablePosters = data.posters;
                    displayAddPosterGallery(data.posters);
                    showAddPosterMessage(data.message, 'success');
                } else {
                    showAddPosterMessage(data.message || 'No posters found', 'warning');
                }
            })
            .catch(error => {
                document.getElementById('addPosterLoadingSpinner').style.display = 'none';
                showAddPosterMessage('Error fetching posters: ' + error.message, 'danger');
            })
            .finally(() => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            });
        }

        function displayAddPosterGallery(posters) {
            const gallery = document.getElementById('addPosterGallery');
            gallery.innerHTML = '';

            posters.forEach((poster, index) => {
                const col = document.createElement('div');
                col.className = 'col-md-4 col-sm-6';
                col.innerHTML = `
                    <div class="card add-poster-option" style="cursor: pointer;" data-poster-index="${index}">
                        <img src="${poster.thumbnail}" class="card-img-top" alt="Poster ${index + 1}">
                        <div class="card-body p-2 text-center">
                            ${poster.label ? `<div class="small fw-bold mb-1 text-truncate" title="${poster.label}">${poster.label}</div>` : ''}
                            <small class="text-muted">
                                ${poster.width && poster.height ? poster.width + ' x ' + poster.height : 'invalid image'}
                                ${poster.vote_average > 0 ? '⭐ ' + poster.vote_average.toFixed(1) : ''}
                            </small>
                        </div>
                    </div>
                `;
                gallery.appendChild(col);
            });

            // Add click handlers
            document.querySelectorAll('.add-poster-option').forEach(option => {
                option.addEventListener('click', function() {
                    const index = parseInt(this.dataset.posterIndex);
                    selectPosterFromGallery(index);
                });
            });
        }

        function selectPosterFromGallery(index) {
            if (index >= 0 && index < availablePosters.length) {
                const poster = availablePosters[index];
                selectedPosterData = poster;

                // Update preview
                updatePosterPreview(poster.url);

                // Store URL
                document.getElementById('selected_poster_url').value = poster.url;

                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('posterSelectionModal')).hide();

                showToast('Poster selected! This will be saved when you create the media.', 'success');
            }
        }

        function updatePosterPreview(url) {
            const previewPoster = document.getElementById('previewPoster');
            const previewPosterPlaceholder = document.getElementById('previewPosterPlaceholder');

            if (url) {
                previewPoster.src = url;
                previewPoster.style.display = '';
                previewPosterPlaceholder.style.display = 'none';
            } else {
                previewPoster.src = '';
                previewPoster.style.display = 'none';
                previewPosterPlaceholder.style.display = '';
            }
        }

        function showAddPosterMessage(message, type) {
            const msgDiv = document.getElementById('addPosterMessage');
            msgDiv.className = 'alert alert-' + type;
            msgDiv.textContent = message;
            msgDiv.style.display = 'block';
        }

        // Upload custom poster
        const addPosterFile = document.getElementById('addPosterFile');
        if (addPosterFile) {
            addPosterFile.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        document.getElementById('addUploadPreviewImg').src = event.target.result;
                        document.getElementById('addUploadPreview').style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        // Select uploaded poster
        const selectUploadedPosterBtn = document.getElementById('selectUploadedPosterBtn');
        if (selectUploadedPosterBtn) {
            selectUploadedPosterBtn.addEventListener('click', function() {
                const file = document.getElementById('addPosterFile').files[0];
                if (!file) {
                    showToast('Please select a file first', 'error');
                    return;
                }

                // Preview the uploaded image
                const previewSrc = document.getElementById('addUploadPreviewImg').src;
                updatePosterPreview(previewSrc);

                // Mark that we're using an upload (clear the URL field)
                document.getElementById('selected_poster_url').value = '';

                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('posterSelectionModal')).hide();

                showToast('Custom poster selected! This will be uploaded when you create the media.', 'success');
            });
        }

        // Clear selected poster
        const clearSelectedPosterBtn = document.getElementById('clearSelectedPosterBtn');
        if (clearSelectedPosterBtn) {
            clearSelectedPosterBtn.addEventListener('click', function() {
                updatePosterPreview(null);
                document.getElementById('selected_poster_url').value = '';
                document.getElementById('addPosterFile').value = '';
                document.getElementById('addUploadPreview').style.display = 'none';

                bootstrap.Modal.getInstance(document.getElementById('posterSelectionModal')).hide();

                showToast('Poster selection cleared', 'success');
            });
        }
    });
</script>
<?= $this->endsection() ?>
