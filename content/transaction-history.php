<?php     
    $queryCustomer = mysqli_query($conn, 
    "SELECT trans_order.*, customer.customer_name
    FROM trans_order
    LEFT JOIN customer ON customer.id = trans_order.id_customer
    WHERE trans_order.deleted_at IS NOT NULL
    ");
    $rowCustomer = mysqli_fetch_all($queryCustomer, MYSQLI_ASSOC);
    
    if(isset($_POST['back'])){
        header('location:?page=transaction');
    }
?>
<div class="card">
<h5 class="card-header">Transaction History</h5>
<form action="" method="post">
    <h5 align="right" class="me-3">
        <button type="submit" class="btn btn-primary" name="back">Back</button>
    </h5>
</form>
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
                <td>Rp <?= $row['total'] ?></td>
                <td>
                    <a href="?page=detail-transaction&detail=<?php echo $row['id']?>" class = "btn btn-primary" name="detail">Detail</a>
                    <a onclick="return confirm('Are you sure you want to delete this user?')" 
                    href="?page=tambah-transaction&delete=<?= $row['id'] ?>" 
                    class="btn btn-danger">Delete</a>
                </td>
            </tr>
            <?php endforeach?>
    </tbody>
    </table>
</div>
</div>
<!--/ Striped Rows -->