<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-info-circle"></i> About Media Organizer</h4>
            </div>
            <div class="card-body">
                <section class="mb-4">
                    <h5>Project Information</h5>
                    <p>Media Organizer is a modern web application designed to help you organize and manage your media collection. It serves as a modern replacement for Griffith, focusing on ease of use and accessibility.</p>
                    <ul class="list-unstyled">
                        <li><strong><i class="bi bi-github"></i> Project Location:</strong> <a href="https://github.com/drdelaney/MediaOrganizer/" target="_blank">github.com/drdelaney/MediaOrganizer/</a></li>
                        <li><strong><i class="bi bi-bug"></i> Issue Tracker:</strong> <a href="https://github.com/drdelaney/MediaOrganizer/issues" target="_blank">Report an issue</a></li>
                        <li><strong><i class="bi bi-archive"></i> Original Griffith Source:</strong> <a href="https://github.com/FiloSottile/Griffith" target="_blank">github.com/FiloSottile/Griffith (Archived)</a></li>
                        <li><strong><i class="bi bi-shield-check"></i> License:</strong> Licensed under <a href="https://www.gnu.org/licenses/gpl-3.0.html" target="_blank">GPL-3.0</a></li>
                    </ul>
                </section>

                <hr>

                <section class="mt-4">
                    <h5>API Credits</h5>
                    <p>This project retrieves media metadata and artwork through the following services:</p>
                    <div class="list-group">
                        <a href="https://www.omdbapi.com/" target="_blank" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">OMDb API</h6>
                                <small>Movie & TV Data</small>
                            </div>
                            <p class="mb-1 text-muted small">A RESTful web service to obtain movie information.</p>
                        </a>
                        <a href="https://www.themoviedb.org/" target="_blank" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">The Movie Database (TMDB)</h6>
                                <small>Movie & TV Data</small>
                            </div>
                            <p class="mb-1 text-muted small">A popular, user-editable database for movies and TV shows.</p>
                        </a>
                        <a href="https://thetvdb.com/" target="_blank" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">TheTVDB</h6>
                                <small>TV & Movie Data</small>
                            </div>
                            <p class="mb-1 text-muted small">An open database for television fans.</p>
                        </a>
                        <a href="https://api.igdb.com/" target="_blank" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">IGDB</h6>
                                <small>Video Game Data</small>
                            </div>
                            <p class="mb-1 text-muted small">A video game database, intended for both game consumers and video game professionals.</p>
                        </a>
                        <a href="https://musicbrainz.org/" target="_blank" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">MusicBrainz</h6>
                                <small>Music Data</small>
                            </div>
                            <p class="mb-1 text-muted small">An open music encyclopedia that collects music metadata.</p>
                        </a>
                        <a href="https://www.upcitemdb.com/" target="_blank" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">UPCItemDB</h6>
                                <small>Barcode Data</small>
                            </div>
                            <p class="mb-1 text-muted small">A large public UPC database, providing barcode lookup services.</p>
                        </a>
                    </div>
                </section>

                <hr>

                <section class="mt-4">
                    <h5>Open Source Credits</h5>
                    <p>This project is made possible by the following open source technologies:</p>
                    <div class="list-group">
                        <a href="https://codeigniter.com/" target="_blank" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">CodeIgniter 4</h6>
                                <small>PHP Framework</small>
                            </div>
                            <p class="mb-1 text-muted small">A powerful PHP framework with a very small footprint.</p>
                        </a>
                        <a href="https://getbootstrap.com/" target="_blank" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Bootstrap 5</h6>
                                <small>CSS Framework</small>
                            </div>
                            <p class="mb-1 text-muted small">The world's most popular front-end open source toolkit.</p>
                        </a>
                        <a href="https://icons.getbootstrap.com/" target="_blank" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Bootstrap Icons</h6>
                                <small>Icon Library</small>
                            </div>
                            <p class="mb-1 text-muted small">Free, high quality, open source icon library.</p>
                        </a>
                        <a href="https://www.php.net/" target="_blank" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">PHP</h6>
                                <small>Language</small>
                            </div>
                            <p class="mb-1 text-muted small">A popular general-purpose scripting language that is especially suited to web development.</p>
                        </a>
                        <a href="https://mariadb.org/" target="_blank" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">MariaDB / MySQL</h6>
                                <small>Database</small>
                            </div>
                            <p class="mb-1 text-muted small">Reliable, scalable, and secure open source relational database.</p>
                        </a>
                        <a href="https://www.postgresql.org/" target="_blank" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">PostgreSQL</h6>
                                <small>Database</small>
                            </div>
                            <p class="mb-1 text-muted small">The world's most advanced open source relational database.</p>
                        </a>
                        <a href="https://www.sqlite.org/" target="_blank" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">SQLite 3</h6>
                                <small>Database</small>
                            </div>
                            <p class="mb-1 text-muted small">A C-language library that implements a small, fast, self-contained, high-reliability, full-featured, SQL database engine.</p>
                        </a>
                    </div>
                </section>
            </div>
            <div class="card-footer text-center text-muted">
                Version 1.0.0 &bull; &copy; <?= date('Y') ?> <?= esc(app_name()) ?> &bull; <a href="https://www.gnu.org/licenses/gpl-3.0.html" target="_blank" class="text-muted">GPL-3.0</a>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
