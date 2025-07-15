<?php
// Delete operation
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $queryDelete = mysqli_query($conn, "DELETE FROM type_of_service WHERE id = $id");
    header("location:?page=service&hapus=" . ($queryDelete ? "berhasil" : "gagal"));
}

// Edit operation: fetch the existing record for editing
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $selectEdit = mysqli_query($conn, "SELECT * FROM type_of_service WHERE id = $id");
    $rowEdit = mysqli_fetch_assoc($selectEdit);
}

// Insert/Update operation when the form is submitted
if (isset($_POST['service_name'])) {
    $service_name = $_POST['service_name'];
    $price        = $_POST['price']*1000;
    $description  = $_POST['description'];

    if (isset($_GET['edit'])) {
        $id = $_GET['edit'];
        // Update: change service name, price, description and set the updated_at timestamp
        $sql = "UPDATE type_of_service 
                SET service_name='$service_name', 
                    price='$price', 
                    description='$description', 
                    updated_at=NOW() 
                WHERE id='$id'";
        $update = mysqli_query($conn, $sql);
        header("location:?page=service&ubah=berhasil");
    } else {
        // Insert: add a new service and set the created_at timestamp
        $sql = "INSERT INTO type_of_service (service_name, price, description, created_at) 
                VALUES ('$service_name', '$price', '$description', NOW())";
        $insert = mysqli_query($conn, $sql);
        header("location:?page=service&tambah=berhasil");
    }
}
?>

<div class="col-xxl">
  <div class="card mb-4">
    <div class="card-header d-flex align-items-center">
      <h5 class="mb-0">
        <?= isset($_GET['edit']) ? "Update Service" : "New Service"; ?>
      </h5>
    </div>
    <div class="card-body">
      <form method="POST">
        <div class="mb-3">
            <input type="text" name="service_name" class="form-control" placeholder="Service Name" value="<?= isset($rowEdit) ? $rowEdit['service_name'] : '' ?>" autofocus required>
        </div>

        <div class="mb-3">
          <input type="number" name="price" class="form-control" placeholder="Service Price" step="0.01" value="<?= isset($rowEdit) ? $rowEdit['price'] : '' ?>" required>
        </div>

        <div class="mb-3">
          <textarea name="description" class="form-control" placeholder="Service Description" required><?= isset($rowEdit) ? $rowEdit['description'] : '' ?></textarea>
        </div>

        <div class="row justify-content-end">
          <div class="col-sm-10" align="right">
            <button type="submit" class="btn btn-primary">
              <?= isset($_GET['edit']) ? "Update" : "Save" ?>
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
