<?php 
    $queryCustomer = mysqli_query($conn, 
    "SELECT trans_order.*, customer.customer_name
    FROM trans_order
    LEFT JOIN customer ON customer.id = trans_order.id_customer
    WHERE trans_order.deleted_at IS NULL
    ");
    $rowCustomer = mysqli_fetch_all($queryCustomer, MYSQLI_ASSOC);
    function rupiah($angka){
	
	$hasil_rupiah = "Rp " . number_format($angka,0,',','.');
	return $hasil_rupiah;
 
}
?>
<!-- Striped Rows -->
<div class="card">
<h5 class="card-header">On Going Transaction</h5>
<h5 align="right" class="me-3">
    <a href="?page=tambah-transaction" class="btn btn-primary mb-3" >Add Transaction</a>
    <a href="?page=transaction-history" class="btn btn-warning mb-3" >Transaction History</a>
</h5>
<div class="table-responsive text-nowrap">
    <table class="table table-striped">
    <thead>
        <tr>
        <th>No</th>
        <th>Customer</th>
        <th>Order Code</th>
        <th>Order Date</th>
        <th>Order Status</th>
        <th>Total</th>
        <th>Action</th>
        </tr>
    </thead>
    <tbody class="table-border-bottom-0">
        <?php foreach ($rowCustomer as $key => $row):?>
            <tr>
                <td><?= $key + 1 ?></td>
                <td><?= $row['customer_name'] ?></td>
                <td><?= $row['order_code'] ?></td>
                <td><?= $row['order_date'] ?></td>
                <td><?= ($row['order_status'] == 0) ? "Transaksi Berhasil" : "Selesai" ?></td>
                <td><?= rupiah($row['total']) ?></td>
                <td>
                    <a href="?page=detail-transaction&detail=<?php echo $row['id']?>" class = "btn btn-primary" name="detail">Detail</a>
                    <a href="?page=pickup&id=<?php echo $row['id']?>" class = "btn btn-warning" name="check">Pickup</a>
                    <a onclick="return confirm('Are you sure wanna delete this data?')" href="?page=tambah-transaction&delete=<?php echo $row['id']?>" class = "btn btn-danger" name="delete">Delete</a>
                </td>
                <input type="hidden" name="id_order" id="modal_order_id" value="<?php $row['id'] ?>">
            </tr>
            <?php endforeach?>
    </tbody>
    </table>
</div>
</div>
<!--/ Striped Rows -->