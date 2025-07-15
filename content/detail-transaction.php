<?php
$price = 0;
$customers = mysqli_query($conn, "SELECT * FROM customer WHERE deleted_at IS NULL");

$queryService=mysqli_query($conn, "SELECT * FROM type_of_service WHERE deleted_at IS NULL");
$rowService=mysqli_fetch_all($queryService, MYSQLI_ASSOC);

$id_user = $_GET['detail'];
$selectDetail = mysqli_query($conn, "SELECT * FROM trans_order WHERE id = $id_user");
$rowDetail = mysqli_fetch_assoc($selectDetail);
if (isset($_GET['detail'])) {
    // print_r($id_user); die;
    $id_order = $rowDetail['id'];
    $selectService= mysqli_query($conn, "SELECT trans_order_detail.*, type_of_service.service_name FROM trans_order_detail 
    LEFT JOIN type_of_service ON type_of_service.id = trans_order_detail.id_service 
    WHERE id_order = $id_order");
    $rowSelectService = mysqli_fetch_all($selectService, MYSQLI_ASSOC);
}

if(isset($_POST['back'])){
  header('location:?page=transaction');
}
?>

<div class="col-xxl">
  <div class="card mb-4">
    <div class="card-header">
      <h5 class="mb-0">Transaction Detail</h5>
    </div>
    <div class="card-body">
      <form method="POST"> 
        <div class="row" align="right">
          <div class="col">
            <?php if(isset($rowDetail['order_status']) && $rowDetail['order_status'] == 1): ?>
              <a href="?page=print&print=<?= $rowDetail['id']?>" class="btn btn-primary mb-3">Print</a>
            <?php endif?>
          </div>
        </div>
        <!-- Customer Select Dropdown -->
        <div class="row mb-3">
        <label class="col-sm-2 col-form-label">Customer</label>
        <div class="col-sm-10">
            <select name="id_customer" class="form-control" required <?php if (isset($_GET['detail'])) echo 'disabled'; ?>>
              <option value="">Select Customer</option>
              <?php while ($cust = mysqli_fetch_assoc($customers)) : ?>
                  <option value="<?= $cust['id'] ?>" 
                      <?= (isset($rowDetail) && ((int)$rowDetail['id_customer'] === (int)$cust['id'])) ? 'selected' : '' ?>>
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
            <input type="date" name="order_date" class="form-control" value="<?= $rowDetail['order_date'] ?>" readonly>
          </div>
        </div>
        
        <!-- Status -->
        <div class="row mb-3">
          <label class="col-sm-2 col-form-label">Order Status</label>
          <div class="col-sm-10">
            <select name="order_status" class="form-control" required <?php if (isset($_GET['detail'])) echo 'disabled'; ?>>
                <option value="0" <?= (isset($rowDetail) && ((int)$rowDetail['order_status'] === 0)) ? 'selected' : '' ?>>Berlangsung</option>
                <option value="1" <?= (isset($rowDetail) && ((int)$rowDetail['order_status'] === 1)) ? 'selected' : '' ?>>Selesai</option>
            </select>

          </div>
        </div>
        
        <?php if(isset($rowDetail['order_status']) && $rowDetail['order_status'] == 1): ?>
            <!-- Order End Date -->
            <div class="row mb-3">
              <label class="col-sm-2 col-form-label">Order End Date</label>
              <div class="col-sm-10">
                <input type="date" name="order_end_date" class="form-control" value="<?= $rowDetail['order_end_date'] ?>" readonly>
              </div>
            </div>
        <?php endif; ?>


        <!-- Add Transaction -->
          
        <div id="container">
          <div class="row mb-3" id="newRow">
            <table id=myTable class="table table-stripped">
              <thead>
                <tr>
                  <th>Service</th>
                  <th>Quantity</th>
                  <th>Sub Total</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($rowSelectService as $data): ?>
                <tr>
                  <td><?= $data['service_name']?></td>
                  <td><?= $data['qty'] . " Kg"?></td>
                  <td><?= "Rp " . $data['subtotal']?></td>
                </tr>
                <?php endforeach?>
              </tbody>
            </table>
          </div>

          <div class="d-flex align-items-center justify-content-end mb-2">
            <label class="form-label me-2 mb-0">Total</label>
            <input type="text" class="form-control form-control" style="width: 250px;" readonly value="<?= "Rp " . $rowDetail['total'] ?>">
          </div>
          <?php if(isset($rowDetail['order_status']) && $rowDetail['order_status'] == 1): ?>
          <div class="d-flex align-items-center justify-content-end mb-2">
            <label class="form-label me-2 mb-0">Payment</label>
            <input type="text" class="form-control form-control" style="width: 250px;" readonly value="<?= "Rp " . $rowDetail['order_pay'] ?>">
          </div>
          <div class="d-flex align-items-center justify-content-end mb-2">
            <label class="form-label me-2 mb-0">Change</label>
            <input type="text" class="form-control form-control" style="width: 250px;" readonly value="<?= "Rp " . $rowDetail['order_change'] ?>">

          </div>
          <?php endif; ?>
        </div>


        <!-- Submit -->
        <div class="row">
          <div class="col-sm-3 mt-2">
            <button type="submit" class="btn btn-primary" name="back">Back</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

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
    </td>`;
  tbody.appendChild(tr);
});

</script>
