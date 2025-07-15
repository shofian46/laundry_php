<?php 
    if (isset($_POST['pay'])) {
    // Retrieve payment and order ID
    $payment = ($_POST['payment']);
    $id_order = isset($_GET['id']) ? $_GET['id'] : '';
    
    // Validate order id
    if (empty($id_order)) {
        die("Error: Order ID is missing.");
    }
    
    // Fetch total and customer ID at the same time
    $selectTotal = mysqli_query($conn, "SELECT total, id_customer FROM trans_order WHERE id = $id_order");
    if (!$selectTotal) {
        die("Error: Query failed.");
    }
    $row = mysqli_fetch_assoc($selectTotal);
    if (!$row) {
        die("Error: Order not found.");
    }
    
    // Use the fetched values
    $total = $row['total'];
    $id_customer = $row['id_customer'];
    
    // Calculate the change in payment
    $order_change = $payment - $total;
    
    // Update the order record (this sets deleted_at to NOW())
    $queryUpdate = mysqli_query($conn, "UPDATE trans_order SET order_pay = '$payment', order_change = '$order_change', order_status = 1, order_end_date = NOW(), deleted_at = NOW() WHERE id = $id_order");
    if ($queryUpdate) {
        // Sanitize notes input
        $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
        
        // Insert the pickup record using the previously fetched customer ID
        $insert = mysqli_query($conn, "INSERT INTO trans_laundry_pickup (id_order, id_customer, pickup_date) VALUES ('$id_order', '$id_customer', NOW())");
        if (!$insert) {
            die("Error: Could not insert pickup record.");
        }
    }
    header("Location: ?page=transaction-history&payment=success");
}

?>

<div class="container-xxl">
      <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
          <!-- Register -->
          <div class="card">
            <div class="card-body">
              <form id="formAuthentication" class="mb-3" method="POST">
                <div class="mb-3">
                  <label for="payment" class="form-label">Payment</label>
                  <input type="text" class="form-control" id="payment" name="payment" placeholder="Enter payment amount" autofocus required/>
                </div>
                <!-- <div class="mb-3">
                  <label for="notes" class="form-label">Notes</label>
                  <input type="text" class="form-control" id="notes" name="notes" placeholder="Enter notes" autofocus/>
                </div> -->
                <div class="mb-3">
                  <button class="btn btn-primary" type="submit" name="pay">Pay</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>