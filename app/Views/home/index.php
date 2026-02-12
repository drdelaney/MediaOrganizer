<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?php $totalMovies = isset($totalMovies) ? (int)$totalMovies : 0; ?>

<div class="row">
    <div class="col-12">
        <!-- Welcome Section -->
        <div class="jumbotron bg-primary text-white rounded p-5 mb-4">
            <div class="container-fluid py-5">
                <h1 class="display-4 fw-bold">Welcome to <?= esc(app_name()) ?></h1>
                <p class="fs-4">Organize and manage your movie collection with ease.</p>
                <form method="post" action="<?= base_url('movies') ?>" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-light btn-lg me-2">
                        <i class="bi bi-collection-play"></i> My Library
                    </button>
                </form>
                <a href="<?= base_url('movies/add') ?>" class="btn btn-success btn-lg">
                    <i class="bi bi-plus-circle"></i> Add Movie/TV
                </a>
            </div>
        </div>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?= number_format($totalMovies) ?></h4>
                                <p class="mb-0">Total Movies</p>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-film display-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><i class="bi bi-gear"></i></h4>
                                <p class="mb-0">Quick Actions</p>
                            </div>
                            <div class="align-self-center">
                                <form method="post" action="<?= base_url('movies') ?>" class="d-inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-dark">
                                        Manage
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5><i class="bi bi-plus-circle"></i> Quick Start</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Get started with your media collection:</p>
                        <div class="d-grid gap-2">
                            <form method="post" action="<?= base_url('movies') ?>" class="d-grid">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-list"></i> View All Movies
                                </button>
                            </form>
                            <form method="post" action="<?= base_url('movies') ?>" class="d-grid">
                                <?= csrf_field() ?>
                                <input type="hidden" name="search" value="">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="bi bi-search"></i> Search Movies
                                </button>
                            </form>
                            <a href="<?= base_url('movies/add') ?>" class="btn btn-success">
                                <i class="bi bi-plus-circle"></i> Add Movie/TV
                            </a>
                            <?php if (session()->get('authenticated')): ?>
                                <a href="<?= base_url('database-maintenance/manage-lookups') ?>" class="btn btn-outline-secondary">
                                    <i class="bi bi-people"></i> Manage People
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5><i class="bi bi-info-circle"></i> About</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            <?= esc(app_name()) ?> helps you catalog and manage your movie collection. 
                            Features include:
                        </p>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check text-success"></i> Movie catalog management</li>
                            <li><i class="bi bi-check text-success"></i> Search and filtering</li>
                            <li><i class="bi bi-check text-success"></i> Rating and status tracking</li>
                            <li><i class="bi bi-check text-success"></i> Loan management</li>
                            <li><i class="bi bi-check text-success"></i> Wishlist support if the wishlist tag exists</li>
                            <li><i class="bi bi-check text-success"></i> Dark/Light theme support</li>
                            <li><i class="bi bi-check text-success"></i> Public view for sharing lists</li>
                            <li><i class="bi bi-check text-success"></i> Sadly vibe coded as an experiment</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($totalMovies === 0): ?>
        <!-- Empty State -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-2 border-dashed">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-film display-1 text-muted"></i>
                        <h3 class="mt-3">No Movies Yet</h3>
                        <p class="text-muted">Your movie collection is empty. Start by adding your first movie!</p>
                        <a href="<?= base_url('movies/add') ?>" class="btn btn-success">
                            <i class="bi bi-plus-circle"></i> Add Your First Movie
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endsection() ?>

<?= $this->section('scripts') ?>
<style>
    .jumbotron {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
    }
    
    [data-bs-theme="dark"] .jumbotron {
        background: linear-gradient(135deg, #0d6efd 0%, #084298 100%) !important;
    }
    
    .card {
        transition: transform 0.2s ease-in-out;
    }
    
    .card:hover {
        transform: translateY(-2px);
    }
</style>
<?= $this->endsection() ?>
