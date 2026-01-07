<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?php
$tag = isset($tag) && is_array($tag) ? $tag : ['name' => 'Unknown'];
$movies = isset($movies) && is_array($movies) ? $movies : [];
$movieCount = isset($movieCount) ? $movieCount : count($movies);
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>
                    <i class="bi bi-tag"></i> <?= esc($tag['name']) ?>
                    <span class="badge bg-primary"><?= $movieCount ?> movie<?= $movieCount !== 1 ? 's' : '' ?></span>
                </h2>
                <a href="<?= base_url('database-maintenance/manage-lookups#tags') ?>" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Lookup Tables
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-film"></i> Movies with this Tag</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($movies)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> No movies are currently tagged with "<?= esc($tag['name']) ?>".
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;">Poster</th>
                                        <th>Title</th>
                                        <th>Year</th>
                                        <th>Runtime</th>
                                        <th>Format</th>
                                        <th>Rating</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($movies as $movie): ?>
                                        <tr>
                                            <td>
                                                <?php if ($movie['poster_md5']): ?>
                                                    <img src="<?= base_url('movies/poster/' . $movie['movie_id']) . '?v=' . urlencode($movie['poster_md5']) ?>"
                                                         class="img-thumbnail"
                                                         alt="<?= esc($movie['title'] ?: $movie['o_title']) ?>"
                                                         style="max-width: 60px; max-height: 90px;">
                                                <?php else: ?>
                                                    <div class="bg-light d-flex align-items-center justify-content-center"
                                                         style="width: 60px; height: 90px;">
                                                        <i class="bi bi-film text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?= esc($movie['title'] ?: $movie['o_title'] ?: 'Untitled') ?></strong>
                                                <?php if ($movie['title'] && $movie['o_title'] && $movie['title'] !== $movie['o_title']): ?>
                                                    <br><small class="text-muted"><?= esc($movie['o_title']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= $movie['year'] ?: '<span class="text-muted">-</span>' ?></td>
                                            <td><?= $movie['runtime'] ? $movie['runtime'] . ' min' : '<span class="text-muted">-</span>' ?></td>
                                            <td><?= $movie['medium_name'] ? esc($movie['medium_name']) : '<span class="text-muted">-</span>' ?></td>
                                            <td>
                                                <?php if ($movie['rating']): ?>
                                                    <span class="text-warning">
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
                                            <td>
                                                <?php if ($movie['seen']): ?>
                                                    <span class="badge bg-success"><i class="bi bi-eye"></i> Seen</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning"><i class="bi bi-eye-slash"></i> Unseen</span>
                                                <?php endif; ?>

                                                <?php if ($movie['loaned']): ?>
                                                    <span class="badge bg-danger"><i class="bi bi-person-check"></i> Loaned</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <a href="<?= base_url('movies/view/' . $movie['movie_id']) ?>"
                                                   class="btn btn-sm btn-primary">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endsection() ?>
