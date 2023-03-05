<div class="row"><div class="col-12"><h4>Media</h4></div></div>
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
                <?php
                /** @var $media \App\Entities\Media */
                if (isset($media)) {
                    if (is_object($media)) {
                        ?>
                        <span class="badge badge-info">ID: <?php echo $media->id ?></span><br>
                        <div class="row">
                            <div class="col-6">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" id="title" class="form-control" value="<?php echo $media->title ?>"><br>

                                <label for="director" class="form-label">Director</label>
                                <input type="text" id="director" class="form-control" value="<?php echo $media->director ?>"><br>

                                <label for="year" class="form-label">Year</label>
                                <input type="text" id="year" class="form-control" value="<?php echo $media->year ?>"><br>

                                <label for="runtime" class="form-label">Runtime</label>
                                <input type="text" id="runtime" class="form-control" value="<?php echo $media->runtime ?>"><br>

                                <label for="rating" class="form-label">Rating</label>
                                <input type="text" id="rating" class="form-control" value="<?php echo $media->rating ?>"><br>

                                <label for="genre" class="form-label">Genre</label>
                                <input type="text" id="genre" class="form-control" value="<?php echo $media->genre ?>"><br>
                            </div>
                            <div class="col-6">
                                <h5>Cast</h5>
                                <table class="table table-sm">
                                    <thead>
                                    <tr>
                                        <th scope="col">Actor</th>
                                        <th scope="col">Character</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $casts = explode("\n", $media->cast);
                                    foreach ($casts as $cast) {
                                        $parsed = explode(" as ", $cast);
                                        ?>
                                        <tr>
                                            <td><?php echo $parsed[0] ?></td>
                                            <td><?php echo $parsed[1] ?></td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php
                    } else {
                       echo "uh oh.";
                    }
                }
                /*
                 *         return [
            "Rating" => $this->rating,
            "Genre" => $this->genre,
            "Seen" => $this->seen,
            "Loaned" => $this->loaned,
            "Site" => $this->site,
            "Trailer" => $this->trailer,
            "Color" => $this->color,
            "Condition" => $this->cond,
            "Reigon" => $this->region,
        ];*/
                ?>
                <br>* empty fields are omitted.
            </div>
        </div>
    </div>
</div>
