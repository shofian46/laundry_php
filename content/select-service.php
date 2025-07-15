<?php 
$query=mysqli_query($config, "SELECT * FROM type_of_service");
$rowQuery = mysqli_fetch_all($query, MYSQLI_ASSOC);
?>
<!-- Examples -->
<div class="row mb-5">
        <?php foreach ($rowQuery as $key => $data): ?>
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><?= $data['service_name']?></h5>
                    <p class="card-text">
                        <?= $data['description']?>
                    </p>
                    <a href="?page=tambah-transaction&service=<?= $data['id'] ?>" class="btn btn-outline-primary">Select</a>
                </div>
            </div>
        </div>
        <?php endforeach ?>
    </div>
<!-- Examples -->