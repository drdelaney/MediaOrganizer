<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?php
/** @var array $movies */
/** @var array $mediaTypes */
/** @var string|null $search */
/** @var string|null $selectedMedium */
/** @var bool $wishlist */
/** @var bool $alpha */
$search = $search ?? '';
$mediaTypes = $mediaTypes ?? [];
$typesMap = [];
foreach ($mediaTypes as $type) {
    $typesMap[$type['medium_id']] = $type['name'];
}
$selectedMedium = $selectedMedium ?? '';
$wishlist = $wishlist ?? false;
$alpha = $alpha ?? false;
?>
<div class="row mb-4">
    <div class="col">
        <h1>Media List</h1>
    </div>
</div>

<div class="row mb-4">
    <div class="col">
        <form action="<?= base_url('public') ?>" method="post" class="row g-3">
            <?= csrf_field() ?>
            <div class="col-md-2">
                <label for="searchInput" class="visually-hidden">Search media</label>
                <input type="text" name="search" id="searchInput" class="form-control" placeholder="Search media..." value="<?= esc($search) ?>">
            </div>
            <div class="col-md-2">
                <label for="mediumSelect" class="visually-hidden">Media Type</label>
                <select name="medium_id" id="mediumSelect" class="form-select">
                    <option value="">All Media Types</option>
                    <?php foreach ($mediaTypes as $type): ?>
                        <option value="<?= $type['medium_id'] ?>" <?= $selectedMedium == $type['medium_id'] ? 'selected' : '' ?>>
                            <?= esc($type['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center pt-2">
                    <div class="form-check me-3">
                        <input class="form-check-input" type="checkbox" name="wishlist" value="1" id="wishlistCheck" <?= $wishlist ? 'checked' : '' ?>>
                        <label class="form-check-label" for="wishlistCheck">
                            Wishlist Only
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="alpha" value="1" id="alphaCheck" <?= (isset($alpha) && $alpha) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="alphaCheck">
                            Sort Alphabetical
                        </label>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
            <div class="col-md-2">
                <form action="<?= base_url('public') ?>" method="post">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-secondary w-100">Clear</button>
                </form>
            </div>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th style="width: 120px;">Type</th>
                <th>Media Name</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($movies)): ?>
                <tr>
                    <td colspan="2" class="text-center">No media found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($movies as $movie): ?>
                    <tr>
                        <td><?= esc($movie['medium_name'] ?? 'Unknown') ?></td>
                        <td>
                            <?php
                            $tooltipContent = 'Classification: ' . esc($movie['classification'] ?: 'N/A') . "\n" .
                                'Runtime: ' . ($movie['runtime'] ? esc($movie['runtime']) . ' min' : 'N/A') . "\n" .
                                'Rating: ' . ($movie['rating'] ? esc($movie['rating']) . '/5' : 'N/A') . "\n" .
                                'Media Types: ';

                            $mediumNames = [];
                            if (!empty($movie['notes']) && strpos($movie['notes'], '<!medium_id>') !== false) {
                                $mediumIds = get_medium_ids_from_notes($movie['notes']);
                                if (!empty($mediumIds)) {
                                    foreach ($mediumIds as $mId) {
                                        if (isset($typesMap[$mId])) {
                                            $mediumNames[] = $typesMap[$mId];
                                        }
                                    }
                                }
                            }
                            if (empty($mediumNames) && !empty($movie['medium_name'])) {
                                $mediumNames = [$movie['medium_name']];
                            }
                            $tooltipContent .= !empty($mediumNames) ? esc(implode(', ', $mediumNames)) : 'N/A';
                            $tooltipContent .= "\n" . 'Status: ' . ($movie['loaned'] ? 'Loaned' : 'Available');
                            ?>
                            <span data-bs-toggle="tooltip" data-bs-html="true" data-bs-custom-class="public-tooltip" style="cursor: help;" title="<?= esc($tooltipContent, 'attr') ?>">
                                <?= esc($movie['title'] ?: $movie['o_title']) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Use event delegation for tooltips if possible, or initialize only visible ones.
        // For simplicity and compatibility with Bootstrap 5, we keep initialization but use a more efficient selector.
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('.public-tooltip'))
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                trigger: 'hover',
                boundary: 'viewport',
                delay: { "show": 200, "hide": 100 }
            })
        })

        var wishlistCheck = document.getElementById('wishlistCheck');
        var alphaCheck = document.getElementById('alphaCheck');
        var mediumSelect = document.getElementById('mediumSelect');
        var searchInput = document.getElementById('searchInput');
        var filterForm = wishlistCheck.closest('form');

        wishlistCheck.addEventListener('change', function() {
            if (this.checked) {
                searchInput.value = '';
            }
            filterForm.submit();
        });

        alphaCheck.addEventListener('change', function() {
            filterForm.submit();
        });

        mediumSelect.addEventListener('change', function() {
            filterForm.submit();
        });
    })
</script>
<?= $this->endSection() ?>
