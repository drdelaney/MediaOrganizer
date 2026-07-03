<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-success">
                <div class="card-header bg-success text-white">
                    <h3 class="card-title mb-0">Setup Complete!</h3>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                    </div>
                    <p class="lead">Database migrations and initial data have been successfully applied.</p>
                    
                    <?php if (isset($updated) && $updated): ?>
                        <div class="alert alert-success">
                            Your <code>.env</code> file has been automatically updated with <code>app.setupComplete = true</code>.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            Could not automatically update your <code>.env</code> file. Please manually add or update:
                            <br><code>app.setupComplete = true</code>
                        </div>
                    <?php endif; ?>

                    <div class="d-grid gap-2">
                        <a href="<?= base_url() ?>" class="btn btn-primary btn-lg">Go to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
