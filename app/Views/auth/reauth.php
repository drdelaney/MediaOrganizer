<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <i class="bi bi-shield-lock text-warning" style="font-size: 3rem;"></i>
                        <h4 class="mt-3">Re-authentication Required</h4>
                        <p class="text-muted">
                            This action requires recent authentication.
                            Please confirm your password to continue.
                        </p>
                    </div>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger">
                            <?= session()->getFlashdata('error') ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="<?= base_url('reauth') ?>">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control"
                                   id="password" name="password" required autofocus>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Confirm
                            </button>
                            <a href="<?= base_url() ?>" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?= $this->endsection() ?>