<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <!-- Notification Area -->
    <div class="row mb-3">
        <div class="col-12">
            <div id="notificationArea"></div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="bi bi-people"></i> Manage Loaned Users</h2>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addPersonModal">
                    <i class="bi bi-person-plus"></i> Add Person
                </button>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-list-ul"></i> People Who Can Borrow Items</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($people)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> No people added yet. Click "Add Person" to get started.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($people as $person): ?>
                                        <tr>
                                            <td><?= esc($person['name']) ?></td>
                                            <td><?= $person['email'] ? esc($person['email']) : '<span class="text-muted">-</span>' ?></td>
                                            <td><?= $person['phone'] ? esc($person['phone']) : '<span class="text-muted">-</span>' ?></td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-primary edit-person" 
                                                        data-id="<?= $person['person_id'] ?>"
                                                        data-name="<?= esc($person['name']) ?>"
                                                        data-email="<?= esc($person['email']) ?>"
                                                        data-phone="<?= esc($person['phone']) ?>">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>
                                                <button class="btn btn-sm btn-danger delete-person" 
                                                        data-id="<?= $person['person_id'] ?>"
                                                        data-name="<?= esc($person['name']) ?>">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
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

<!-- Add Person Modal -->
<div class="modal fade" id="addPersonModal" tabindex="-1" aria-labelledby="addPersonModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPersonModalLabel"><i class="bi bi-person-plus"></i> Add New Person</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addPersonForm">
                    <div class="mb-3">
                        <label for="person_name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="person_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="person_email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="person_email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="person_phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="person_phone" name="phone">
                        <small class="form-text text-muted">Optional</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="savePersonBtn">
                    <i class="bi bi-save"></i> Save Person
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Person Modal -->
<div class="modal fade" id="editPersonModal" tabindex="-1" aria-labelledby="editPersonModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPersonModalLabel"><i class="bi bi-pencil"></i> Edit Person</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editPersonForm">
                    <input type="hidden" id="edit_person_id">
                    <div class="mb-3">
                        <label for="edit_person_name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_person_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_person_email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="edit_person_email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_person_phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="edit_person_phone" name="phone">
                        <small class="form-text text-muted">Optional</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="updatePersonBtn">
                    <i class="bi bi-save"></i> Update Person
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endsection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show notification function
    function showNotification(message, type = 'success') {
        const notificationArea = document.getElementById('notificationArea');
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'check-circle' : 'exclamation-circle';
        
        notificationArea.innerHTML = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="bi bi-${icon}"></i> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        notificationArea.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            const alert = notificationArea.querySelector('.alert');
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    }

    // Add person
    document.getElementById('savePersonBtn').addEventListener('click', function() {
        const name = document.getElementById('person_name').value.trim();
        const email = document.getElementById('person_email').value.trim();
        const phone = document.getElementById('person_phone').value.trim();

        if (!name) {
            showNotification('Please enter a name', 'error');
            return;
        }

        if (!email) {
            showNotification('Please enter an email address', 'error');
            return;
        }

        fetch('<?= base_url('people/add') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'name=' + encodeURIComponent(name) + 
                  '&email=' + encodeURIComponent(email) + 
                  '&phone=' + encodeURIComponent(phone)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showNotification(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            showNotification('Error: ' + error.message, 'error');
        });
    });

    // Edit person - populate modal
    document.querySelectorAll('.edit-person').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const email = this.dataset.email;
            const phone = this.dataset.phone;

            document.getElementById('edit_person_id').value = id;
            document.getElementById('edit_person_name').value = name;
            document.getElementById('edit_person_email').value = email || '';
            document.getElementById('edit_person_phone').value = phone || '';

            const modal = new bootstrap.Modal(document.getElementById('editPersonModal'));
            modal.show();
        });
    });

    // Update person
    document.getElementById('updatePersonBtn').addEventListener('click', function() {
        const id = document.getElementById('edit_person_id').value;
        const name = document.getElementById('edit_person_name').value.trim();
        const email = document.getElementById('edit_person_email').value.trim();
        const phone = document.getElementById('edit_person_phone').value.trim();

        if (!name) {
            showNotification('Please enter a name', 'error');
            return;
        }

        if (!email) {
            showNotification('Please enter an email address', 'error');
            return;
        }

        fetch('<?= base_url('people/update') ?>/' + id, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'name=' + encodeURIComponent(name) + 
                  '&email=' + encodeURIComponent(email) + 
                  '&phone=' + encodeURIComponent(phone)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showNotification(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            showNotification('Error: ' + error.message, 'error');
        });
    });

    // Delete person
    document.querySelectorAll('.delete-person').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;

            if (!confirm('Are you sure you want to delete "' + name + '"?')) {
                return;
            }

            fetch('<?= base_url('people/delete') ?>/' + id, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showNotification(data.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification('Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('Error: ' + error.message, 'error');
            });
        });
    });
});
</script>
<?= $this->endsection() ?>
