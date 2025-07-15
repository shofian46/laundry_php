<?php
if (isset($_GET['delete'])) {
    $id_customer = $_GET['delete'];
    $queryDelete = mysqli_query($conn, "UPDATE customer SET deleted_at = NOW() WHERE id = $id_customer");
    header("location:?page=customer&hapus=" . ($queryDelete? "berhasil" : "gagal"));
}

if (isset($_GET['edit'])) {
    $id_customer = $_GET['edit'];
    $selectEdit = mysqli_query($conn, "SELECT * FROM customer WHERE id = $id_customer");
    $rowEdit = mysqli_fetch_assoc($selectEdit); 
}

if (isset($_POST['customer_name'])) {
    $name    = $_POST['customer_name'];
    $phone   = $_POST['phone'];
    $address = $_POST['address'];

    if (isset($_GET['edit'])) {
        $id_customer = $_GET['edit'];
        $sql = "UPDATE customer SET customer_name='$name', phone='$phone', address='$address' WHERE id='$id_customer'";
        $update = mysqli_query($conn, $sql);
        header("location:?page=customer&ubah=berhasil");
    } else {
        $sql = "INSERT INTO customer (customer_name, phone, address) VALUES ('$name', '$phone', '$address')";
        $insert = mysqli_query($conn, $sql);
        header("location:?page=customer&tambah=berhasil");
    }
}
?>
<div class="container">
  <div class="row">
    <div class="card">
      <div class="card-header text-center pt-4">
        <h5 class="mb-0">
          <?= isset($_GET['edit']) ? "Update Customer" : "New Customer"; ?>
        </h5>
      </div>
      <div class="card-body">
          <form method="POST">
            <div class="mb-3">
                <input
                  type="text"
                  name="customer_name"
                  class="form-control"
                  id="customer-name"
                  placeholder="Customer Name"
                  value="<?= isset($rowEdit) ? $rowEdit['customer_name'] : '' ?>" autofocus required
                />
            </div>
            <div class="mb-3">
              <input
                type="text"
                name="phone"
                class="form-control"
                id="customer-phone"
                placeholder="Number Phone"
                value="<?= isset($rowEdit) ? $rowEdit['phone'] : '' ?>"
                required
              />
            </div>
            <div class="mb-3">
              <textarea
                name="address"
                class="form-control"
                id="customer-address"
                placeholder="Address"
                required
              ><?= isset($rowEdit) ? $rowEdit['address'] : '' ?></textarea>
            </div>
            <div class="justify-content-end">
              <button type="submit" class="btn btn-primary">
                <?= isset($_GET['edit']) ? "Update" : "Submit" ?>
              </button>
            </div>
          </form>
      </div>
    </div>
  </div>
</div>
</div>