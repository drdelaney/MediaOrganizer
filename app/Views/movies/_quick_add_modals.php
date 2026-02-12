<!-- Add Collection Modal -->
<div class="modal fade" id="addCollectionModal" tabindex="-1" aria-labelledby="addCollectionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCollectionModalLabel">Add New Collection</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="quickAddCollectionForm">
                    <?= csrf_field() ?>
                    <div id="addCollectionAlert"></div>
                    <div class="mb-3">
                        <label for="new_collection_name" class="form-label">Collection Name</label>
                        <input type="text" class="form-control" id="new_collection_name" placeholder="Enter collection name">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveNewCollectionBtn">Save Collection</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Volume Modal -->
<div class="modal fade" id="addVolumeModal" tabindex="-1" aria-labelledby="addVolumeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addVolumeModalLabel">Add New Volume</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="quickAddVolumeForm">
                    <?= csrf_field() ?>
                    <div id="addVolumeAlert"></div>
                    <div class="mb-3">
                        <label for="new_volume_name" class="form-label">Volume Name</label>
                        <input type="text" class="form-control" id="new_volume_name" placeholder="Enter volume name">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveNewVolumeBtn">Save Volume</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Person Modal -->
<div class="modal fade" id="addPersonModal" tabindex="-1" aria-labelledby="addPersonModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPersonModalLabel">Add New Person</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="quickAddPersonForm">
                    <?= csrf_field() ?>
                    <div id="addPersonAlert"></div>
                    <div class="mb-3">
                        <label for="new_person_name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="new_person_name" placeholder="Enter name">
                    </div>
                    <div class="mb-3">
                        <label for="new_person_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="new_person_email" placeholder="Enter email">
                    </div>
                    <div class="mb-3">
                        <label for="new_person_phone" class="form-label">Phone (Optional)</label>
                        <input type="text" class="form-control" id="new_person_phone" placeholder="Enter phone number">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveNewPersonBtn">Save Person</button>
            </div>
        </div>
    </div>
</div>

<!-- Loan Movie Modal (Global) -->
<div class="modal fade" id="loanMovieModal" tabindex="-1" aria-labelledby="loanMovieModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loanMovieModalLabel">
                    <i class="bi bi-person-check"></i> Loan Movie
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="loanMovieForm">
                    <?= csrf_field() ?>
                    <div id="loanModalAlert"></div>
                    <div class="mb-3">
                        <label for="loanPersonSelect" class="form-label">Select Person to Loan To:</label>
                        <div class="input-group">
                            <select class="form-select" id="loanPersonSelect">
                                <option value="">-- Select a person --</option>
                            </select>
                            <button class="btn btn-outline-secondary" type="button" id="addNewPersonBtn" title="Add New Person">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </div>
                    </div>
                    <div class="text-muted small">
                        <i class="bi bi-info-circle"></i> The movie will be marked as loaned out to the selected person.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmLoanBtn">
                    <i class="bi bi-check-circle"></i> Confirm Loan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Shared Loan Modal Logic
    const loanModalEl = document.getElementById('loanMovieModal');
    const loanModal = loanModalEl ? new bootstrap.Modal(loanModalEl) : null;
    let onLoanConfirmed = null;

    window.showLoanModal = function(callback, cancelCallback) {
        if (!loanModal) return;
        onLoanConfirmed = callback;
        
        // Reset modal state
        const selectElement = document.getElementById('loanPersonSelect');
        selectElement.innerHTML = '<option value="">-- Select a person --</option>';
        document.getElementById('loanModalAlert').innerHTML = '';

        // Handle modal close/cancel
        const modalEl = document.getElementById('loanMovieModal');
        const onHidden = function() {
            if (cancelCallback) cancelCallback();
            modalEl.removeEventListener('hidden.bs.modal', onHidden);
            onLoanConfirmed = null; // Clear to prevent double calls
        };
        modalEl.addEventListener('hidden.bs.modal', onHidden);
        
        // Fetch list of people
        fetch('<?= base_url('movies/getPeople') ?>', {
            method: 'GET',
            headers: { 
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok && response.status === 403) {
                throw new Error('CSRF validation failed. Please refresh the page.');
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                data.people.forEach(person => {
                    const option = document.createElement('option');
                    option.value = person.person_id;
                    option.textContent = person.name;
                    selectElement.appendChild(option);
                });
                
                loanModal.show();
            }
        });
    };

    const confirmLoanBtn = document.getElementById('confirmLoanBtn');
    if (confirmLoanBtn) {
        confirmLoanBtn.addEventListener('click', function() {
            const selectElement = document.getElementById('loanPersonSelect');
            const personId = selectElement.value;
            const personName = selectElement.options[selectElement.selectedIndex].text;
            const alertDiv = document.getElementById('loanModalAlert');
            
            if (!personId) {
                alertDiv.innerHTML = '<div class="alert alert-danger">Please select a person</div>';
                return;
            }

            if (onLoanConfirmed) {
                const callback = onLoanConfirmed;
                onLoanConfirmed = null; // Clear so hidden.bs.modal doesn't trigger cancelCallback
                callback(personId, personName);
            }
            loanModal.hide();
        });
    }
    // Collection Quick Add
    const addCollectionBtn = document.getElementById('addNewCollectionBtn');
    if (addCollectionBtn) {
        const addCollectionModal = new bootstrap.Modal(document.getElementById('addCollectionModal'));
        addCollectionBtn.addEventListener('click', () => addCollectionModal.show());

        document.getElementById('saveNewCollectionBtn').addEventListener('click', function() {
            const name = document.getElementById('new_collection_name').value;
            const alertDiv = document.getElementById('addCollectionAlert');
            
            if (!name) {
                alertDiv.innerHTML = '<div class="alert alert-danger">Please enter a name</div>';
                return;
            }

            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';

            const formData = new FormData();
            formData.append('name', name);

            fetch('<?= base_url('movies/addCollection') ?>', {
                method: 'POST',
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok && response.status === 403) {
                    throw new Error('CSRF validation failed. Please refresh the page.');
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    const select = document.getElementById('collection_id');
                    const option = new Option(data.name, data.collection_id, true, true);
                    select.add(option);
                    addCollectionModal.hide();
                    document.getElementById('new_collection_name').value = '';
                    alertDiv.innerHTML = '';
                } else {
                    let errorMsg = data.errors ? Object.values(data.errors).join('<br>') : 'Error adding collection';
                    alertDiv.innerHTML = `<div class="alert alert-danger">${errorMsg}</div>`;
                }
            })
            .catch(() => {
                alertDiv.innerHTML = '<div class="alert alert-danger">Server error</div>';
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = 'Save Collection';
            });
        });
    }

    // Volume Quick Add
    const addVolumeBtn = document.getElementById('addNewVolumeBtn');
    if (addVolumeBtn) {
        const addVolumeModal = new bootstrap.Modal(document.getElementById('addVolumeModal'));
        addVolumeBtn.addEventListener('click', () => addVolumeModal.show());

        document.getElementById('saveNewVolumeBtn').addEventListener('click', function() {
            const name = document.getElementById('new_volume_name').value;
            const alertDiv = document.getElementById('addVolumeAlert');
            
            if (!name) {
                alertDiv.innerHTML = '<div class="alert alert-danger">Please enter a name</div>';
                return;
            }

            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';

            const formData = new FormData();
            formData.append('name', name);

            fetch('<?= base_url('movies/addVolume') ?>', {
                method: 'POST',
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok && response.status === 403) {
                    throw new Error('CSRF validation failed. Please refresh the page.');
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    const select = document.getElementById('volume_id');
                    const option = new Option(data.name, data.volume_id, true, true);
                    select.add(option);
                    addVolumeModal.hide();
                    document.getElementById('new_volume_name').value = '';
                    alertDiv.innerHTML = '';
                } else {
                    let errorMsg = data.errors ? Object.values(data.errors).join('<br>') : 'Error adding volume';
                    alertDiv.innerHTML = `<div class="alert alert-danger">${errorMsg}</div>`;
                }
            })
            .catch(() => {
                alertDiv.innerHTML = '<div class="alert alert-danger">Server error</div>';
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = 'Save Volume';
            });
        });
    }

    // Person Quick Add
    const addPersonBtn = document.getElementById('addNewPersonBtn');
    if (addPersonBtn) {
        const addPersonModal = new bootstrap.Modal(document.getElementById('addPersonModal'));
        addPersonBtn.addEventListener('click', () => addPersonModal.show());

        document.getElementById('saveNewPersonBtn').addEventListener('click', function() {
            const name = document.getElementById('new_person_name').value;
            const email = document.getElementById('new_person_email').value;
            const phone = document.getElementById('new_person_phone').value;
            const alertDiv = document.getElementById('addPersonAlert');
            
            if (!name || !email) {
                alertDiv.innerHTML = '<div class="alert alert-danger">Name and Email are required</div>';
                return;
            }

            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';

            const formData = new FormData();
            formData.append('name', name);
            formData.append('email', email);
            formData.append('phone', phone);

            fetch('<?= base_url('movies/addPerson') ?>', {
                method: 'POST',
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok && response.status === 403) {
                    throw new Error('CSRF validation failed. Please refresh the page.');
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    // Check if we are on index page with loanPersonSelect or somewhere else
                    const select = document.getElementById('loanPersonSelect');
                    if (select) {
                        const option = new Option(data.name, data.person_id, true, true);
                        select.add(option);
                    }
                    addPersonModal.hide();
                    document.getElementById('new_person_name').value = '';
                    document.getElementById('new_person_email').value = '';
                    document.getElementById('new_person_phone').value = '';
                    alertDiv.innerHTML = '';
                } else {
                    let errorMsg = data.errors ? Object.values(data.errors).join('<br>') : 'Error adding person';
                    alertDiv.innerHTML = `<div class="alert alert-danger">${errorMsg}</div>`;
                }
            })
            .catch(() => {
                alertDiv.innerHTML = '<div class="alert alert-danger">Server error</div>';
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = 'Save Person';
            });
        });
    }
});
</script>
