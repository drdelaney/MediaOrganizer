<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?php
/** @var int $total */
/** @var string|null $search */
/** @var string $searchField */
/** @var string|null $tagId */
/** @var array $tags */
/** @var array $mediaTypes */
/** @var int $currentPage */
/** @var int $perPage */
$total = $total ?? 0;
$search = $search ?? '';
$searchField = $searchField ?? 'title';
$tagId = $tagId ?? '';
$tags = $tags ?? [];
$mediaTypes = $mediaTypes ?? [];
$currentPage = $currentPage ?? 1;
$perPage = $perPage ?? 20;
?>

<div class="row">
    <div class="col-12">
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

        <h1 class="mb-4">
            <i class="bi bi-collection-play"></i> Media Library
            <small class="text-muted fs-6">(<?= number_format($total) ?> entries)</small>
        </h1>

        <!-- Search Form -->
        <div class="search-form mb-4">
            <form method="post" action="<?= base_url('movies') ?>">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="visually-hidden">Search</label>
                        <input type="text" class="form-control" name="search" id="search"
                               value="<?= esc($search) ?>" 
                               placeholder="Search movies...">
                    </div>
                    <div class="col-md-3">
                        <label for="searchField" class="visually-hidden">Search Field</label>
                        <select class="form-select" name="searchField" id="searchField">
                            <option value="title" <?= $searchField === 'title' ? 'selected' : '' ?>>Title</option>
                            <option value="o_title" <?= $searchField === 'o_title' ? 'selected' : '' ?>>Original Title</option>
                            <option value="director" <?= $searchField === 'director' ? 'selected' : '' ?>>Director</option>
                            <option value="genre" <?= $searchField === 'genre' ? 'selected' : '' ?>>Genre</option>
                            <option value="country" <?= $searchField === 'country' ? 'selected' : '' ?>>Country</option>
                            <option value="studio" <?= $searchField === 'studio' ? 'selected' : '' ?>>Studio</option>
                            <option value="movie_id" <?= $searchField === 'movie_id' ? 'selected' : '' ?>>Media ID</option>
                            <option value="all" <?= $searchField === 'all' ? 'selected' : '' ?>>All Fields</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="tag" class="visually-hidden">Tag</label>
                        <select class="form-select" name="tag" id="tag">
                            <option value="">All Tags</option>
                            <?php 
                            foreach ($tags as $tag): 
                                $isSelected = $tagId == $tag['tag_id'];
                            ?>
                                <option value="<?= $tag['tag_id'] ?>" <?= $isSelected ? 'selected' : '' ?>>
                                    <?= esc($tag['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100" title="Search">
                                <i class="bi bi-search"></i>
                            </button>
                            <form method="post" action="<?= base_url('movies') ?>" class="w-100">
                                <?= csrf_field() ?>
                                <input type="hidden" name="serendipitous" value="1">
                                <button type="submit" class="btn btn-warning w-100" title="Feeling Serendipitous!">
                                    <i class="bi bi-dice-5"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex gap-2">
                            <?php if ($search || $tagId): ?>
                                <form method="post" action="<?= base_url('movies') ?>" class="w-100">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-outline-secondary w-100">
                                        <i class="bi bi-x-circle"></i> Clear
                                    </button>
                                </form>
                            <?php endif; ?>
                            <a href="<?= base_url('movies/add') ?>" class="btn btn-success w-100">
                                <i class="bi bi-plus-circle"></i> Add
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Movies Table -->
        <?php if (empty($movies)): ?>
            <div class="text-center py-5">
                <i class="bi bi-film display-1 text-muted"></i>
                <h3 class="mt-3 text-muted">No movies found</h3>
                <?php if ($search || $tagId): ?>
                    <p class="text-muted">Try adjusting your search criteria</p>
                    <form method="post" action="<?= base_url('movies') ?>" class="d-inline">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-primary">Clear All Filters</button>
                    </form>
                <?php else: ?>
                    <p class="text-muted">Start building your movie collection</p>
                    <a href="<?= base_url('movies/add') ?>" class="btn btn-success">Add Your First Movie</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 60px;">Poster</th>
                            <th>Title</th>
                            <th>Director</th>
                            <th>Year</th>
                            <th>Genre</th>
                            <th>Medium</th>
                            <th>Rating</th>
                            <th>Status</th>
                            <th style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movies as $movie): ?>
                            <tr class="movie-row">
                                <!-- Poster -->
                                <td>
                                    <?php if ($movie['poster_md5']): ?>
                                        <img src="<?= base_url('movies/poster/' . $movie['movie_id']) ?>" 
                                             class="poster-thumbnail" 
                                             alt="<?= esc($movie['title'] ?: $movie['o_title']) ?>"
                                             loading="lazy">
                                    <?php else: ?>
                                        <div class="bg-light d-flex align-items-center justify-content-center poster-thumbnail">
                                            <i class="bi bi-film text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- Title -->
                                <td>
                                    <div>
                                        <strong><?= esc($movie['title'] ?: $movie['o_title'] ?: 'Untitled') ?></strong>
                                        <?php if ($movie['title'] && $movie['o_title'] && $movie['title'] !== $movie['o_title']): ?>
                                            <br><small class="text-muted"><?= esc($movie['o_title']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- Director -->
                                <td><?= $movie['director'] ? esc($movie['director']) : '<span class="text-muted">-</span>' ?></td>

                                <!-- Year -->
                                <td><?= $movie['year'] ? esc($movie['year']) : '<span class="text-muted">-</span>' ?></td>

                                <!-- Genre -->
                                <td><?= $movie['genre'] ? esc($movie['genre']) : '<span class="text-muted">-</span>' ?></td>

                                <!-- Medium - Multiple formats -->
                                <td>
                                    <?php 
                                    // Get all medium formats from notes
                                    $mediumIds = get_medium_ids_from_notes($movie['notes'] ?? null);
                                    $mediumNames = [];
                                    if (!empty($mediumIds)) {
                                        foreach ($mediumIds as $mediumId) {
                                            foreach ($mediaTypes as $media) {
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
                                    echo !empty($mediumNames) ? esc(implode(', ', $mediumNames)) : '<span class="text-muted">-</span>';
                                    ?>
                                </td>

                                <!-- Rating -->
                                <td>
                                    <?php if ($movie['rating']): ?>
                                        <span class="rating-stars">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <?php if ($i <= $movie['rating']): ?>
                                                    <i class="bi bi-star-fill"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-star"></i>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Status -->
                                <td>
                                    <div>
                                        <?php if ($movie['seen']): ?>
                                            <span class="badge bg-success"><i class="bi bi-eye"></i> Seen</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning"><i class="bi bi-eye-slash"></i> Unseen</span>
                                        <?php endif; ?>

                                        <?php 
                                        $isWishlist = false;
                                        if (isset($movie['tags']) && is_array($movie['tags'])) {
                                            foreach ($movie['tags'] as $tag) {
                                                if (strtolower($tag['name']) === 'wishlist') {
                                                    $isWishlist = true;
                                                    break;
                                                }
                                            }
                                        }
                                        ?>
                                        
                                        <?php if ($movie['loaned']): ?>
                                            <br><span class="badge bg-danger mt-1"><i class="bi bi-person-check"></i> Loaned</span>
                                        <?php elseif ($isWishlist): ?>
                                            <br><span class="badge bg-info mt-1"><i class="bi bi-heart"></i> Wishlist</span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="<?= base_url('movies/view/' . $movie['movie_id']) ?>" 
                                           class="btn btn-outline-primary" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?= base_url('movies/edit/' . $movie['movie_id']) ?>" 
                                           class="btn btn-outline-secondary" title="Edit Movie">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-outline-<?= $movie['seen'] ? 'warning' : 'success' ?> toggle-seen-btn"
                                                data-movie-id="<?= $movie['movie_id'] ?>"
                                                data-seen="<?= $movie['seen'] ?>"
                                                title="<?= $movie['seen'] ? 'Mark as Unseen' : 'Mark as Seen' ?>">
                                            <i class="bi bi-<?= $movie['seen'] ? 'eye-slash' : 'eye-fill' ?>"></i>
                                        </button>
                                        <?php if ($movie['loaned']): ?>
                                            <button type="button" 
                                                    class="btn btn-outline-info return-loan-btn"
                                                    data-movie-id="<?= $movie['movie_id'] ?>"
                                                    title="Return Movie">
                                                <i class="bi bi-arrow-return-left"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" 
                                                    class="btn btn-outline-danger loan-movie-btn"
                                                    data-movie-id="<?= $movie['movie_id'] ?>"
                                                    title="Loan Movie"
                                                    <?= $isWishlist ? 'disabled' : '' ?>>
                                                <i class="bi bi-person-check"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total > $perPage): ?>
                <?php
                $totalPages = ceil($total / $perPage);
                $startPage = max(1, $currentPage - 2);
                $endPage = min($totalPages, $currentPage + 2);
                ?>
                <nav aria-label="Movies pagination">
                    <ul class="pagination justify-content-center">
                        <!-- Previous Page -->
                        <?php if ($currentPage > 1): ?>
                            <li class="page-item">
                                <form method="post" action="<?= base_url('movies') ?>" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="page" value="<?= $currentPage - 1 ?>">
                                    <?php if ($search): ?>
                                        <input type="hidden" name="search" value="<?= esc($search) ?>">
                                        <input type="hidden" name="searchField" value="<?= esc($searchField) ?>">
                                    <?php endif; ?>
                                    <?php if ($tagId): ?>
                                        <input type="hidden" name="tag" value="<?= esc($tagId) ?>">
                                    <?php endif; ?>
                                    <button type="submit" class="page-link">
                                        <i class="bi bi-chevron-left"></i> Previous
                                    </button>
                                </form>
                            </li>
                        <?php endif; ?>

                        <!-- Page Numbers -->
                        <?php for ($page = $startPage; $page <= $endPage; $page++): ?>
                            <li class="page-item <?= $page === $currentPage ? 'active' : '' ?>">
                                <form method="post" action="<?= base_url('movies') ?>" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="page" value="<?= $page ?>">
                                    <?php if ($search): ?>
                                        <input type="hidden" name="search" value="<?= esc($search) ?>">
                                        <input type="hidden" name="searchField" value="<?= esc($searchField) ?>">
                                    <?php endif; ?>
                                    <?php if ($tagId): ?>
                                        <input type="hidden" name="tag" value="<?= esc($tagId) ?>">
                                    <?php endif; ?>
                                    <button type="submit" class="page-link">
                                        <?= $page ?>
                                    </button>
                                </form>
                            </li>
                        <?php endfor; ?>

                        <!-- Next Page -->
                        <?php if ($currentPage < $totalPages): ?>
                            <li class="page-item">
                                <form method="post" action="<?= base_url('movies') ?>" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="page" value="<?= $currentPage + 1 ?>">
                                    <?php if ($search): ?>
                                        <input type="hidden" name="search" value="<?= esc($search) ?>">
                                        <input type="hidden" name="searchField" value="<?= esc($searchField) ?>">
                                    <?php endif; ?>
                                    <?php if ($tagId): ?>
                                        <input type="hidden" name="tag" value="<?= esc($tagId) ?>">
                                    <?php endif; ?>
                                    <button type="submit" class="page-link">
                                        Next <i class="bi bi-chevron-right"></i>
                                    </button>
                                </form>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>

                <!-- Page Jump Dropdown -->
                <div class="d-flex justify-content-center align-items-center mb-2">
                    <form class="d-flex align-items-center gap-2" method="post" action="<?= base_url('movies') ?>">
                        <?= csrf_field() ?>
                        <?php if ($search): ?>
                            <input type="hidden" name="search" value="<?= esc($search) ?>">
                            <input type="hidden" name="searchField" value="<?= esc($searchField) ?>">
                        <?php endif; ?>
                        <label for="pageSelect" class="me-2 mb-0">Page:</label>
                        <select id="pageSelect" name="page" class="form-select" style="width: auto;" onchange="this.form.submit()">
                            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                                <option value="<?= $p ?>" <?= $p === $currentPage ? 'selected' : '' ?>><?= $p ?> / <?= $totalPages ?></option>
                            <?php endfor; ?>
                        </select>
                        <noscript>
                            <button type="submit" class="btn btn-outline-primary">Go</button>
                        </noscript>
                    </form>
                </div>

                <!-- Pagination Info -->
                <div class="text-center text-muted">
                    Showing <?= ($currentPage - 1) * $perPage + 1 ?> to <?= min($currentPage * $perPage, $total) ?> of <?= number_format($total) ?> entries
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Loan Movie Modal -->
<div id="loanModalPlaceholder"></div>

<?= $this->include('movies/_quick_add_modals') ?>

<?= $this->endsection() ?>

<?= $this->section('scripts') ?>
<style>
    /* Auto-focus search input */
    .search-form {
        background: var(--bs-secondary-bg, #f8f9fa);
        border-radius: 0.375rem;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .poster-thumbnail {
        transition: transform 0.2s ease;
    }
    
    .poster-thumbnail:hover {
        transform: scale(1.1);
    }
    
    /* Dark mode enhancements */
    [data-bs-theme="dark"] .search-form {
        background: var(--bs-secondary-bg);
        border: 1px solid #495057;
    }
</style>

<script>
    // Auto-focus search input and existing functionality
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput && !searchInput.value) {
            searchInput.focus();
        }

        // Toggle seen/unseen status
        document.querySelectorAll('.toggle-seen-btn').forEach(function(toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                const movieId = this.dataset.movieId;
                
                // Disable button during request
                this.disabled = true;
                const originalHtml = this.innerHTML;
                this.innerHTML = '<i class="bi bi-hourglass-split"></i>';

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
                        this.className = `btn btn-outline-${newSeen ? 'warning' : 'success'} toggle-seen-btn`;
                        this.innerHTML = `<i class="bi bi-${newSeen ? 'eye-slash' : 'eye-fill'}"></i>`;
                        this.title = newSeen ? 'Mark as Unseen' : 'Mark as Seen';
                        
                        // Update status badge in the same row
                        const row = this.closest('tr');
                        const statusCell = row.querySelector('td:nth-child(8)');
                        if (statusCell) {
                            const seenBadge = statusCell.querySelector('.badge');
                            if (seenBadge) {
                                if (newSeen) {
                                    seenBadge.className = 'badge bg-success';
                                    seenBadge.innerHTML = '<i class="bi bi-eye"></i> Seen';
                                } else {
                                    seenBadge.className = 'badge bg-warning';
                                    seenBadge.innerHTML = '<i class="bi bi-eye-slash"></i> Unseen';
                                }
                            }
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
        });

        // Loan movie functionality
        let currentLoanMovieId = null;

        // Load people list when loan button is clicked
        document.querySelectorAll('.loan-movie-btn').forEach(function(loanBtn) {
            loanBtn.addEventListener('click', function() {
                currentLoanMovieId = this.dataset.movieId;
                
                showLoanModal(function(personId) {
                    const confirmBtn = document.getElementById('confirmLoanBtn');
                    
                    confirmBtn.disabled = true;
                    confirmBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';

                    fetch(`<?= base_url('movies/loan/') ?>${currentLoanMovieId}`, {
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
                            showMessage(data.message, 'success');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(() => {
                        alert('Error processing loan');
                    })
                    .finally(() => {
                        confirmBtn.disabled = false;
                        confirmBtn.innerHTML = '<i class="bi bi-check-circle"></i> Confirm Loan';
                    });
                });
            });
        });

        // Return loan functionality
        document.querySelectorAll('.return-loan-btn').forEach(function(returnBtn) {
            returnBtn.addEventListener('click', function() {
                const movieId = this.dataset.movieId;
                
                if (!confirm('Are you sure you want to mark this movie as returned?')) {
                    return;
                }

                this.disabled = true;
                const originalHtml = this.innerHTML;
                this.innerHTML = '<i class="bi bi-hourglass-split"></i>';

                fetch(`<?= base_url('movies/returnLoan/') ?>${movieId}`, {
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
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        this.innerHTML = originalHtml;
                        this.disabled = false;
                        showMessage(data.message, 'error');
                    }
                })
                .catch(() => {
                    this.innerHTML = originalHtml;
                    this.disabled = false;
                    showMessage('Error processing return', 'error');
                });
            });
        });
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
</script>
<?= $this->endsection() ?>
