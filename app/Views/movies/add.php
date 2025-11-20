<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?php 
$movie = isset($movie) && is_array($movie) ? $movie : []; 
$apiAvailable = isset($apiAvailable) ? (bool)$apiAvailable : false; 
$mediaTypes = isset($mediaTypes) && is_array($mediaTypes) ? $mediaTypes : []; 
$collections = isset($collections) && is_array($collections) ? $collections : []; 
$volumes = isset($volumes) && is_array($volumes) ? $volumes : []; 
$videoCodecs = isset($videoCodecs) && is_array($videoCodecs) ? $videoCodecs : []; 
$ratios = isset($ratios) && is_array($ratios) ? $ratios : []; 
?>

<div class="row">
    <div class="col-12">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">
                <i class="bi bi-plus-circle"></i> Add Movie / TV Entry
            </h1>
            <a href="<?= base_url('movies') ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Library
            </a>
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

        <!-- Add Form -->
        <form action="<?= base_url('movies/store') ?>" method="post">
            <!-- Lookup Section (Title / External IDs) -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="bi bi-search"></i> Find by Title, IMDb ID, TVDB ID, or Barcode</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label for="lookup_type" class="form-label">Type</label>
                            <select class="form-select" id="lookup_type" name="lookup_type">
                                <option value="movie" <?= old('lookup_type', 'movie') === 'movie' ? 'selected' : '' ?>>Movie</option>
                                <option value="tv" <?= old('lookup_type') === 'tv' ? 'selected' : '' ?>>TV</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" placeholder="Enter title to search (optional)" value="<?= esc(old('title')) ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="year" class="form-label">Year</label>
                            <input type="number" class="form-control" id="year" name="year" min="1800" max="2099" value="<?= esc(old('year')) ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="imdb_id" class="form-label">IMDb ID</label>
                            <input type="text" class="form-control" id="imdb_id" name="imdb_id" placeholder="e.g. tt0133093" value="<?= esc(old('imdb_id')) ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="tvdb_id" class="form-label">TVDB ID</label>
                            <input type="text" class="form-control" id="tvdb_id" name="tvdb_id" placeholder="e.g. 8118" value="<?= esc(old('tvdb_id')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="lookup_barcode" class="form-label">Barcode (UPC/EAN)</label>
                            <input type="text" class="form-control" id="lookup_barcode" name="lookup_barcode" placeholder="e.g. 883929401120" value="<?= esc(old('lookup_barcode')) ?>">
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">
                        - Provide IMDb ID (starts with tt), TVDB numeric ID, a Title (with optional year), or scan/paste a Barcode (UPC/EAN).<br>
                        - Barcode search will first check your local library and then attempt an online UPC lookup to map to a movie/TV entry (via TMDB when available).<br>
                        - If none are provided, the entry will be created from the manual fields below.
                        <?php if (!$apiAvailable): ?>
                        <br>- TMDB API key not configured; title/ID mapping will be skipped, but basic barcode-to-title lookup will still be attempted.
                        <?php endif; ?>
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
                            <img id="previewPoster" src="" alt="Poster" class="img-fluid rounded shadow-sm" style="max-height:300px; display:none;">
                            <div id="previewPosterPlaceholder" class="bg-light rounded d-flex align-items-center justify-content-center" style="height:300px;">
                                <span class="text-muted">No poster</span>
                            </div>
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
                                <label for="o_title" class="form-label">Original Title</label>
                                <input type="text" class="form-control" id="o_title" name="o_title" value="<?= esc(old('o_title')) ?>">
                            </div>
                            <div class="mb-3">
                                <label for="director" class="form-label">Director</label>
                                <input type="text" class="form-control" id="director" name="director" value="<?= esc(old('director')) ?>">
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="runtime" class="form-label">Runtime (minutes)</label>
                                        <input type="number" class="form-control" id="runtime" name="runtime" min="0" value="<?= esc(old('runtime')) ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
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
                            <div class="mb-3">
                                <label for="studio" class="form-label">Studio</label>
                                <input type="text" class="form-control" id="studio" name="studio" value="<?= esc(old('studio')) ?>">
                            </div>
                            <div class="mb-3">
                                <label for="classification" class="form-label">Classification (MPAA/TV rating)</label>
                                <input type="text" class="form-control" id="classification" name="classification" value="<?= esc(old('classification')) ?>">
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
                                <label class="form-label">Media Formats <small class="text-muted">(select all that apply)</small></label>
                                <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                                    <?php foreach ($mediaTypes as $medium): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
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
                            </div>
                            <div class="mb-3">
                                <label for="collection_id" class="form-label">Collection</label>
                                <select class="form-select" id="collection_id" name="collection_id">
                                    <option value="">No Collection</option>
                                    <?php foreach ($collections as $collection): ?>
                                        <option value="<?= $collection['collection_id'] ?>" <?= old('collection_id') == $collection['collection_id'] ? 'selected' : '' ?>><?= esc($collection['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="volume_id" class="form-label">Volume</label>
                                <select class="form-select" id="volume_id" name="volume_id">
                                    <option value="">No Volume</option>
                                    <?php foreach ($volumes as $volume): ?>
                                        <option value="<?= $volume['volume_id'] ?>" <?= old('volume_id') == $volume['volume_id'] ? 'selected' : '' ?>><?= esc($volume['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
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
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="seen" name="seen" value="1" <?= old('seen') ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="seen"><i class="bi bi-eye"></i> Seen</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="loaned" name="loaned" value="1" <?= old('loaned') ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="loaned"><i class="bi bi-person-check"></i> Loaned</label>
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
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="card">
                <div class="card-body text-center">
                    <button type="submit" class="btn btn-success btn-lg me-3">
                        <i class="bi bi-check-lg"></i> Save
                    </button>
                    <a href="<?= base_url('movies') ?>" class="btn btn-secondary btn-lg">
                        <i class="bi bi-x-lg"></i> Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<?= $this->endsection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('lookupPreviewBtn');
        const applyBtn = document.getElementById('applyPreviewBtn');
        const previewCard = document.getElementById('previewCard');
        const previewPoster = document.getElementById('previewPoster');
        const previewPosterPlaceholder = document.getElementById('previewPosterPlaceholder');
        const previewTitle = document.getElementById('previewTitle');
        const previewSubtitle = document.getElementById('previewSubtitle');
        const previewPlot = document.getElementById('previewPlot');

        function setPreviewVisible(visible) {
            if (previewCard) previewCard.style.display = visible ? '' : 'none';
        }

        function showToast(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
            alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 500px;';
            alertDiv.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
            document.body.appendChild(alertDiv);
            setTimeout(() => { if (alertDiv.parentNode) alertDiv.parentNode.removeChild(alertDiv); }, 5000);
        }

        if (btn) {
            btn.addEventListener('click', function() {
                const payload = {
                    lookup_type: (document.getElementById('lookup_type')?.value || 'movie'),
                    title: (document.getElementById('title')?.value || '').trim(),
                    year: (document.getElementById('year')?.value || '').trim(),
                    imdb_id: (document.getElementById('imdb_id')?.value || '').trim(),
                    tvdb_id: (document.getElementById('tvdb_id')?.value || '').trim(),
                    barcode: (document.getElementById('lookup_barcode')?.value || '').trim(),
                };

                // basic guard
                if (!payload.title && !payload.imdb_id && !payload.tvdb_id && !payload.barcode) {
                    showToast('Enter a Title, IMDb ID, TVDB ID, or Barcode to search.', 'error');
                    return;
                }

                btn.disabled = true;
                const oldHtml = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Searching...';

                fetch('<?= base_url('movies/lookup') ?>', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                }).then(r => r.json())
                  .then(res => {
                      if (res.success) {
                          const d = res.data || {};
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
                          } else {
                              previewPoster.src = '';
                              previewPoster.style.display = 'none';
                              previewPosterPlaceholder.style.display = '';
                          }

                          setPreviewVisible(true);
                          showToast(res.message, 'success');

                          // Attach to apply button
                          if (applyBtn) {
                              applyBtn.onclick = function() {
                                  function setVal(id, val) { const el = document.getElementById(id); if (el && val !== undefined && val !== null) el.value = val; }
                                  setVal('title', d.title);
                                  setVal('o_title', d.o_title);
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
                      } else {
                          setPreviewVisible(false);
                          showToast(res.message || 'No results.', 'error');
                      }
                  })
                  .catch(() => {
                      setPreviewVisible(false);
                      showToast('An error occurred while searching TMDB.', 'error');
                  })
                  .finally(() => { btn.disabled = false; btn.innerHTML = oldHtml; });
            });
        }
    });
</script>
<?= $this->endsection() ?>
