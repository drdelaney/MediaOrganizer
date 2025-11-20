<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">
                <i class="bi bi-list-ul"></i> Manage Lookup Tables
            </h1>

            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                Manage reference data used throughout the application. Items in use cannot be deleted.
            </div>

            <!-- Alert for messages -->
            <div id="alert-container"></div>

            <!-- Tabs -->
            <ul class="nav nav-tabs mb-4" id="lookupTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="mediums-tab" data-bs-toggle="tab" data-bs-target="#mediums" type="button">
                        <i class="bi bi-disc"></i> Mediums
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="collections-tab" data-bs-toggle="tab" data-bs-target="#collections" type="button">
                        <i class="bi bi-collection"></i> Collections
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="volumes-tab" data-bs-toggle="tab" data-bs-target="#volumes" type="button">
                        <i class="bi bi-box"></i> Volumes
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="codecs-tab" data-bs-toggle="tab" data-bs-target="#codecs" type="button">
                        <i class="bi bi-file-earmark-code"></i> Codecs
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="lookupTabsContent">
                <!-- MEDIUMS TAB -->
                <div class="tab-pane fade show active" id="mediums" role="tabpanel">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-disc"></i> Mediums</h5>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addMediumModal">
                                <i class="bi bi-plus-circle"></i> Add Medium
                            </button>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th width="150">Actions</th>
                                </tr>
                                </thead>
                                <tbody id="mediums-table">
                                <?php foreach ($mediums as $medium): ?>
                                    <tr data-id="<?= $medium['medium_id'] ?>">
                                        <td><?= $medium['medium_id'] ?></td>
                                        <td><?= esc($medium['name']) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info edit-medium" data-id="<?= $medium['medium_id'] ?>" data-name="<?= esc($medium['name']) ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-medium" data-id="<?= $medium['medium_id'] ?>" data-name="<?= esc($medium['name']) ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- COLLECTIONS TAB -->
                <div class="tab-pane fade" id="collections" role="tabpanel">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-collection"></i> Collections</h5>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addCollectionModal">
                                <i class="bi bi-plus-circle"></i> Add Collection
                            </button>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Loaned</th>
                                    <th width="150">Actions</th>
                                </tr>
                                </thead>
                                <tbody id="collections-table">
                                <?php foreach ($collections as $collection): ?>
                                    <tr data-id="<?= $collection['collection_id'] ?>">
                                        <td><?= $collection['collection_id'] ?></td>
                                        <td><?= esc($collection['name']) ?></td>
                                        <td><?= $collection['loaned'] ? '<span class="badge bg-warning">Yes</span>' : '<span class="badge bg-success">No</span>' ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info edit-collection"
                                                    data-id="<?= $collection['collection_id'] ?>"
                                                    data-name="<?= esc($collection['name']) ?>"
                                                    data-loaned="<?= $collection['loaned'] ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-collection"
                                                    data-id="<?= $collection['collection_id'] ?>"
                                                    data-name="<?= esc($collection['name']) ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- VOLUMES TAB -->
                <div class="tab-pane fade" id="volumes" role="tabpanel">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-box"></i> Volumes</h5>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addVolumeModal">
                                <i class="bi bi-plus-circle"></i> Add Volume
                            </button>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Loaned</th>
                                    <th width="150">Actions</th>
                                </tr>
                                </thead>
                                <tbody id="volumes-table">
                                <?php foreach ($volumes as $volume): ?>
                                    <tr data-id="<?= $volume['volume_id'] ?>">
                                        <td><?= $volume['volume_id'] ?></td>
                                        <td><?= esc($volume['name']) ?></td>
                                        <td><?= $volume['loaned'] ? '<span class="badge bg-warning">Yes</span>' : '<span class="badge bg-success">No</span>' ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info edit-volume"
                                                    data-id="<?= $volume['volume_id'] ?>"
                                                    data-name="<?= esc($volume['name']) ?>"
                                                    data-loaned="<?= $volume['loaned'] ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-volume"
                                                    data-id="<?= $volume['volume_id'] ?>"
                                                    data-name="<?= esc($volume['name']) ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- CODECS TAB -->
                <div class="tab-pane fade" id="codecs" role="tabpanel">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-file-earmark-code"></i> Video Codecs</h5>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addCodecModal">
                                <i class="bi bi-plus-circle"></i> Add Codec
                            </button>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th width="150">Actions</th>
                                </tr>
                                </thead>
                                <tbody id="codecs-table">
                                <?php foreach ($codecs as $codec): ?>
                                    <tr data-id="<?= $codec['vcodec_id'] ?>">
                                        <td><?= $codec['vcodec_id'] ?></td>
                                        <td><?= esc($codec['name']) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info edit-codec"
                                                    data-id="<?= $codec['vcodec_id'] ?>"
                                                    data-name="<?= esc($codec['name']) ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-codec"
                                                    data-id="<?= $codec['vcodec_id'] ?>"
                                                    data-name="<?= esc($codec['name']) ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODALS -->
    <!-- Add Medium Modal -->
    <div class="modal fade" id="addMediumModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Medium</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addMediumForm">
                        <div class="mb-3">
                            <label for="medium_name" class="form-label">Medium Name</label>
                            <input type="text" class="form-control" id="medium_name" name="name" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveMedium">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Medium Modal -->
    <div class="modal fade" id="editMediumModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Medium</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editMediumForm">
                        <input type="hidden" id="edit_medium_id">
                        <div class="mb-3">
                            <label for="edit_medium_name" class="form-label">Medium Name</label>
                            <input type="text" class="form-control" id="edit_medium_name" name="name" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="updateMedium">Update</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Collection Modal -->
    <div class="modal fade" id="addCollectionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Collection</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addCollectionForm">
                        <div class="mb-3">
                            <label for="collection_name" class="form-label">Collection Name</label>
                            <input type="text" class="form-control" id="collection_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="collection_loaned" class="form-label">Loaned</label>
                            <select class="form-select" id="collection_loaned" name="loaned">
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveCollection">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Collection Modal -->
    <div class="modal fade" id="editCollectionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Collection</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editCollectionForm">
                        <input type="hidden" id="edit_collection_id">
                        <div class="mb-3">
                            <label for="edit_collection_name" class="form-label">Collection Name</label>
                            <input type="text" class="form-control" id="edit_collection_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_collection_loaned" class="form-label">Loaned</label>
                            <select class="form-select" id="edit_collection_loaned" name="loaned">
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="updateCollection">Update</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Volume Modal -->
    <div class="modal fade" id="addVolumeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Volume</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addVolumeForm">
                        <div class="mb-3">
                            <label for="volume_name" class="form-label">Volume Name</label>
                            <input type="text" class="form-control" id="volume_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="volume_loaned" class="form-label">Loaned</label>
                            <select class="form-select" id="volume_loaned" name="loaned">
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveVolume">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Volume Modal -->
    <div class="modal fade" id="editVolumeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Volume</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editVolumeForm">
                        <input type="hidden" id="edit_volume_id">
                        <div class="mb-3">
                            <label for="edit_volume_name" class="form-label">Volume Name</label>
                            <input type="text" class="form-control" id="edit_volume_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_volume_loaned" class="form-label">Loaned</label>
                            <select class="form-select" id="edit_volume_loaned" name="loaned">
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="updateVolume">Update</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Codec Modal -->
    <div class="modal fade" id="addCodecModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Codec</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addCodecForm">
                        <div class="mb-3">
                            <label for="codec_name" class="form-label">Codec Name</label>
                            <input type="text" class="form-control" id="codec_name" name="name" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveCodec">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Codec Modal -->
    <div class="modal fade" id="editCodecModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Codec</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editCodecForm">
                        <input type="hidden" id="edit_codec_id">
                        <div class="mb-3">
                            <label for="edit_codec_name" class="form-label">Codec Name</label>
                            <input type="text" class="form-control" id="edit_codec_name" name="name" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="updateCodec">Update</button>
                </div>
            </div>
        </div>
    </div>

<?= $this->endsection() ?>

<?= $this->section('scripts') ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Helper function to get active tab ID
            function getActiveTabId() {
                const activeTab = document.querySelector('#lookupTabs .nav-link.active');
                return activeTab ? activeTab.getAttribute('data-bs-target') : '#mediums';
            }
            
            // Helper function to save current tab to URL hash
            function saveActiveTab() {
                const activeTabId = getActiveTabId();
                window.location.hash = activeTabId;
            }
            
            // Restore active tab from URL hash on page load
            function restoreActiveTab() {
                const hash = window.location.hash;
                if (hash && hash !== '#mediums') {
                    const tabButton = document.querySelector(`[data-bs-target="${hash}"]`);
                    if (tabButton) {
                        const tab = new bootstrap.Tab(tabButton);
                        tab.show();
                    }
                }
            }
            
            // Restore the active tab when page loads
            restoreActiveTab();
            
            // Save active tab when tabs are switched
            document.querySelectorAll('#lookupTabs button[data-bs-toggle="tab"]').forEach(button => {
                button.addEventListener('shown.bs.tab', function() {
                    saveActiveTab();
                });
            });
            
            // Helper function to show alerts
            function showAlert(message, type) {
                const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
                document.getElementById('alert-container').innerHTML = alertHtml;
                
                // Scroll to top of page to show the alert, especially for errors
                if (type === 'danger') {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
                
                setTimeout(() => {
                    const alert = document.querySelector('.alert');
                    if (alert) alert.remove();
                }, 5000);
            }

            // MEDIUM OPERATIONS
            document.getElementById('saveMedium').addEventListener('click', function() {
                const name = document.getElementById('medium_name').value;

                fetch('<?= base_url('database-maintenance/medium/add') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'name=' + encodeURIComponent(name)
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            showAlert(data.message, 'success');
                            bootstrap.Modal.getInstance(document.getElementById('addMediumModal')).hide();
                            location.reload(); // Reload to show new data
                        } else {
                            showAlert(data.message, 'danger');
                        }
                    });
            });

            // Edit medium buttons
            document.querySelectorAll('.edit-medium').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const name = this.dataset.name;

                    document.getElementById('edit_medium_id').value = id;
                    document.getElementById('edit_medium_name').value = name;

                    new bootstrap.Modal(document.getElementById('editMediumModal')).show();
                });
            });

            document.getElementById('updateMedium').addEventListener('click', function() {
                const id = document.getElementById('edit_medium_id').value;
                const name = document.getElementById('edit_medium_name').value;

                fetch(`<?= base_url('database-maintenance/medium/update') ?>/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'name=' + encodeURIComponent(name)
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            showAlert(data.message, 'success');
                            bootstrap.Modal.getInstance(document.getElementById('editMediumModal')).hide();
                            location.reload();
                        } else {
                            showAlert(data.message, 'danger');
                        }
                    });
            });

            // Delete medium buttons
            document.querySelectorAll('.delete-medium').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const name = this.dataset.name;

                    if (confirm(`Are you sure you want to delete "${name}"?`)) {
                        fetch(`<?= base_url('database-maintenance/medium/delete') ?>/${id}`, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    showAlert(data.message, 'success');
                                    location.reload();
                                } else {
                                    showAlert(data.message, 'danger');
                                }
                            });
                    }
                });
            });

            // COLLECTION OPERATIONS
            document.getElementById('saveCollection').addEventListener('click', function() {
                const name = document.getElementById('collection_name').value;
                const loaned = document.getElementById('collection_loaned').value;

                fetch('<?= base_url('database-maintenance/collection/add') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'name=' + encodeURIComponent(name) + '&loaned=' + loaned
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            showAlert(data.message, 'success');
                            bootstrap.Modal.getInstance(document.getElementById('addCollectionModal')).hide();
                            location.reload();
                        } else {
                            showAlert(data.message, 'danger');
                        }
                    });
            });

            // Edit collection buttons
            document.querySelectorAll('.edit-collection').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const name = this.dataset.name;
                    const loaned = this.dataset.loaned;

                    document.getElementById('edit_collection_id').value = id;
                    document.getElementById('edit_collection_name').value = name;
                    document.getElementById('edit_collection_loaned').value = loaned;

                    new bootstrap.Modal(document.getElementById('editCollectionModal')).show();
                });
            });

            document.getElementById('updateCollection').addEventListener('click', function() {
                const id = document.getElementById('edit_collection_id').value;
                const name = document.getElementById('edit_collection_name').value;
                const loaned = document.getElementById('edit_collection_loaned').value;

                fetch(`<?= base_url('database-maintenance/collection/update') ?>/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'name=' + encodeURIComponent(name) + '&loaned=' + loaned
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            showAlert(data.message, 'success');
                            bootstrap.Modal.getInstance(document.getElementById('editCollectionModal')).hide();
                            location.reload();
                        } else {
                            showAlert(data.message, 'danger');
                        }
                    });
            });

            // Delete collection buttons
            document.querySelectorAll('.delete-collection').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const name = this.dataset.name;

                    if (confirm(`Are you sure you want to delete "${name}"?`)) {
                        fetch(`<?= base_url('database-maintenance/collection/delete') ?>/${id}`, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    showAlert(data.message, 'success');
                                    location.reload();
                                } else {
                                    showAlert(data.message, 'danger');
                                }
                            });
                    }
                });
            });

            // VOLUME OPERATIONS
            document.getElementById('saveVolume').addEventListener('click', function() {
                const name = document.getElementById('volume_name').value;
                const loaned = document.getElementById('volume_loaned').value;

                fetch('<?= base_url('database-maintenance/volume/add') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'name=' + encodeURIComponent(name) + '&loaned=' + loaned
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            showAlert(data.message, 'success');
                            bootstrap.Modal.getInstance(document.getElementById('addVolumeModal')).hide();
                            location.reload();
                        } else {
                            showAlert(data.message, 'danger');
                        }
                    });
            });

            // Edit volume buttons
            document.querySelectorAll('.edit-volume').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const name = this.dataset.name;
                    const loaned = this.dataset.loaned;

                    document.getElementById('edit_volume_id').value = id;
                    document.getElementById('edit_volume_name').value = name;
                    document.getElementById('edit_volume_loaned').value = loaned;

                    new bootstrap.Modal(document.getElementById('editVolumeModal')).show();
                });
            });

            document.getElementById('updateVolume').addEventListener('click', function() {
                const id = document.getElementById('edit_volume_id').value;
                const name = document.getElementById('edit_volume_name').value;
                const loaned = document.getElementById('edit_volume_loaned').value;

                fetch(`<?= base_url('database-maintenance/volume/update') ?>/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'name=' + encodeURIComponent(name) + '&loaned=' + loaned
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            showAlert(data.message, 'success');
                            bootstrap.Modal.getInstance(document.getElementById('editVolumeModal')).hide();
                            location.reload();
                        } else {
                            showAlert(data.message, 'danger');
                        }
                    });
            });

            // Delete volume buttons
            document.querySelectorAll('.delete-volume').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const name = this.dataset.name;

                    if (confirm(`Are you sure you want to delete "${name}"?`)) {
                        fetch(`<?= base_url('database-maintenance/volume/delete') ?>/${id}`, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    showAlert(data.message, 'success');
                                    location.reload();
                                } else {
                                    showAlert(data.message, 'danger');
                                }
                            });
                    }
                });
            });

            // CODEC OPERATIONS
            document.getElementById('saveCodec').addEventListener('click', function() {
                const name = document.getElementById('codec_name').value;

                fetch('<?= base_url('database-maintenance/codec/add') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'name=' + encodeURIComponent(name)
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            showAlert(data.message, 'success');
                            bootstrap.Modal.getInstance(document.getElementById('addCodecModal')).hide();
                            location.reload();
                        } else {
                            showAlert(data.message, 'danger');
                        }
                    });
            });

            // Edit codec buttons
            document.querySelectorAll('.edit-codec').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const name = this.dataset.name;

                    document.getElementById('edit_codec_id').value = id;
                    document.getElementById('edit_codec_name').value = name;

                    new bootstrap.Modal(document.getElementById('editCodecModal')).show();
                });
            });

            document.getElementById('updateCodec').addEventListener('click', function() {
                const id = document.getElementById('edit_codec_id').value;
                const name = document.getElementById('edit_codec_name').value;

                fetch(`<?= base_url('database-maintenance/codec/update') ?>/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'name=' + encodeURIComponent(name)
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            showAlert(data.message, 'success');
                            bootstrap.Modal.getInstance(document.getElementById('editCodecModal')).hide();
                            location.reload();
                        } else {
                            showAlert(data.message, 'danger');
                        }
                    });
            });

            // Delete codec buttons
            document.querySelectorAll('.delete-codec').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const name = this.dataset.name;

                    if (confirm(`Are you sure you want to delete "${name}"?`)) {
                        fetch(`<?= base_url('database-maintenance/codec/delete') ?>/${id}`, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    showAlert(data.message, 'success');
                                    location.reload();
                                } else {
                                    showAlert(data.message, 'danger');
                                }
                            });
                    }
                });
            });
        });
    </script>
<?= $this->endsection() ?>