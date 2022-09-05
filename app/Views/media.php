<div class="table-responsive">
    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th>TITLE</th>
            </tr>
        </thead>
        <tbody>
            <?php
            /** @var $media \App\Controllers\Media */
            foreach ($media as $row) {
                echo "<tr><td>" . $row['title'] . "</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>
<nav aria-label="Page navigation">
    <ul class="pagination justify-content-center">
        <li class="page-item">
            <a class="page-link" href="#" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a>
        </li>
        <li class="page-item"><a class="page-link" href="#">1</a></li>
        <li class="page-item"><a class="page-link" href="#">2</a></li>
        <li class="page-item"><a class="page-link" href="#">3</a></li>
        <li class="page-item"><a class="page-link" href="#">Next</a></li>
        <li class="page-item">
            <a class="page-link" href="#" aria-label="Next"><span aria-hidden="true">&raquo;</span></a>
        </li>

    </ul>
</nav>
