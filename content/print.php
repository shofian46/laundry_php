<?php
$price = 0;
$customers = mysqli_query($conn, "SELECT * FROM customer WHERE deleted_at IS NULL");

$queryService=mysqli_query($conn, "SELECT * FROM type_of_service WHERE deleted_at IS NULL");
$rowService=mysqli_fetch_all($queryService, MYSQLI_ASSOC);

$id_user = $_GET['print'];
$selectDetail = mysqli_query($conn, "SELECT * FROM trans_order WHERE id = $id_user");
$rowDetail = mysqli_fetch_assoc($selectDetail);
if (isset($_GET['print'])) {
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

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Struk Transaksi</title>
  <link rel="stylesheet" type="text/css" href="template/assets/css/print.css" media="print">
  <style>
    /* Optional: Tambahan gaya untuk preview sebelum cetak */
    body {
      font-size: 12px;
      margin: 10px;
      line-height: 1.3;
    }

    .receipt {
      max-width: 58mm;
      margin: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th, td {
      padding: 4px 0;
      text-align: left;
    }

    @media print {
      button, .no-print {
        display: none !important;
      }
    }

    .item-block {
      margin-bottom: 0px;
      margin: 2px 0px  2px 2px;
    }

    .row-inline {
      display: flex;
      justify-content: space-between;
      font-size: 12px;
    }

    .summary p {
      font-weight: bold;
      font-size: 12px;
    }

    .item-block p {
      margin: 0;
      font-size: 12px;
    }

    .item-block .row-inline {
      display: flex;
      justify-content: space-between;
    }

    @media print {
      @page {
        size: 58mm auto;
        margin: 0;
      }

      body {
        margin: 0;
        width: 58mm;
      }
    }

    .logo-container svg {
      width: 20px !important;
    }

    .logo-container app-brand-text{
      font-size: 10px !important;
    }

  </style>
</head>
<body>

  <div class="receipt">

  <p class="item-block mt-3">Order Code: <?= $rowDetail['order_code'] ?></p>
  <p class="item-block">Order Date: <?= $rowDetail['order_date'] ?></p>
  <p class="item-block">Pickup Date: <?= $rowDetail['order_end_date'] ?></p>
  <p>============================</p>
  <hr>

  <?php foreach($rowSelectService as $item): ?>
    <div class="item-block">
      <p><?= $item['service_name'] ?></p>
      <div class="row-inline">
        <span><?= $item['qty']?> Kg</span>
        <span style="float: right;">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></span>
      </div>
    </div>
  <?php endforeach; ?>

  <hr>
  <div class="summary">
    <p style="text-align:right;">Total: Rp <?= $rowDetail['total'] ?></p>
    <p style="text-align:right;">Dibayar: Rp <?= $rowDetail['order_pay'] ?></p>
    <p style="text-align:right;">Kembalian: Rp <?= $rowDetail['order_change'] ?></p>
  </div>
  <hr>
  <p style="text-align:center">Terima kasih 🙏</p>
</div>


  <script>
    window.onload = function() {
      window.print();
    }
  </script>

</body>
</html>

