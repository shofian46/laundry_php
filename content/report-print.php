<?php
  $periode = isset($_GET['periode']) ? $_GET['periode']: date('Y-m');

  // ubah periode untuk ditampilkan: “Juni 2025”
  $labelPeriode = date('F Y', strtotime($periode . '-01'));

  // 1) Query dibatasi bulan terpilih
  $sql = "
    SELECT 
      o.id,
      o.order_code,
      o.order_date,
      c.customer_name,
      o.total,
      o.order_pay,
      o.order_change,
      o.order_status
    FROM trans_order o
    LEFT JOIN customer c ON o.id_customer = c.id
    WHERE DATE_FORMAT(o.order_date, '%Y-%m') = '$periode'
    ORDER BY o.order_date ASC
  ";
  $res = mysqli_query($conn, $sql);
  if (!$res) {
      die("Query error: " . mysqli_error($conn));
  }

  // 2) Inisialisasi akumulasi
  $sumTotal  = 0;
  $sumPay    = 0;
  $sumChange = 0;

  // helper: format rupiah
  function rupiah($angka) {
      return 'Rp ' . number_format($angka, 0, ',', '.');
  }
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Laporan Transaksi Laundry</title>
  <style>
    @media print {
        .d-print-none {
            display: none !important;
        }
    }

  </style>
</head>
<body>
  <!-- Tombol cetak -->
  <button id="print-btn" onclick="window.print()" class="btn btn-primary mb-3 d-print-none">🖨️ Cetak Laporan</button>

  <!-- Tampilkan periode terpilih -->
  <h4 class="text-center">LAPORAN TRANSAKSI SELURUH</h4>
  <p>Periode: <?= $labelPeriode ?></p>

  <table class="table table-striped">
    <thead>
      <tr>
        <th>#</th>
        <th>Kode Order</th>
        <th>Tanggal</th>
        <th>Customer</th>
        <th class="text-right">Total</th>
        <th class="text-right">Bayar</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php $i = 1; while($row = mysqli_fetch_assoc($res)): 
        $sumTotal  += $row['total'];
        $sumPay    += $row['order_pay'];
        $sumChange += $row['order_change'];
      ?>
      <tr>
        <td><?= $i++ ?></td>
        <td><?= htmlspecialchars($row['order_code']) ?></td>
        <td><?= date('d/m/Y', strtotime($row['order_date'])) ?></td>
        <td><?= htmlspecialchars($row['customer_name']) ?></td>
        <td class="text-right"><?= rupiah($row['total']) ?></td>
        <td class="text-right"><?= rupiah($row['order_pay']) ?></td>
        <td><?= $row['order_status']==1 ? 'Lunas' : 'Belum Lunas' ?></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
    <tfoot>
      <tr>
        <th colspan="4" class="text-right">TOTAL</th>
        <th class="text-right"><?= rupiah($sumTotal) ?></th>
        <th class="text-right"><?= rupiah($sumPay) ?></th>
        <th></th>
        <th></th>
      </tr>
    </tfoot>
  </table>

</body>
</html>
