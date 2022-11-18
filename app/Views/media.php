<div class="row"><div class="col-12"><h2>Media</h2></div></div>
<?php if(isset($validation)):?>
    <div class="alert alert-danger"><?= $validation->listErrors() ?></div>
<?php endif;?>
<?php if(isset($not_found)):?>
    <div class="alert alert-danger"><?= $not_found ?></div>
<?php endif;?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-xl-8">
                        <form action="/media/search" class="row align-items-center justify-content-xl-start justify-between">
                            <div class="col-auto">
                                <div class="form-outline">
                                    <input type="text" name="search" class="form-control" id="InputForSearch" value="">
                                    <label for="InputForSearch" class="form-label">Search</label>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="d-flex align-items-center">
                                    <label for="InputForFilter" class="me-2">Filter</label>
                                    <select name="filter" class="form-select" id="InputForFilter">
                                        <option value="None">None</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-xl-4">
                        <div class="text-xl-end mt-xl-0 mt-2">
                            <button type="button" class="btn btn-danger mb-2 me-2" data-bs-toggle="modal" data-bs-target="#saveModal">
                                <i class="fas fa-circle-plus"></i> Add New Media</button>
                            <div id="saveModal" class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <form action="/media/create" method="post">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="staticBackdropLabel">New Media</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                                <div class="container">
                                                    <div class="row">
                                                        <div class="form-outline mb-4 col-4">
                                                            <input type="text" name="title" class="form-control" id="InputForTitle" value="">
                                                            <label for="InputForTitle" class="form-label">Title</label>
                                                        </div>
                                                        <div class="col-2 mt-1">
                                                            <label for="InputForType">Type</label>
                                                        </div>
                                                        <div class="col-6">
                                                            <select name="type" class="form-control" id="InputForType" value="">
                                                                <option value="DVD">DVD</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Save</button>
                                        </div>
                                        </form>
                                    </div>
                                </div>

                            </div>
                            <button class="btn btn-light mb-2">Export</button>
                        </div>
                    </div>
                </div>
                <table class="table table-centered table-nowrap table-striped table-sm">
                    <thead>
                    <tr>
                        <th>Title</th>
                        <th>Genre</th>
                        <th>Director</th>
                        <th>Cast</th>
                        <th>Runtime</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    /** @var $media \App\Controllers\Media */
                    if (isset($media)) {
                        if (is_object($media)) {
                            echo "<tr>"
                                . "<td>" . $media->title . "</td>"
                                . "<td>" . $media->genre . "</td>"
                                . "<td>" . $media->director . "</td>"
                                // Let's not display all the cast, as it can greatly increase the size of a row.
                                . "<td>" . (strlen($media->cast) > 100 ? substr($media->cast, 0, 100) . "..." : $media->cast) . "</td>"
                                . "<td>" . $media->runtime . "</td>"
                            . "</tr>";
                        } else if (isset($media[0])) {
                            foreach ($media as $row) {
                                echo "<tr>"
                                    . "<td>" . $row->title . "</td>"
                                    . "<td>" . $row->genre . "</td>"
                                    . "<td>" . $row->director . "</td>"
                                    . "<td>" . (strlen($row->cast) > 100 ? substr($row->cast, 0, 100) . "..." : $row->cast) . "</td>"
                                    . "<td>" . $row->runtime . "</td>"
                                . "</tr>";
                           }
                        }
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<nav aria-label="Page navigation">
    <ul class="pagination justify-content-center">
        <li class="page-item">
            <a class="page-link" href="#" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a>
        </li>
        <li class="page-item"><a class="page-link" href="#">1</a></li>
        <li class="page-item"><a class="page-link" href="#">2</a></li>
        <li class="page-item"><a class="page-link" href="#">3</a></li>
        <li class="page-item">
            <a class="page-link" href="#" aria-label="Next"><span aria-hidden="true">&raquo;</span></a>
        </li>

    </ul>
</nav>
