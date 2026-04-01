<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?php $movie = isset($movie) && is_array($movie) ? $movie : []; $apiAvailable = isset($apiAvailable) ? (bool)$apiAvailable : false; $mediaTypes = isset($mediaTypes) && is_array($mediaTypes) ? $mediaTypes : []; $collections = isset($collections) && is_array($collections) ? $collections : []; $volumes = isset($volumes) && is_array($volumes) ? $volumes : []; $videoCodecs = isset($videoCodecs) && is_array($videoCodecs) ? $videoCodecs : []; $ratios = isset($ratios) && is_array($ratios) ? $ratios : []; $movieTags = isset($movieTags) && is_array($movieTags) ? $movieTags : []; $allTags = isset($allTags) && is_array($allTags) ? $allTags : []; ?>

    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-0">
                    <i class="bi bi-pencil"></i> Edit Media
                </h1>
                <div>
                    <a href="<?= base_url('media/view/' . $movie['movie_id']) ?>" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-eye"></i> View Details
                    </a>
                    <a href="<?= base_url('media') ?>" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Library
                    </a>
                </div>
            </div>

            <!-- Success/Error Messages -->
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

            <!-- Media Poster Preview -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="bi bi-image"></i> Media Poster</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <?php if ($movie['poster_md5']): ?>
                                    <img src="<?= base_url('media/poster/' . $movie['movie_id']) . '?v=' . urlencode($movie['poster_md5']) ?>"
                                         class="img-fluid rounded shadow-sm poster-image" 
                                         alt="<?= esc($movie['title'] ?: $movie['o_title']) ?>"
                                         style="max-height: 300px; max-width: 200px;"
                                         id="poster-preview">
                                <?php else: ?>
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center shadow-sm" 
                                         style="height: 300px; width: 200px; margin: 0 auto;" id="poster-preview">
                                        <i class="bi bi-film display-4 text-muted"></i>
                                    </div>
                                    <p class="text-muted mt-2">No poster available</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="d-flex align-items-center h-100">
                                <div>
                                    <h6 class="mb-3">Current Media: <?= esc($movie['title'] ?: $movie['o_title'] ?: 'Untitled') ?></h6>
                                    <?php if ($movie['poster_md5']): ?>
                                        <p class="text-muted mb-2">
                                            <i class="bi bi-check-circle text-success"></i> 
                                            Poster is available (MD5: <code><?= esc($movie['poster_md5']) ?></code>)
                                        </p>
                                        <p class="text-muted mb-3">
                                            <small>Last updated: <?= $movie['updated'] ? user_date($movie['updated']) : 'Unknown' ?></small>
                                        </p>
                                    <?php else: ?>
                                        <p class="text-muted mb-3">
                                            <i class="bi bi-x-circle text-warning"></i> 
                                            No poster available for this media
                                        </p>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($apiAvailable) && $apiAvailable): ?>
                                        <div class="alert alert-info py-2">
                                            <small>
                                                <i class="bi bi-info-circle"></i>
                                                Use the "Fetch Media Data from TMDB" button below to automatically download a poster for this media.
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Form -->
            <form action="<?= base_url('media/update/' . $movie['movie_id']) ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" id="fetched_poster_url" name="fetched_poster_url" value="">

                <!-- Poster selection (existing vs fetched) -->
                <div id="poster-compare-container" class="card mb-4" style="display: none;">
                    <div class="card-header">
                        <h5><i class="bi bi-images"></i> Choose Poster to Save</h5>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-start">
                            <div class="col-md-6 mb-3">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="poster_choice" id="poster_choice_existing" value="existing" <?= $movie['poster_md5'] ? 'checked' : 'disabled' ?>>
                                    <label class="form-check-label" for="poster_choice_existing">
                                        Keep existing poster
                                    </label>
                                </div>
                                <div class="text-center">
                                    <?php if ($movie['poster_md5']): ?>
                                        <img id="existing-poster-img" src="<?= base_url('media/poster/' . $movie['movie_id']) . '?v=' . urlencode($movie['poster_md5']) ?>" class="img-fluid rounded shadow-sm" alt="Existing Poster" style="max-height: 260px; max-width: 180px;">
                                    <?php else: ?>
                                        <div id="existing-poster-img" class="bg-light rounded d-flex align-items-center justify-content-center shadow-sm" style="height: 260px; width: 180px; margin: 0 auto;">
                                            <span class="text-muted">No existing poster</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="poster_choice" id="poster_choice_fetched" value="fetched">
                                    <label class="form-check-label" for="poster_choice_fetched">
                                        Use new poster from TMDB
                                    </label>
                                </div>
                                <div class="text-center">
                                    <img id="fetched-poster-img" src="" class="img-fluid rounded shadow-sm" alt="Fetched Poster" style="max-height: 260px; max-width: 180px; display: none;">
                                    <div id="fetched-poster-placeholder" class="bg-light rounded d-flex align-items-center justify-content-center shadow-sm" style="height: 260px; width: 180px; margin: 0 auto;">
                                        <span class="text-muted">No fetched poster yet</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="radio" name="poster_choice" id="poster_choice_none" value="none">
                            <label class="form-check-label" for="poster_choice_none">
                                No poster (clear poster on save)
                            </label>
                        </div>
                        <small class="text-muted">Choose to keep the existing poster, use the newly fetched one, or select "No poster" to remove it. Your selection will be applied when you click "Update Media". Cancel will discard any fetched changes.</small>
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
                                    <label for="title" class="form-label">Title</label>
                                    <input type="text" class="form-control" id="title" name="title"
                                           value="<?= esc(old('title', $movie['title'])) ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="o_title" class="form-label">Original Title</label>
                                    <input type="text" class="form-control" id="o_title" name="o_title"
                                           value="<?= esc(old('o_title', $movie['o_title'])) ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="director" class="form-label">Director</label>
                                    <input type="text" class="form-control" id="director" name="director"
                                           value="<?= esc(old('director', $movie['director'])) ?>">
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="year" class="form-label">Year</label>
                                            <input type="number" class="form-control" id="year" name="year"
                                                   value="<?= esc(old('year', $movie['year'])) ?>" min="1800" max="2099">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="runtime" class="form-label">Runtime (minutes)</label>
                                            <input type="number" class="form-control" id="runtime" name="runtime"
                                                   value="<?= esc(old('runtime', $movie['runtime'])) ?>" min="0">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="genre" class="form-label">Genre</label>
                                    <input type="text" class="form-control" id="genre" name="genre"
                                           value="<?= esc(old('genre', $movie['genre'])) ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="country" class="form-label">Country</label>
                                    <input type="text" class="form-control" id="country" name="country"
                                           value="<?= esc(old('country', $movie['country'])) ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="studio" class="form-label">Studio</label>
                                    <input type="text" class="form-control" id="studio" name="studio"
                                           value="<?= esc(old('studio', $movie['studio'])) ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="classification" class="form-label">Classification (MPAA/TV rating)</label>
                                    <input type="text" class="form-control" id="classification" name="classification"
                                           value="<?= esc(old('classification', $movie['classification'])) ?>">
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
                                <?php 
                                // Extract medium_ids from notes for pre-selection
                                $storedMediumIds = get_medium_ids_from_notes($movie['notes'] ?? null);
                                // If no IDs in notes but medium_id is set, use that (backward compatibility)
                                if (empty($storedMediumIds) && !empty($movie['medium_id'])) {
                                    $storedMediumIds = [(int)$movie['medium_id']];
                                }
                                // Allow old() to override if form was submitted with errors
                                $selectedMediumIds = old('medium_ids', $storedMediumIds);
                                ?>
                                <!-- Multiple medium selection -->
                                <div class="mb-3">
                                    <label class="form-label">Media Formats <small class="text-muted">(select all that apply)</small></label>
                                    <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                                        <?php foreach ($mediaTypes as $medium): ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="medium_<?= $medium['medium_id'] ?>" 
                                                       name="medium_ids[]" 
                                                       value="<?= $medium['medium_id'] ?>"
                                                       <?= is_array($selectedMediumIds) && in_array($medium['medium_id'], $selectedMediumIds) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="medium_<?= $medium['medium_id'] ?>">
                                                    <?= esc($medium['name']) ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <small class="text-muted">The highest format will be stored as the primary medium.</small>
                                </div>

                                <div class="mb-3">
                                    <label for="collection_id" class="form-label">Collection</label>
                                    <div class="input-group">
                                        <select class="form-select" id="collection_id" name="collection_id">
                                            <option value="">No Collection</option>
                                            <?php foreach ($collections as $collection): ?>
                                                <option value="<?= $collection['collection_id'] ?>"
                                                        <?= old('collection_id', $movie['collection_id']) == $collection['collection_id'] ? 'selected' : '' ?>>
                                                    <?= esc($collection['name']) ?>
                                                </option>
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
                                                <option value="<?= $volume['volume_id'] ?>"
                                                        <?= old('volume_id', $movie['volume_id']) == $volume['volume_id'] ? 'selected' : '' ?>>
                                                    <?= esc($volume['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="btn btn-outline-secondary" type="button" id="addNewVolumeBtn" title="Add New Volume">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                    </div>
                                </div>

                                <div id="digital-only-fields">
                                    <div class="mb-3">
                                        <label for="vcodec_id" class="form-label">Video Codec</label>
                                        <select class="form-select" id="vcodec_id" name="vcodec_id">
                                            <option value="">Select Video Codec</option>
                                            <?php foreach ($videoCodecs as $codec): ?>
                                                <option value="<?= $codec['vcodec_id'] ?>"
                                                        <?= old('vcodec_id', $movie['vcodec_id']) == $codec['vcodec_id'] ? 'selected' : '' ?>>
                                                    <?= esc($codec['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="ratio_id" class="form-label">Aspect Ratio</label>
                                        <select class="form-select" id="ratio_id" name="ratio_id">
                                            <option value="">Select Aspect Ratio</option>
                                            <?php foreach ($ratios as $ratio): ?>
                                                <option value="<?= $ratio['ratio_id'] ?>"
                                                        <?= old('ratio_id', $movie['ratio_id']) == $ratio['ratio_id'] ? 'selected' : '' ?>>
                                                    <?= esc($ratio['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="width" class="form-label">Width (pixels)</label>
                                                <input type="number" class="form-control" id="width" name="width"
                                                       value="<?= esc(old('width', $movie['width'])) ?>" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="height" class="form-label">Height (pixels)</label>
                                                <input type="number" class="form-control" id="height" name="height"
                                                       value="<?= esc(old('height', $movie['height'])) ?>" min="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="barcode" class="form-label">Barcode</label>
                                    <input type="text" class="form-control" id="barcode" name="barcode"
                                           value="<?= esc(old('barcode', $movie['barcode'])) ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="rating" class="form-label">Rating (1-5)</label>
                                    <select class="form-select" id="rating" name="rating">
                                        <option value="">No Rating</option>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <option value="<?= $i ?>"
                                                    <?= old('rating', $movie['rating']) == $i ? 'selected' : '' ?>>
                                                <?= $i ?> Star<?= $i > 1 ? 's' : '' ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>

                                <!-- Tags -->
                                <div class="mb-3">
                                    <label class="form-label"><i class="bi bi-tags"></i> Tags</label>
                                    <?php if (!empty($allTags)): ?>
                                        <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                                            <?php
                                            $selectedTagIds = array_column($movieTags, 'tag_id');
                                            $isWishlist = false;
                                            foreach ($allTags as $tag):
                                                $isChecked = in_array($tag['tag_id'], $selectedTagIds);
                                                if ($isChecked && strtolower($tag['name']) === 'wishlist') {
                                                    $isWishlist = true;
                                                }
                                            ?>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="tag_ids[]"
                                                           value="<?= $tag['tag_id'] ?>" id="edit_tag_<?= $tag['tag_id'] ?>"
                                                           <?= $isChecked ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="edit_tag_<?= $tag['tag_id'] ?>">
                                                        <?= esc($tag['name']) ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <!-- Hidden field to ensure tag_ids is always sent even if no tags are selected -->
                                        <input type="hidden" name="tag_ids_sent" value="1">
                                    <?php else: ?>
                                        <div class="text-muted small">No tags available. <a href="<?= base_url('database-maintenance/manage-lookups') ?>" target="_blank">Create tags in Database Maintenance</a>.</div>
                                    <?php endif; ?>
                                </div>

                                <!-- Status Checkboxes -->
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="seen" name="seen" value="1"
                                                <?= old('seen', $movie['seen']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="seen">
                                            <i class="bi bi-eye"></i> Seen
                                        </label>
                                    </div>
                                    <div class="form-check d-flex align-items-center gap-2">
                                        <input class="form-check-input" type="checkbox" id="loaned" name="loaned" value="1"
                                                <?= old('loaned', $movie['loaned']) ? 'checked' : '' ?>
                                                <?= $isWishlist ? 'disabled' : '' ?>>
                                        <label class="form-check-label" for="loaned">
                                            <i class="bi bi-person-check"></i> Loaned
                                            <?php if ($isWishlist): ?>
                                                <small class="text-muted">(Wishlist items cannot be loaned)</small>
                                            <?php endif; ?>
                                        </label>

                                        <?php 
                                        $loanedPersonName = '';
                                        $loanPersonId = '';
                                        if (isset($currentLoan) && $currentLoan) {
                                            $loanedPersonName = $currentLoan['person_name'];
                                            $loanPersonId = $currentLoan['person_id'];
                                        }
                                        ?>
                                        <span id="loaned_person_name" class="badge bg-info text-dark <?= $movie['loaned'] ? '' : 'd-none' ?>"><?= esc(old('loaned_person_name', $loanedPersonName)) ?></span>
                                        <input type="hidden" id="loan_person_id" name="loan_person_id" value="<?= esc(old('loan_person_id', $loanPersonId)) ?>">
                                        <input type="hidden" id="loaned_person_name_input" name="loaned_person_name" value="<?= esc(old('loaned_person_name', $loanedPersonName)) ?>">
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
                                    <input type="url" class="form-control" id="site" name="site"
                                           value="<?= esc(old('site', $movie['site'])) ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="o_site" class="form-label">Original Site URL</label>
                                    <input type="url" class="form-control" id="o_site" name="o_site"
                                           value="<?= esc(old('o_site', $movie['o_site'])) ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="trailer" class="form-label">Trailer URL</label>
                                    <input type="url" class="form-control" id="trailer" name="trailer"
                                           value="<?= esc(old('trailer', $movie['trailer'])) ?>">
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
                                    <input type="text" class="form-control" id="screenplay" name="screenplay"
                                           value="<?= esc(old('screenplay', $movie['screenplay'])) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="cameraman" class="form-label">Cameraman</label>
                                    <input type="text" class="form-control" id="cameraman" name="cameraman"
                                           value="<?= esc(old('cameraman', $movie['cameraman'])) ?>">
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
                            <textarea class="form-control" id="cast" name="cast" rows="4"><?= esc(old('cast', $movie['cast'])) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="plot" class="form-label">Plot</label>
                            <textarea class="form-control" id="plot" name="plot" rows="6"><?= esc(old('plot', $movie['plot'])) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="4"><?= esc(old('notes', $movie['notes'])) ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="card">
                    <div class="card-body">
                        <!-- API Fetch Section - Only show if API is available -->
                        <?php if (isset($apiAvailable) && $apiAvailable): ?>
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card bg-light">
                                        <div class="card-header">
                                            <h6 class="mb-0"><i class="bi bi-cloud-download"></i> Auto-Fill from Online Database</h6>
                                        </div>
                                        <div class="card-body">
                                            <p class="text-muted mb-3">
                                                Automatically fetch media information from The Movie Database (TMDB).
                                                This will fill in fields like plot, cast, runtime, and prepare a poster preview.
                                                Changes are not saved until you click "Update Media".
                                            </p>
                                            <div class="input-group">
                                                <label for="apiSearch" class="visually-hidden">Search TMDB</label>
                                                <select class="form-select flex-shrink-0" style="max-width: 140px" id="apiType" aria-label="TMDB Search Type">
                                                    <option value="movie" selected>Movies</option>
                                                    <option value="tv">TV</option>
                                                </select>
                                                <input type="text" class="form-control" id="apiSearch" placeholder="Search TMDB by title or paste TMDB ID" value="<?= esc($movie['title'] ?: $movie['o_title'] ?: '') ?>">
                                                <button type="button" id="fetchFromApi" class="btn btn-info">
                                                    <i class="bi bi-cloud-download"></i> Fetch from TMDB
                                                </button>
                                            </div>
                                            <small class="text-muted">Tip: Choose Movies or TV, enter a different title to search, or paste a TMDB numeric ID directly (IDs overlap between Movies and TV).</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php elseif (isset($apiAvailable) && !$apiAvailable): ?>
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card bg-light border-warning">
                                        <div class="card-header bg-warning bg-opacity-25">
                                            <h6 class="mb-0"><i class="bi bi-info-circle"></i> Online Database Integration</h6>
                                        </div>
                                        <div class="card-body">
                                            <p class="text-muted mb-2">
                                                To enable automatic movie data fetching from The Movie Database (TMDB),
                                                please add your API key to the environment configuration.
                                            </p>
                                            <small class="text-muted">
                                                <strong>Steps:</strong><br>
                                                1. Get a free API key from <a href="https://www.themoviedb.org/settings/api" target="_blank" class="text-decoration-none">themoviedb.org <i class="bi bi-box-arrow-up-right"></i></a><br>
                                                2. Add <code>TMDB_API_KEY=your_api_key_here</code> to your .env file<br>
                                                3. Restart your web server
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Regular Submit Buttons -->
                        <div class="text-center">
                            <button type="submit" class="btn btn-success btn-lg me-3">
                                <i class="bi bi-check-lg"></i> Update Media
                            </button>
                            <a href="<?= base_url('media/view/' . $movie['movie_id']) ?>" class="btn btn-secondary btn-lg me-3">
                                <i class="bi bi-x-lg"></i> Cancel
                            </a>
                            <a href="<?= base_url('media') ?>" class="btn btn-outline-secondary btn-lg">
                                <i class="bi bi-arrow-left"></i> Back to Library
                            </a>
                        </div>
                    </div>
                </div>
            </form>
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
            const mediaId = <?= $movie['movie_id'] ?>;

            if (loanedCheckbox) {
                loanedCheckbox.addEventListener('change', function() {
                    if (this.checked) {
                        showLoanModal(function(personId, personName) {
                            // Use AJAX to loan media immediately
                            fetch(`<?= base_url('media/loan/') ?>${mediaId}`, {
                                method: 'POST',
                                headers: { 
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: 'person_id=' + personId
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    loanPersonIdInput.value = personId;
                                    loanedPersonNameInput.value = personName;
                                    loanedPersonBadge.textContent = personName;
                                    loanedPersonBadge.classList.remove('d-none');
                                } else {
                                    alert(data.message);
                                    loanedCheckbox.checked = false;
                                }
                            })
                            .catch(() => {
                                alert('Error processing loan');
                                loanedCheckbox.checked = false;
                            });
                        }, function() {
                            // If modal was closed without confirming, uncheck the checkbox
                            if (!loanPersonIdInput.value) {
                                loanedCheckbox.checked = false;
                            }
                        });
                    } else {
                        // Check if it was already loaned in the database
                        // In edit mode, we want to save return status immediately
                        if (!confirm('Are you sure you want to mark this media as returned?')) {
                            this.checked = true;
                            return;
                        }
                        
                        // Use AJAX to return media immediately
                        fetch(`<?= base_url('media/returnLoan/') ?>${mediaId}`, {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                loanPersonIdInput.value = '';
                                loanedPersonNameInput.value = '';
                                loanedPersonBadge.classList.add('d-none');
                                loanedPersonBadge.textContent = '';
                            } else {
                                alert(data.message);
                                this.checked = true;
                            }
                        })
                        .catch(() => {
                            alert('Error processing return');
                            this.checked = true;
                        });
                    }
                });
            }

            // Handle poster image error
            const posterImg = document.querySelector('.poster-image');
            if (posterImg) {
                posterImg.addEventListener('error', function() {
                    this.parentNode.innerHTML = '<div class=\'bg-light rounded d-flex align-items-center justify-content-center shadow-sm\' style=\'height: 300px; width: 200px; margin: 0 auto;\'><i class=\'bi bi-film display-4 text-muted\'></i></div><p class=\'text-muted mt-2\'>No poster available</p>';
                });
            }

            // Auto-resize textareas
            const textareas = document.querySelectorAll('textarea');
            textareas.forEach(textarea => {
                textarea.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight) + 'px';
                });
            });


            // Digital-only fields visibility based on medium selection\n            const mediumSelect = document.getElementById('medium_id');\n            const digitalFields = document.getElementById('digital-only-fields');\n            const vcodecInput = document.getElementById('vcodec_id');\n            const ratioInput = document.getElementById('ratio_id');\n            const widthInput = document.getElementById('width');\n            const heightInput = document.getElementById('height');\n\n            function isDigitalMediumName(name) {\n                if (!name) return false;\n                const n = name.toLowerCase();\n                return n.includes('digital') || n.includes('file');\n            }\n\n            function updateDigitalFieldsVisibility() {\n                const selected = mediumSelect ? mediumSelect.options[mediumSelect.selectedIndex] : null;\n                const name = selected ? selected.textContent : '';\n                const isDigital = isDigitalMediumName(name);\n                if (digitalFields) {\n                    digitalFields.style.display = isDigital ? '' : 'none';\n                }\n                [vcodecInput, ratioInput, widthInput, heightInput].forEach(el => {\n                    if (el) el.disabled = !isDigital;\n                });\n            }\n\n            if (mediumSelect) {\n                mediumSelect.addEventListener('change', updateDigitalFieldsVisibility);\n                updateDigitalFieldsVisibility(); // initialize\n            }\n\n            // Function to apply fetched data to the form
            function applyFetchedData(data) {
                if (data.title) document.getElementById('title').value = data.title;
                if (data.o_title) document.getElementById('o_title').value = data.o_title;
                if (data.director) document.getElementById('director').value = data.director;
                if (data.year) document.getElementById('year').value = data.year;
                if (data.runtime) document.getElementById('runtime').value = data.runtime;
                if (data.genre) document.getElementById('genre').value = data.genre;
                if (data.country) document.getElementById('country').value = data.country;
                if (data.studio) document.getElementById('studio').value = data.studio;
                if (data.classification) document.getElementById('classification').value = data.classification;
                if (data.plot) document.getElementById('plot').value = data.plot;
                if (data.cast) document.getElementById('cast').value = data.cast;
                if (data.notes) document.getElementById('notes').value = data.notes;
                if (data.site) document.getElementById('site').value = data.site;
                if (data.rating) document.getElementById('rating').value = data.rating;

                // Auto-resize textareas after updating
                textareas.forEach(textarea => {
                    textarea.style.height = 'auto';
                    textarea.style.height = (textarea.scrollHeight) + 'px';
                });

                // Poster selection UI: show both existing and fetched, let the user choose
                const hiddenPosterInput = document.getElementById('fetched_poster_url');
                const compareCard = document.getElementById('poster-compare-container');
                const fetchedImg = document.getElementById('fetched-poster-img');
                const fetchedPlaceholder = document.getElementById('fetched-poster-placeholder');
                const radioFetched = document.getElementById('poster_choice_fetched');
                const radioExisting = document.getElementById('poster_choice_existing');
                const hasExistingPoster = <?= $movie['poster_md5'] ? 'true' : 'false' ?>;

                if (data.poster_url) {
                    hiddenPosterInput.value = data.poster_url;

                    if (fetchedImg) {
                        fetchedImg.src = data.poster_url;
                        fetchedImg.style.display = '';
                    }
                    if (fetchedPlaceholder) {
                        fetchedPlaceholder.style.display = 'none';
                    }
                    if (compareCard) {
                        compareCard.style.display = '';
                    }
                    if (radioFetched) {
                        radioFetched.disabled = false;
                        radioFetched.checked = true; // default to new poster when one is fetched
                    }
                    if (radioExisting && hasExistingPoster) {
                        radioExisting.disabled = false;
                    }
                } else {
                    if (hiddenPosterInput) hiddenPosterInput.value = '';
                    if (fetchedImg) {
                        fetchedImg.src = '';
                        fetchedImg.style.display = 'none';
                    }
                    if (fetchedPlaceholder) {
                        fetchedPlaceholder.style.display = '';
                    }
                    // If there is no fetched poster, hide the compare card unless there is an existing poster and user had it open
                    if (compareCard && !hasExistingPoster) {
                        compareCard.style.display = 'none';
                    }
                    if (radioFetched) radioFetched.checked = false;
                    if (radioExisting && hasExistingPoster) radioExisting.checked = true;
                }
            }

            // Function to show multiple results modal
            function showMultipleResultsEdit(results, lookupType, page = 1, totalPages = 1, totalResults = 0, searchParams = {}) {
                const modalHtml = `
                    <div class="modal fade" id="selectMediaModalEdit" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Select a Match (${totalResults} results found)</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body" style="max-height: 600px; overflow-y: auto;">
                                    <div class="list-group" id="mediaResultsListEdit">
                                        ${results.map((result, index) => `
                                            <a href="#" class="list-group-item list-group-item-action media-result-item-edit" data-index="${index}">
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
                                                        ${result.year ? `<small class="text-muted">Year: ${result.year}</small>` : ''}
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
                                        <button type="button" class="btn btn-secondary" id="prevPageBtnEdit" ${page <= 1 ? 'disabled' : ''}>
                                            <i class="bi bi-chevron-left"></i> Previous
                                        </button>
                                        <span>Page ${page} of ${totalPages}</span>
                                        <button type="button" class="btn btn-secondary" id="nextPageBtnEdit" ${page >= totalPages ? 'disabled' : ''}>
                                            Next <i class="bi bi-chevron-right"></i>
                                        </button>
                                    </div>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                `;

                const existingModal = document.getElementById('selectMediaModalEdit');
                if (existingModal) existingModal.remove();

                document.body.insertAdjacentHTML('beforeend', modalHtml);

                const modal = new bootstrap.Modal(document.getElementById('selectMediaModalEdit'));
                modal.show();

                // Handle pagination
                if (totalPages > 1) {
                    const prevBtn = document.getElementById('prevPageBtnEdit');
                    const nextBtn = document.getElementById('nextPageBtnEdit');

                    if (prevBtn) {
                        prevBtn.addEventListener('click', function() {
                            modal.hide();
                            loadPageEdit(page - 1, searchParams, lookupType);
                        });
                    }

                    if (nextBtn) {
                        nextBtn.addEventListener('click', function() {
                            modal.hide();
                            loadPageEdit(page + 1, searchParams, lookupType);
                        });
                    }
                }

                document.querySelectorAll('.media-result-item-edit').forEach(item => {
                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        const index = parseInt(this.dataset.index);
                        const selected = results[index];

                        modal.hide();

                        // Fetch full details for the selected item
                        fetchBtn.disabled = true;
                        const oldHtml = fetchBtn.innerHTML;
                        fetchBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Loading...';

                        fetch('<?= base_url('media/fetchDetails') ?>', {
                            method: 'POST',
                            headers: { 
                                'X-Requested-With': 'XMLHttpRequest', 
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ tmdb_id: selected.tmdb_id, type: selected.type })
                        })
                        .then(r => {
                            if (!r.ok && r.status === 403) {
                                throw new Error('CSRF validation failed. Please refresh the page.');
                            }
                            return r.json();
                        })
                        .then(res => {
                            if (res.success && res.data) {
                                applyFetchedData(res.data);
                                showMessage(res.message + (res.data.poster_url ? ' (Poster ready to save)' : ''), 'success');
                            } else {
                                showMessage(res.message || 'Failed to fetch details', 'error');
                            }
                        })
                        .catch(() => {
                            showMessage('An error occurred while fetching details.', 'error');
                        })
                        .finally(() => {
                            fetchBtn.disabled = false;
                            fetchBtn.innerHTML = oldHtml;
                        });
                    });
                });
            }

            function loadPageEdit(page, searchParams, lookupType) {
                const mediaId = <?= $movie['movie_id'] ?>;

                fetchBtn.disabled = true;
                const oldHtml = fetchBtn.innerHTML;
                fetchBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Loading...';

                const payload = { ...searchParams, page: page };

                fetch(`<?= base_url('media/fetchFromApi/') ?>${mediaId}`, {
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
                    if (res.success && res.multiple && res.results) {
                        showMultipleResultsEdit(res.results, lookupType, res.page, res.total_pages, res.total_results, searchParams);
                    } else {
                        showMessage(res.message || 'No more results', 'error');
                    }
                })
                .catch(() => {
                    showMessage('An error occurred while loading more results.', 'error');
                })
                .finally(() => {
                    fetchBtn.disabled = false;
                    fetchBtn.innerHTML = oldHtml;
                });
            }

            // Handle API fetch button - Only if it exists (API is available)
            const fetchBtn = document.getElementById('fetchFromApi');
            if (fetchBtn) {
                fetchBtn.addEventListener('click', function() {
                    const mediaId = <?= $movie['movie_id'] ?>;

                    // Disable button and show loading
                    this.disabled = true;
                    const originalHtml = this.innerHTML;
                    this.innerHTML = '<i class="bi bi-hourglass-split"></i> Fetching data...';

                    const searchInput = document.getElementById('apiSearch');
                    const yearInput = document.getElementById('year');
                    const query = (searchInput && searchInput.value.trim()) ? searchInput.value.trim() : (document.getElementById('title')?.value || '');
                    const yearVal = (yearInput && yearInput.value) ? parseInt(yearInput.value, 10) : null;
                    const lookupType = document.getElementById('apiType')?.value || 'movie';

                    const searchParams = { query: query, year: yearVal, type: lookupType };

                    fetch(`<?= base_url('media/fetchFromApi/') ?>${mediaId}`, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(searchParams)
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                if (data.multiple && data.results) {
                                    // Show selection modal with pagination
                                    showMultipleResultsEdit(
                                        data.results,
                                        lookupType,
                                        data.page || 1,
                                        data.total_pages || 1,
                                        data.total_results || data.results.length,
                                        searchParams
                                    );
                                    showMessage(data.message, 'success');
                                } else {
                                    // Single result - apply directly
                                    applyFetchedData(data.data);
                                    showMessage(data.message + (data.data.poster_url ? ' (Poster ready to save)' : ''), 'success');
                                }
                            } else {
                                showMessage(data.message, 'error');
                            }
                        })
                        .catch(() => {
                            showMessage('An error occurred while fetching media data', 'error');
                        })
                        .finally(() => {
                            this.disabled = false;
                            this.innerHTML = originalHtml;
                        });
                });
            }
        });

        function showMessage(message, type) {
            // Create a temporary alert
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
            alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 500px;';
            alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

            document.body.appendChild(alertDiv);

            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.parentNode.removeChild(alertDiv);
                }
            }, 5000);
        }
    </script>
<?= $this->endsection() ?>