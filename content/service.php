<?php 
$queryUser = mysqli_query($conn, "SELECT * FROM type_of_service");
$rowsUser = mysqli_fetch_all($queryUser, MYSQLI_ASSOC);
?>
<div class="card">
  <h5 class="card-header">Service Data</h5>
  <h5 align="right" class="me-3">
    <a href="?page=tambah-service" class="btn btn-primary mb-3">Add Service</a>
  </h5>
  <div class="table-responsive text-nowrap">
    <table class="table table-hover">
      <thead>
        <tr>
          <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">No</th>
          <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Service Name</th>
          <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Price</th>
          <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Description</th>
          <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Action</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        <?php foreach ($rowsUser as $key => $row): ?>
        <tr>
          <td>
            <div class="d-flex px-2 py-1">
              <div class="d-flex flex-column justify-content-center">
                <?= $key+1; ?>
              </div>
            </div>
          </td>
          <td><?= $row['service_name'] ?></td>
          <td>Rp.<?= $row['price']/1000 ?></td>
          <td><?= $row['description'] ?></td>
          <td class="align-middle">
            <a href="?page=tambah-service&edit=<?= $row['id'] ?>" class="text-primary me-2 font-weight-bold text-xs">Edit</a>
            <a onclick="return confirm('Are you sure you want to delete this service?')" 
               href="?page=tambah-service&delete=<?= $row['id'] ?>" 
               class="text-danger me-2 font-weight-bold text-xs">Delete</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>