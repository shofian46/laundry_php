<?php 
$queryUser = mysqli_query($conn, "
    SELECT level.level_name AS level_name, user.*
    FROM user
    LEFT JOIN level ON user.id_level = level.id
");
$rowsUser = mysqli_fetch_all($queryUser, MYSQLI_ASSOC);
?>
<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-header pb-0">
        <h6>Users table</h6>
      </div>
      <h5 align="right" class="me-3">
        <a href="?page=tambah-user" class="btn btn-primary mb-3">Add User</a>
      </h5>
      <div class="card-body px-0 pt-0 pb-2">
        <div class="table-responsive p-0">
          <table class="table align-items-center mb-0">
            <thead>
              <tr>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">No</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Level</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Name</th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Email</th>
                <th class="text-secondary opacity-7"></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rowsUser as $key => $row): ?>
              <tr>
                <td>
                  <div class="d-flex px-2 py-1">
                    <div class="d-flex flex-column justify-content-center">
                      <?= $key+1; ?>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="d-flex px-2 py-1">
                    <div class="d-flex flex-column justify-content-center">
                      <?= $row['level_name']; ?>
                    </div>
                  </div>
                </td>
                <td>
                 <?= $row['name']; ?>
                </td>
                <td class="align-middle text-center text-sm">
                  <?= $row['email']; ?>
                </td>
                <td class="align-middle">
                  <a href="?page=tambah-user&edit=<?=$row['id'];?>" class="text-primary me-2 font-weight-bold text-xs" data-toggle="tooltip" data-original-title="Edit user">
                    Edit
                  </a>
                  <a href="?page=tambah-user&delete=<?=$row['id'];?>" class="text-danger font-weight-bold text-xs" onclick="return confirm('Are you sure you want to delete this user?')">
                    Delete
                  </a>
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