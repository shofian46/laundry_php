<?php 
    $queryCustomer = mysqli_query($conn, "SELECT * FROM customer WHERE deleted_at IS NULL");
    $rowCustomer = mysqli_fetch_all($queryCustomer, MYSQLI_ASSOC);
?>
<div class="card">
    <h5 class="card-header">Data Customer</h5>
    <h5 align="right" class="me-3">
        <a href="?page=tambah-customer" class="btn btn-primary mb-3" >Add Customer</a>
    </h5>
    <div class="table-responsive text-nowrap">
        <table class="table table-striped">
        <thead>
            <tr>
            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">No</th>
            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Customer Name</th>
            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Phone</th>
            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Address</th>
            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Action</th>
            </tr>
        </thead>
        <tbody class="table-border-bottom-0">
            <?php foreach ($queryCustomer as $key => $row):?>
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
                      <?= $row['customer_name']; ?>
                    </div>
                  </div>
                    </td>
                    <td><?= $row['phone'] ?></td>
                    <td><?= $row['address'] ?></td>
                    <td class="align-middle">
                        <a href="?page=tambah-customer&edit=<?php echo $row['id']?>" class="text-primary me-2 font-weight-bold text-xs" name="edit">Update</a>
                        <a onclick="return confirm('Are you sure wanna delete this data?')" href="?page=tambah-customer&delete=<?php echo $row['id']?>" class="text-danger me-2 font-weight-bold text-xs" name="delete">Delete</a>
                    </td>
                </tr>
                <?php endforeach?>
        </tbody>
        </table>
    </div>
</div>
</div>