<?php
if (isset($_GET['delete'])) {
    $id_user = $_GET['delete'];
    $queryDelete = mysqli_query($conn, "DELETE FROM user WHERE id = $id_user");
    header("location:?page=user&hapus=" . ($queryDelete ? "berhasil" : "gagal"));
}


if (isset($_GET['edit'])) {
    $id_user = $_GET['edit'];
    $selectEdit = mysqli_query($conn, "SELECT * FROM user WHERE id = $id_user");
    $rowEdit = mysqli_fetch_assoc($selectEdit);
}

if (isset($_POST['name'])) {
    $name     = $_POST['name'];
    $email    = $_POST['email'];
    $password = $_POST['password'];
    $level_id = $_POST['id_level'];

    if (isset($_GET['edit'])) {
        $id_user = $_GET['edit'];
        if (!empty($password)) {
            $hashed = sha1($password);
            $sql = "UPDATE user SET name='$name', email='$email', password='$hashed', id_level='$level_id' WHERE id='$id_user'";
        } else {
            $sql = "UPDATE user SET name='$name', email='$email', id_level='$level_id' WHERE id='$id_user'";
        }
        $update = mysqli_query($conn, $sql);
        header("location:?page=user&ubah=berhasil");
    } else {
        $hashed = sha1($password);
        $sql = "INSERT INTO user (name, email, password, id_level) VALUES ('$name', '$email', '$hashed', '$level_id')";
        $insert = mysqli_query($conn, $sql);
        header("location:?page=user&tambah=berhasil");
    }
}
?>
<div class="container">
  <div class="row">
    <div class="card">
      <div class="card-header text-center pt-4">
        <h5 class="mb-0">
          <?= isset($_GET['edit']) ? "Update User" : "New User"; ?>
        </h5>
      </div>
      <div class="card-body">
        <form role="form text-left" method="POST">
          <div class="mb-3">
             <input type="text" name="name" class="form-control" placeholder="Input Your Name"
              value="<?= isset($rowEdit) ? $rowEdit['name'] : '' ?>" required>
          </div>
          <div class="mb-3">
           <input type="email" name="email" class="form-control" placeholder="Input Your Email"
              value="<?= isset($rowEdit) ? $rowEdit['email'] : '' ?>" required>
          </div>
          <div class="mb-3">
            <input type="password" name="password" class="form-control"
              placeholder="<?= isset($_GET['edit']) ? 'Leave blank to keep current password' : 'Password' ?>">
          </div>
          <div class="mb-3">
          <div class="col-sm">
            <select name="id_level" class="form-control" required>
              <option value="">Select Level</option>
              <?php
              $levels = mysqli_query($conn, "SELECT * FROM level WHERE deleted_at IS NULL");
              while ($lvl = mysqli_fetch_assoc($levels)) :
              ?>
              <option value="<?= $lvl['id'] ?>" <?= isset($rowEdit) && $rowEdit['id_level'] == $lvl['id'] ? 'selected' : '' ?>>
                <?= $lvl['level_name'] ?>
              </option>
              <?php endwhile; ?>
            </select>
          </div>
        </div>
          <div class="text-center">
            <button type="submit" class="btn bg-gradient-primary w-100 my-4 mb-2"><?= isset($_GET['edit']) ? "Update" : "Submit" ?></button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>