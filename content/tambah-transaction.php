<?php

    if (isset($_GET['delete'])) {
    $id_user = $_GET['delete'];
    $queryDelete = mysqli_query($conn, "DELETE FROM trans_order WHERE id = $id_user");
    header("location:?page=user&hapus=" . ($queryDelete ? "berhasil" : "gagal"));
    }
$price = 0;
$customers = mysqli_query($conn, "SELECT * FROM customer WHERE deleted_at IS NULL");

if (isset($_GET['delete'])) {
    $id_transaction = $_GET['delete'];
    $queryDelete = mysqli_query($conn, "UPDATE trans_order SET deleted_at = NOW() WHERE id = $id_transaction");
    header("location:?page=transaction&hapus=" . ($queryDelete? "berhasil" : "gagal"));
}

if (isset($_POST['submit'])) {
    // Generate the next order code.
    $orderKode = mysqli_query($conn, "SELECT order_code FROM trans_order WHERE order_code LIKE 'ord%' ORDER BY id DESC LIMIT 1");
    $rowOrderCode = mysqli_fetch_assoc($orderKode);
    $lastNum = 0;
    if ($rowOrderCode && isset($rowOrderCode['order_code'])) {
      $lastNum = intval(substr($rowOrderCode['order_code'], 3)); // Strip "ord"
    }
    $nextCode = 'ord' . ($lastNum + 1); 

    $id_customer = $_POST['id_customer'];
    $order_date  = $_POST['order_date'];
    $notes       = $_POST['notes'] ?? ''; 

    $insertTransOrder = mysqli_query($conn, "INSERT INTO trans_order (id_customer, order_code, order_date, order_status) VALUES ('$id_customer', '$nextCode', '$order_date', 0)");
    
    if ($insertTransOrder) {
      $lastId = mysqli_insert_id($conn);
      $id_services = $_POST['id_service']; 
      $qtys        = floatval($_POST['qty']);
      $total= 0;
      for ($i = 0; $i < count($id_services); $i++) {
        $service_id = $id_services[$i];
        $qty = floatval($_POST['qty'][$i]); // Convert grams to kg
        
        $selectPrice = mysqli_query($conn, "SELECT price FROM type_of_service WHERE id = '$service_id'");
        $rowPrice    = mysqli_fetch_assoc($selectPrice);
        
        if (!$rowPrice) {
          continue;
        }
        $price    = floatval($rowPrice['price']);
        // print_r($price); die;
        $subtotal = $qty* $price;
        $total+= $subtotal;
        
        $insertOrderDetail = mysqli_query($conn, "INSERT INTO trans_order_detail (id_order, id_service, qty, subtotal, notes) VALUES ('$lastId', '$service_id', '$qty', '$subtotal', '$notes')");
      }
    $updateTransOrder = mysqli_query($conn, "UPDATE `trans_order` SET `total`='$total' WHERE id = $lastId");

      
      header("location:?page=transaction&transaction=success");
    }
}

$queryService=mysqli_query($conn, "SELECT * FROM type_of_service WHERE deleted_at IS NULL");
$rowService=mysqli_fetch_all($queryService, MYSQLI_ASSOC);
?>

<div class="col-xxl">
  <div class="card mb-4">
    <div class="card-header">
      <h5 class="mb-0">New Transaction</h5>
    </div>
    <div class="card-body">
      <form method="POST"> 
        <!-- Customer Select Dropdown -->
        <div class="row mb-3">
        <label class="col-sm-2 col-form-label">Customer</label>
        <div class="col-sm-10">
            <select name="id_customer" class="form-control" required>
            <option value="">Select Customer</option>
            <?php
            while ($cust = mysqli_fetch_assoc($customers)) :
            ?>
                <option value="<?= $cust['id'] ?>"
                <?= (isset($rowEdit) && $rowEdit['id_customer'] == $cust['id']) ? 'selected' : '' ?>>
                <?= $cust['customer_name'] ?> - <?= $cust['phone'] ?>
                </option>
            <?php endwhile; ?>
            </select>
        </div>
        </div>

        <!-- Order Date -->
        <div class="row mb-3">
          <label class="col-sm-2 col-form-label">Order Date</label>
          <div class="col-sm-10">
            <input type="date" name="order_date" class="form-control" value="<?= date('Y-m-d') ?>" readonly>
          </div>
        </div>

        <!-- Quantity -->
        <!-- <div class="row mb-3">
          <label class="col-sm-2 col-form-label">Quantity (grams)</label>
          <div class="col-sm-10">
            <input type="number" id="qty" name="qty" class="form-control" required>
          </div>
        </div> -->

        <!-- Status -->
        <!-- <div class="row mb-3">
          <label class="col-sm-2 col-form-label">Order Status</label>
          <div class="col-sm-10">
            <select name="order_status" class="form-control" required>
              <option value="0">Berlangsung</option>
              <option value="1">Selesai</option>
            </select>
          </div>
        </div> -->

        <!-- Total (auto from qty / 1000 * price) -->
        <!-- <div class="row mb-3">
          <label class="col-sm-2 col-form-label">Total</label>
          <div class="col-sm-10">
            <input type="number" id="total" name="total" class="form-control" readonly required>
          </div>
        </div> -->

        <!-- Payment -->
        <!-- <div class="row mb-3">
          <label class="col-sm-2 col-form-label">Order Pay</label>
          <div class="col-sm-10">
            <input type="number" id="order_pay" name="order_pay" class="form-control" required>
          </div>
        </div> -->

        <!-- Change -->
        <!-- <div class="row mb-3">
          <label class="col-sm-2 col-form-label">Change</label>
          <div class="col-sm-10">
            <input type="number" id="order_change" name="order_change" class="form-control" readonly required>
          </div>
        </div> -->

        <!-- Notes -->
        <div class="row mb-3">
          <label class="col-sm-2 col-form-label">Notes</label>
          <div class="col-sm-10">
            <textarea name="notes" id="notes" name="notes" class="form-control"></textarea>
          </div>
        </div> 

        <!-- Add Transaction -->
        <div align="right" class="mb-3">
          <button type="button" class="btn btn-primary addTransaction" id="addTransaction">Add Transaction</button>
        </div>
        <div id="container">
          <div class="row mb-3" id="newRow">
            <table id=myTable>
              <thead>
                <tr class="text-center">
                  <th>Service</th>
                  <th>Quantity</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>
                    <select name="id_service[]" class="service form-control" required>
                      <option value="">Select Service</option>
                      <?php foreach($rowService as $key => $data): ?>
                        <option value="<?= $data['id'] ?>"><?= $data['service_name'] ?></option>
                      <?php endforeach ?>
                    </select>
                  </td>
                  <td>
                    <input type="number" step="any" name="qty[]" class="qty form-control" required>
                  </td>
                  <td class="text-center">
                    <button class="btn btn-danger deleteRow" name="delete">Delete</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Submit -->
        <div class="row">
          <div class="col-sm-3 mt-2">
            <button type="submit" class="btn btn-primary" name="submit">Submit Order</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- JS to Auto Calculate -->
<script>
const addTransactionBtn = document.getElementById('addTransaction');
const tbody = document.querySelector('#myTable tbody');

addTransactionBtn.addEventListener('click', function() {
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td>
      <select name="id_service[]" class="service form-control" required>
        <option value="">Select Service</option>
        <?php foreach($rowService as $data): ?>
          <option value="<?= $data['id'] ?>"><?= $data['service_name'] ?></option>
        <?php endforeach ?>
      </select>
    </td>
    <td>
      <input type="number" step="any" name="qty[]" class="qty form-control" required>
    </td>
    <td class="text-center">
      <button type="button" class="btn btn-danger deleteRow">Delete</button>
    </td>`;
  tbody.appendChild(tr);
});

tbody.addEventListener('click', function(e) {
  if (e.target && e.target.classList.contains('deleteRow')) {
    e.target.closest('tr').remove();
  }
});
</script>

