<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?php
$mediums = isset($mediums) && is_array($mediums) ? $mediums : [];
$collections = isset($collections) && is_array($collections) ? $collections : [];
$volumes = isset($volumes) && is_array($volumes) ? $volumes : [];
$codecs = isset($codecs) && is_array($codecs) ? $codecs : [];
$tags = isset($tags) && is_array($tags) ? $tags : [];
$people = isset($people) && is_array($people) ? $people : [];
$achannels = isset($achannels) && is_array($achannels) ? $achannels : [];
$acodecs = isset($acodecs) && is_array($acodecs) ? $acodecs : [];
$languages = isset($languages) && is_array($languages) ? $languages : [];
$ratios = isset($ratios) && is_array($ratios) ? $ratios : [];
$subformats = isset($subformats) && is_array($subformats) ? $subformats : [];
$poster_count = isset($poster_count) ? $poster_count : 0;
?>

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
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tags-tab" data-bs-toggle="tab" data-bs-target="#tags" type="button">
                        <i class="bi bi-tags"></i> Tags
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="people-tab" data-bs-toggle="tab" data-bs-target="#people" type="button">
                        <i class="bi bi-people"></i> People
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="achannels-tab" data-bs-toggle="tab" data-bs-target="#achannels" type="button">
                        <i class="bi bi-volume-up"></i> Audio Channels
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="acodecs-tab" data-bs-toggle="tab" data-bs-target="#acodecs" type="button">
                        <i class="bi bi-file-earmark-music"></i> Audio Codecs
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="languages-tab" data-bs-toggle="tab" data-bs-target="#languages" type="button">
                        <i class="bi bi-translate"></i> Languages
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="ratios-tab" data-bs-toggle="tab" data-bs-target="#ratios" type="button">
                        <i class="bi bi-aspect-ratio"></i> Ratios
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="subformats-tab" data-bs-toggle="tab" data-bs-target="#subformats" type="button">
                        <i class="bi bi-chat-dots"></i> Subtitle Formats
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="posters-tab" data-bs-toggle="tab" data-bs-target="#posters" type="button">
                        <i class="bi bi-image"></i> Posters
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
                                    <th style="width: 150px;">Actions</th>
                                </tr>
                                </thead>
                                <tbody id="mediums-table">
                                <?php foreach ($mediums as $medium): ?>
                                    <tr data-id="<?= $medium['medium_id'] ?>">
                                        <td><?= $medium['medium_id'] ?></td>
                                        <td><?= esc($medium['name']) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info edit-medium" data-id="<?= $medium['medium_id'] ?>" data-name="<?= esc($medium['name'], 'attr') ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-medium" data-id="<?= $medium['medium_id'] ?>" data-name="<?= esc($medium['name'], 'attr') ?>">
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
                                    <th style="width: 150px;">Actions</th>
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
                                                    data-name="<?= esc($collection['name'], 'attr') ?>"
                                                    data-loaned="<?= $collection['loaned'] ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-collection"
                                                    data-id="<?= $collection['collection_id'] ?>"
                                                    data-name="<?= esc($collection['name'], 'attr') ?>">
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
                                    <th style="width: 150px;">Actions</th>
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
                                                    data-name="<?= esc($volume['name'], 'attr') ?>"
                                                    data-loaned="<?= $volume['loaned'] ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-volume"
                                                    data-id="<?= $volume['volume_id'] ?>"
                                                    data-name="<?= esc($volume['name'], 'attr') ?>">
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
                                    <th style="width: 150px;">Actions</th>
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
                                                    data-name="<?= esc($codec['name'], 'attr') ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-codec"
                                                    data-id="<?= $codec['vcodec_id'] ?>"
                                                    data-name="<?= esc($codec['name'], 'attr') ?>">
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

                <!-- TAGS TAB -->
                <div class="tab-pane fade" id="tags" role="tabpanel">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-tags"></i> Tags</h5>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addTagModal">
                                <i class="bi bi-plus-circle"></i> Add Tag
                            </button>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Movies</th>
                                    <th style="width: 150px;">Actions</th>
                                </tr>
                                </thead>
                                <tbody id="tags-table">
                                <?php foreach ($tags as $tag): ?>
                                    <tr data-id="<?= $tag['tag_id'] ?>">
                                        <td><?= $tag['tag_id'] ?></td>
                                        <td>
                                            <a href="<?= base_url('movies?tag=' . $tag['tag_id']) ?>" class="text-decoration-none">
                                                <?= esc($tag['name']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?= $tag['movie_count'] ?></span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info edit-tag" 
                                                    data-id="<?= $tag['tag_id'] ?>" 
                                                    data-name="<?= esc($tag['name'], 'attr') ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-tag" 
                                                    data-id="<?= $tag['tag_id'] ?>" 
                                                    data-name="<?= esc($tag['name'], 'attr') ?>"
                                                    data-count="<?= $tag['movie_count'] ?>">
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

                <!-- PEOPLE TAB -->
                <div class="tab-pane fade" id="people" role="tabpanel">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-people"></i> People</h5>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addPersonModal">
                                <i class="bi bi-person-plus"></i> Add Person
                            </button>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th style="width: 150px;">Actions</th>
                                </tr>
                                </thead>
                                <tbody id="people-table">
                                <?php foreach ($people as $person): ?>
                                    <tr data-id="<?= $person['person_id'] ?>">
                                        <td><?= $person['person_id'] ?></td>
                                        <td><?= esc($person['name']) ?></td>
                                        <td><?= $person['email'] ? esc($person['email']) : '<span class="text-muted">-</span>' ?></td>
                                        <td><?= $person['phone'] ? esc($person['phone']) : '<span class="text-muted">-</span>' ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info edit-person"
                                                    data-id="<?= $person['person_id'] ?>"
                                                    data-name="<?= esc($person['name'], 'attr') ?>"
                                                    data-email="<?= esc($person['email'], 'attr') ?>"
                                                    data-phone="<?= esc($person['phone'], 'attr') ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-person"
                                                    data-id="<?= $person['person_id'] ?>"
                                                    data-name="<?= esc($person['name'], 'attr') ?>">
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

                <!-- ACHANNELS TAB -->
                <div class="tab-pane fade" id="achannels" role="tabpanel">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-volume-up"></i> Audio Channels</h5>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addAChannelModal">
                                <i class="bi bi-plus-circle"></i> Add Audio Channel
                            </button>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th style="width: 150px;">Actions</th>
                                </tr>
                                </thead>
                                <tbody id="achannels-table">
                                <?php foreach ($achannels as $achannel): ?>
                                    <tr data-id="<?= $achannel['achannel_id'] ?>">
                                        <td><?= $achannel['achannel_id'] ?></td>
                                        <td><?= esc($achannel['name']) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info edit-achannel" data-id="<?= $achannel['achannel_id'] ?>" data-name="<?= esc($achannel['name'], 'attr') ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-achannel" data-id="<?= $achannel['achannel_id'] ?>" data-name="<?= esc($achannel['name'], 'attr') ?>">
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

                <!-- ACODECS TAB -->
                <div class="tab-pane fade" id="acodecs" role="tabpanel">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-file-earmark-music"></i> Audio Codecs</h5>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addACodecModal">
                                <i class="bi bi-plus-circle"></i> Add Audio Codec
                            </button>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th style="width: 150px;">Actions</th>
                                </tr>
                                </thead>
                                <tbody id="acodecs-table">
                                <?php foreach ($acodecs as $acodec): ?>
                                    <tr data-id="<?= $acodec['acodec_id'] ?>">
                                        <td><?= $acodec['acodec_id'] ?></td>
                                        <td><?= esc($acodec['name']) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info edit-acodec" data-id="<?= $acodec['acodec_id'] ?>" data-name="<?= esc($acodec['name'], 'attr') ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-acodec" data-id="<?= $acodec['acodec_id'] ?>" data-name="<?= esc($acodec['name'], 'attr') ?>">
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

                <!-- LANGUAGES TAB -->
                <div class="tab-pane fade" id="languages" role="tabpanel">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-translate"></i> Languages</h5>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addLanguageModal">
                                <i class="bi bi-plus-circle"></i> Add Language
                            </button>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th style="width: 150px;">Actions</th>
                                </tr>
                                </thead>
                                <tbody id="languages-table">
                                <?php foreach ($languages as $language): ?>
                                    <tr data-id="<?= $language['lang_id'] ?>">
                                        <td><?= $language['lang_id'] ?></td>
                                        <td><?= esc($language['name']) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info edit-language" data-id="<?= $language['lang_id'] ?>" data-name="<?= esc($language['name'], 'attr') ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-language" data-id="<?= $language['lang_id'] ?>" data-name="<?= esc($language['name'], 'attr') ?>">
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

                <!-- RATIOS TAB -->
                <div class="tab-pane fade" id="ratios" role="tabpanel">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-aspect-ratio"></i> Ratios</h5>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addRatioModal">
                                <i class="bi bi-plus-circle"></i> Add Ratio
                            </button>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th style="width: 150px;">Actions</th>
                                </tr>
                                </thead>
                                <tbody id="ratios-table">
                                <?php foreach ($ratios as $ratio): ?>
                                    <tr data-id="<?= $ratio['ratio_id'] ?>">
                                        <td><?= $ratio['ratio_id'] ?></td>
                                        <td><?= esc($ratio['name']) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info edit-ratio" data-id="<?= $ratio['ratio_id'] ?>" data-name="<?= esc($ratio['name'], 'attr') ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-ratio" data-id="<?= $ratio['ratio_id'] ?>" data-name="<?= esc($ratio['name'], 'attr') ?>">
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

                <!-- SUBFORMATS TAB -->
                <div class="tab-pane fade" id="subformats" role="tabpanel">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-chat-dots"></i> Subtitle Formats</h5>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addSubformatModal">
                                <i class="bi bi-plus-circle"></i> Add Subtitle Format
                            </button>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th style="width: 150px;">Actions</th>
                                </tr>
                                </thead>
                                <tbody id="subformats-table">
                                <?php foreach ($subformats as $subformat): ?>
                                    <tr data-id="<?= $subformat['subformat_id'] ?>">
                                        <td><?= $subformat['subformat_id'] ?></td>
                                        <td><?= esc($subformat['name']) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info edit-subformat" data-id="<?= $subformat['subformat_id'] ?>" data-name="<?= esc($subformat['name'], 'attr') ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-subformat" data-id="<?= $subformat['subformat_id'] ?>" data-name="<?= esc($subformat['name'], 'attr') ?>">
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

                <!-- POSTERS TAB -->
                <div class="tab-pane fade" id="posters" role="tabpanel">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-image"></i> Posters</h5>
                            <button class="btn btn-sm btn-warning" id="purge-posters-btn">
                                <i class="bi bi-trash"></i> Purge Unused Posters
                            </button>
                        </div>
                        <div class="card-body">
                            <p>Total posters in database: <strong><?= $poster_count ?></strong></p>
                            <p class="text-muted small">
                                Posters are stored in the database and linked to movies by MD5 hash. 
                                Purging will remove all posters that are not currently associated with any movie.
                            </p>
                            <div id="purge-result" class="mt-3" style="display: none;">
                                <div class="alert alert-success"></div>
                            </div>
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
                        <?= csrf_field() ?>
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
                        <?= csrf_field() ?>
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
                        <?= csrf_field() ?>
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
                        <?= csrf_field() ?>
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
                        <?= csrf_field() ?>
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
                        <?= csrf_field() ?>
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
                        <?= csrf_field() ?>
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
                        <?= csrf_field() ?>
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

    <!-- Add Tag Modal -->
    <div class="modal fade" id="addTagModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Tag</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addTagForm">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="tag_name" class="form-label">Tag Name</label>
                            <input type="text" class="form-control" id="tag_name" name="name" maxlength="64" required>
                            <small class="form-text text-muted">Maximum 64 characters</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveTag">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Tag Modal -->
    <div class="modal fade" id="editTagModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Tag</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editTagForm">
                        <?= csrf_field() ?>
                        <input type="hidden" id="edit_tag_id">
                        <div class="mb-3">
                            <label for="edit_tag_name" class="form-label">Tag Name</label>
                            <input type="text" class="form-control" id="edit_tag_name" name="name" maxlength="64" required>
                            <small class="form-text text-muted">Maximum 64 characters</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="updateTag">Update</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Person Modal -->
    <div class="modal fade" id="addPersonModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Person</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addPersonForm">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="person_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="person_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="person_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="person_email" name="email">
                        </div>
                        <div class="mb-3">
                            <label for="person_phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="person_phone" name="phone">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="savePerson">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Person Modal -->
    <div class="modal fade" id="editPersonModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Person</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editPersonForm">
                        <?= csrf_field() ?>
                        <input type="hidden" id="edit_person_id">
                        <div class="mb-3">
                            <label for="edit_person_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="edit_person_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_person_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_person_email" name="email">
                        </div>
                        <div class="mb-3">
                            <label for="edit_person_phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="edit_person_phone" name="phone">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="updatePerson">Update</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ACHANNEL MODALS -->
    <div class="modal fade" id="addAChannelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Audio Channel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addAChannelForm">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="achannel_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="achannel_name" name="name" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveAChannel">Save</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editAChannelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Audio Channel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editAChannelForm">
                        <?= csrf_field() ?>
                        <input type="hidden" id="edit_achannel_id">
                        <div class="mb-3">
                            <label for="edit_achannel_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="edit_achannel_name" name="name" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="updateAChannel">Update</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ACODEC MODALS -->
    <div class="modal fade" id="addACodecModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Audio Codec</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addACodecForm">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="acodec_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="acodec_name" name="name" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveACodec">Save</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editACodecModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Audio Codec</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editACodecForm">
                        <?= csrf_field() ?>
                        <input type="hidden" id="edit_acodec_id">
                        <div class="mb-3">
                            <label for="edit_acodec_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="edit_acodec_name" name="name" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="updateACodec">Update</button>
                </div>
            </div>
        </div>
    </div>

    <!-- LANGUAGE MODALS -->
    <div class="modal fade" id="addLanguageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Language</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addLanguageForm">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="language_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="language_name" name="name" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveLanguage">Save</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editLanguageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Language</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editLanguageForm">
                        <?= csrf_field() ?>
                        <input type="hidden" id="edit_language_id">
                        <div class="mb-3">
                            <label for="edit_language_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="edit_language_name" name="name" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="updateLanguage">Update</button>
                </div>
            </div>
        </div>
    </div>

    <!-- RATIO MODALS -->
    <div class="modal fade" id="addRatioModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Ratio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addRatioForm">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="ratio_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="ratio_name" name="name" maxlength="5" required>
                            <small class="text-muted">Max 5 characters (e.g. 16:9)</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveRatio">Save</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editRatioModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Ratio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editRatioForm">
                        <?= csrf_field() ?>
                        <input type="hidden" id="edit_ratio_id">
                        <div class="mb-3">
                            <label for="edit_ratio_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="edit_ratio_name" name="name" maxlength="5" required>
                            <small class="text-muted">Max 5 characters (e.g. 16:9)</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="updateRatio">Update</button>
                </div>
            </div>
        </div>
    </div>

    <!-- SUBFORMAT MODALS -->
    <div class="modal fade" id="addSubformatModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Subtitle Format</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addSubformatForm">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="subformat_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="subformat_name" name="name" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveSubformat">Save</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editSubformatModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Subtitle Format</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editSubformatForm">
                        <?= csrf_field() ?>
                        <input type="hidden" id="edit_subformat_id">
                        <div class="mb-3">
                            <label for="edit_subformat_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="edit_subformat_name" name="name" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="updateSubformat">Update</button>
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
                window.location.hash = getActiveTabId();
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

            // Handle Enter key in modals: highlight Save/Update button instead of submitting/closing
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    const activeModal = document.querySelector('.modal.show');
                    if (activeModal) {
                        // Check if an input or select has focus
                        const focusedElement = document.activeElement;
                        if (focusedElement && (focusedElement.tagName === 'INPUT' || focusedElement.tagName === 'SELECT')) {
                            // If it's a select, Enter might be used to pick an option (though usually not in standard HTML select)
                            // But for text inputs, we definitely want to prevent submission
                            event.preventDefault(); // Prevent form submission or default Enter behavior
                            
                            // Find the primary button (Save or Update) in the modal footer
                            const primaryBtn = activeModal.querySelector('.modal-footer .btn-primary');
                            if (primaryBtn) {
                                // Clear existing focus first to ensure the highlight is visible if it was already focused
                                primaryBtn.blur();
                                setTimeout(() => {
                                    primaryBtn.focus();
                                    // Add a temporary highlight effect
                                    primaryBtn.classList.add('btn-highlight-pulse');
                                    setTimeout(() => {
                                        primaryBtn.classList.remove('btn-highlight-pulse');
                                    }, 1500);
                                }, 10);
                            }
                        }
                    }
                }
            });
            
            // Helper function to show alerts
            function showAlert(message, type) {
                document.getElementById('alert-container').innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
                
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
                                'X-Requested-With': 'XMLHttpRequest',
                                '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
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
                        'X-Requested-With': 'XMLHttpRequest',
                        '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
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
                                'X-Requested-With': 'XMLHttpRequest',
                                '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
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
                                'X-Requested-With': 'XMLHttpRequest',
                                '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
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

            // TAG OPERATIONS
            document.getElementById('saveTag')?.addEventListener('click', function() {
                const name = document.getElementById('tag_name').value;

                fetch('<?= base_url('database-maintenance/tag/add') ?>', {
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
                            bootstrap.Modal.getInstance(document.getElementById('addTagModal')).hide();
                            location.reload();
                        } else {
                            showAlert(data.message, 'danger');
                        }
                    });
            });

            // Edit tag buttons
            document.querySelectorAll('.edit-tag').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const name = this.dataset.name;

                    document.getElementById('edit_tag_id').value = id;
                    document.getElementById('edit_tag_name').value = name;

                    new bootstrap.Modal(document.getElementById('editTagModal')).show();
                });
            });

            document.getElementById('updateTag')?.addEventListener('click', function() {
                const id = document.getElementById('edit_tag_id').value;
                const name = document.getElementById('edit_tag_name').value;

                fetch(`<?= base_url('database-maintenance/tag/update') ?>/${id}`, {
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
                            bootstrap.Modal.getInstance(document.getElementById('editTagModal')).hide();
                            location.reload();
                        } else {
                            showAlert(data.message, 'danger');
                        }
                    });
            });

            // Delete tag buttons
            document.querySelectorAll('.delete-tag').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const name = this.dataset.name;
                    const count = parseInt(this.dataset.count);

                    let confirmMessage = `Are you sure you want to delete "${name}"?`;
                    if (count > 0) {
                        confirmMessage = `Tag "${name}" is used by ${count} movie(s). Are you sure you want to delete it?`;
                    }

                    if (confirm(confirmMessage)) {
                        fetch(`<?= base_url('database-maintenance/tag/delete') ?>/${id}`, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
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

            // PEOPLE OPERATIONS
            document.getElementById('savePerson').addEventListener('click', function() {
                const name = document.getElementById('person_name').value;
                const email = document.getElementById('person_email').value;
                const phone = document.getElementById('person_phone').value;

                fetch('<?= base_url('people/add') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'name=' + encodeURIComponent(name) + '&email=' + encodeURIComponent(email) + '&phone=' + encodeURIComponent(phone)
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            showAlert(data.message, 'success');
                            bootstrap.Modal.getInstance(document.getElementById('addPersonModal')).hide();
                            location.reload();
                        } else {
                            showAlert(data.message, 'danger');
                        }
                    });
            });

            // Edit person buttons
            document.querySelectorAll('.edit-person').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const name = this.dataset.name;
                    const email = this.dataset.email;
                    const phone = this.dataset.phone;

                    document.getElementById('edit_person_id').value = id;
                    document.getElementById('edit_person_name').value = name;
                    document.getElementById('edit_person_email').value = email;
                    document.getElementById('edit_person_phone').value = phone;

                    new bootstrap.Modal(document.getElementById('editPersonModal')).show();
                });
            });

            document.getElementById('updatePerson').addEventListener('click', function() {
                const id = document.getElementById('edit_person_id').value;
                const name = document.getElementById('edit_person_name').value;
                const email = document.getElementById('edit_person_email').value;
                const phone = document.getElementById('edit_person_phone').value;

                fetch(`<?= base_url('people/update') ?>/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'name=' + encodeURIComponent(name) + '&email=' + encodeURIComponent(email) + '&phone=' + encodeURIComponent(phone)
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            showAlert(data.message, 'success');
                            bootstrap.Modal.getInstance(document.getElementById('editPersonModal')).hide();
                            location.reload();
                        } else {
                            showAlert(data.message, 'danger');
                        }
                    });
            });

            // Delete person buttons
            document.querySelectorAll('.delete-person').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const name = this.dataset.name;

                    if (confirm(`Are you sure you want to delete "${name}"?`)) {
                        fetch(`<?= base_url('people/delete') ?>/${id}`, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
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

            // ACHANNEL OPERATIONS
            document.getElementById('saveAChannel').addEventListener('click', function() {
                const name = document.getElementById('achannel_name').value;
                fetch('<?= base_url('database-maintenance/achannel/add') ?>', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
                    body: 'name=' + encodeURIComponent(name)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showAlert(data.message, 'success');
                        bootstrap.Modal.getInstance(document.getElementById('addAChannelModal')).hide();
                        location.reload();
                    } else { showAlert(data.message, 'danger'); }
                });
            });
            document.querySelectorAll('.edit-achannel').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.getElementById('edit_achannel_id').value = this.dataset.id;
                    document.getElementById('edit_achannel_name').value = this.dataset.name;
                    new bootstrap.Modal(document.getElementById('editAChannelModal')).show();
                });
            });
            document.getElementById('updateAChannel').addEventListener('click', function() {
                const id = document.getElementById('edit_achannel_id').value;
                const name = document.getElementById('edit_achannel_name').value;
                fetch(`<?= base_url('database-maintenance/achannel/update') ?>/${id}`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
                    body: 'name=' + encodeURIComponent(name)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showAlert(data.message, 'success');
                        bootstrap.Modal.getInstance(document.getElementById('editAChannelModal')).hide();
                        location.reload();
                    } else { showAlert(data.message, 'danger'); }
                });
            });
            document.querySelectorAll('.delete-achannel').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const name = this.dataset.name;
                    if (confirm(`Are you sure you want to delete "${name}"?`)) {
                        fetch(`<?= base_url('database-maintenance/achannel/delete') ?>/${id}`, {
                            method: 'POST',
                            headers: {'X-Requested-With': 'XMLHttpRequest'}
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                showAlert(data.message, 'success');
                                location.reload();
                            } else { showAlert(data.message, 'danger'); }
                        });
                    }
                });
            });

            // ACODEC OPERATIONS
            document.getElementById('saveACodec').addEventListener('click', function() {
                const name = document.getElementById('acodec_name').value;
                fetch('<?= base_url('database-maintenance/acodec/add') ?>', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
                    body: 'name=' + encodeURIComponent(name)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showAlert(data.message, 'success');
                        bootstrap.Modal.getInstance(document.getElementById('addACodecModal')).hide();
                        location.reload();
                    } else { showAlert(data.message, 'danger'); }
                });
            });
            document.querySelectorAll('.edit-acodec').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.getElementById('edit_acodec_id').value = this.dataset.id;
                    document.getElementById('edit_acodec_name').value = this.dataset.name;
                    new bootstrap.Modal(document.getElementById('editACodecModal')).show();
                });
            });
            document.getElementById('updateACodec').addEventListener('click', function() {
                const id = document.getElementById('edit_acodec_id').value;
                const name = document.getElementById('edit_acodec_name').value;
                fetch(`<?= base_url('database-maintenance/acodec/update') ?>/${id}`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
                    body: 'name=' + encodeURIComponent(name)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showAlert(data.message, 'success');
                        bootstrap.Modal.getInstance(document.getElementById('editACodecModal')).hide();
                        location.reload();
                    } else { showAlert(data.message, 'danger'); }
                });
            });
            document.querySelectorAll('.delete-acodec').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const name = this.dataset.name;
                    if (confirm(`Are you sure you want to delete "${name}"?`)) {
                        fetch(`<?= base_url('database-maintenance/acodec/delete') ?>/${id}`, {
                            method: 'POST',
                            headers: {'X-Requested-With': 'XMLHttpRequest'}
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                showAlert(data.message, 'success');
                                location.reload();
                            } else { showAlert(data.message, 'danger'); }
                        });
                    }
                });
            });

            // LANGUAGE OPERATIONS
            document.getElementById('saveLanguage').addEventListener('click', function() {
                const name = document.getElementById('language_name').value;
                fetch('<?= base_url('database-maintenance/language/add') ?>', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
                    body: 'name=' + encodeURIComponent(name)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showAlert(data.message, 'success');
                        bootstrap.Modal.getInstance(document.getElementById('addLanguageModal')).hide();
                        location.reload();
                    } else { showAlert(data.message, 'danger'); }
                });
            });
            document.querySelectorAll('.edit-language').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.getElementById('edit_language_id').value = this.dataset.id;
                    document.getElementById('edit_language_name').value = this.dataset.name;
                    new bootstrap.Modal(document.getElementById('editLanguageModal')).show();
                });
            });
            document.getElementById('updateLanguage').addEventListener('click', function() {
                const id = document.getElementById('edit_language_id').value;
                const name = document.getElementById('edit_language_name').value;
                fetch(`<?= base_url('database-maintenance/language/update') ?>/${id}`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
                    body: 'name=' + encodeURIComponent(name)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showAlert(data.message, 'success');
                        bootstrap.Modal.getInstance(document.getElementById('editLanguageModal')).hide();
                        location.reload();
                    } else { showAlert(data.message, 'danger'); }
                });
            });
            document.querySelectorAll('.delete-language').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const name = this.dataset.name;
                    if (confirm(`Are you sure you want to delete "${name}"?`)) {
                        fetch(`<?= base_url('database-maintenance/language/delete') ?>/${id}`, {
                            method: 'POST',
                            headers: {'X-Requested-With': 'XMLHttpRequest'}
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                showAlert(data.message, 'success');
                                location.reload();
                            } else { showAlert(data.message, 'danger'); }
                        });
                    }
                });
            });

            // RATIO OPERATIONS
            document.getElementById('saveRatio').addEventListener('click', function() {
                const name = document.getElementById('ratio_name').value;
                fetch('<?= base_url('database-maintenance/ratio/add') ?>', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
                    body: 'name=' + encodeURIComponent(name)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showAlert(data.message, 'success');
                        bootstrap.Modal.getInstance(document.getElementById('addRatioModal')).hide();
                        location.reload();
                    } else { showAlert(data.message, 'danger'); }
                });
            });
            document.querySelectorAll('.edit-ratio').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.getElementById('edit_ratio_id').value = this.dataset.id;
                    document.getElementById('edit_ratio_name').value = this.dataset.name;
                    new bootstrap.Modal(document.getElementById('editRatioModal')).show();
                });
            });
            document.getElementById('updateRatio').addEventListener('click', function() {
                const id = document.getElementById('edit_ratio_id').value;
                const name = document.getElementById('edit_ratio_name').value;
                fetch(`<?= base_url('database-maintenance/ratio/update') ?>/${id}`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
                    body: 'name=' + encodeURIComponent(name)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showAlert(data.message, 'success');
                        bootstrap.Modal.getInstance(document.getElementById('editRatioModal')).hide();
                        location.reload();
                    } else { showAlert(data.message, 'danger'); }
                });
            });
            document.querySelectorAll('.delete-ratio').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const name = this.dataset.name;
                    if (confirm(`Are you sure you want to delete "${name}"?`)) {
                        fetch(`<?= base_url('database-maintenance/ratio/delete') ?>/${id}`, {
                            method: 'POST',
                            headers: {'X-Requested-With': 'XMLHttpRequest'}
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                showAlert(data.message, 'success');
                                location.reload();
                            } else { showAlert(data.message, 'danger'); }
                        });
                    }
                });
            });

            // SUBFORMAT OPERATIONS
            document.getElementById('saveSubformat').addEventListener('click', function() {
                const name = document.getElementById('subformat_name').value;
                fetch('<?= base_url('database-maintenance/subformat/add') ?>', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
                    body: 'name=' + encodeURIComponent(name)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showAlert(data.message, 'success');
                        bootstrap.Modal.getInstance(document.getElementById('addSubformatModal')).hide();
                        location.reload();
                    } else { showAlert(data.message, 'danger'); }
                });
            });
            document.querySelectorAll('.edit-subformat').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.getElementById('edit_subformat_id').value = this.dataset.id;
                    document.getElementById('edit_subformat_name').value = this.dataset.name;
                    new bootstrap.Modal(document.getElementById('editSubformatModal')).show();
                });
            });
            document.getElementById('updateSubformat').addEventListener('click', function() {
                const id = document.getElementById('edit_subformat_id').value;
                const name = document.getElementById('edit_subformat_name').value;
                fetch(`<?= base_url('database-maintenance/subformat/update') ?>/${id}`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
                    body: 'name=' + encodeURIComponent(name)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showAlert(data.message, 'success');
                        bootstrap.Modal.getInstance(document.getElementById('editSubformatModal')).hide();
                        location.reload();
                    } else { showAlert(data.message, 'danger'); }
                });
            });
            document.querySelectorAll('.delete-subformat').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const name = this.dataset.name;
                    if (confirm(`Are you sure you want to delete "${name}"?`)) {
                        fetch(`<?= base_url('database-maintenance/subformat/delete') ?>/${id}`, {
                            method: 'POST',
                            headers: {'X-Requested-With': 'XMLHttpRequest'}
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                showAlert(data.message, 'success');
                                location.reload();
                            } else { showAlert(data.message, 'danger'); }
                        });
                    }
                });
            });

            // POSTER OPERATIONS
            document.getElementById('purge-posters-btn').addEventListener('click', function() {
                if (confirm('Are you sure you want to purge all unused posters? This cannot be undone.')) {
                    const btn = this;
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Purging...';
                    
                    fetch('<?= base_url('database-maintenance/poster/purge') ?>', {
                        method: 'POST',
                        headers: {'X-Requested-With': 'XMLHttpRequest'}
                    })
                    .then(response => response.json())
                    .then(data => {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-trash"></i> Purge Unused Posters';
                        
                        const resultDiv = document.getElementById('purge-result');
                        resultDiv.style.display = 'block';
                        resultDiv.querySelector('.alert').textContent = data.message;
                        
                        if (data.status === 'success') {
                            setTimeout(() => { location.reload(); }, 3000);
                        }
                    });
                }
            });
        });
    </script>
<?= $this->endsection() ?>