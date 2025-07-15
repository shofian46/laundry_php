<?php
session_start();
include 'config/connection.php';

$user = isset($_SESSION['NAME']) ? $_SESSION['NAME'] : '';

$queryActiveTransaction = mysqli_query($conn, 'SELECT COUNT(id) AS total_transaction FROM trans_order WHERE deleted_at IS NULL');
$activeTransaction = mysqli_fetch_assoc($queryActiveTransaction)['total_transaction'];

$queryDoneTransaction = mysqli_query($conn, 'SELECT COUNT(id) AS total_transaction FROM trans_order WHERE deleted_at IS NOT NULL');
$doneTransaction = mysqli_fetch_assoc($queryDoneTransaction)['total_transaction'];

$queryTransaction = mysqli_query($conn, 'SELECT COUNT(id) AS total_transaction FROM trans_order');
$totalTransaction = mysqli_fetch_assoc($queryTransaction)['total_transaction'];

$queryIncome = mysqli_query($conn, 'SELECT SUM(total) AS income FROM trans_order');
$totalIncome = mysqli_fetch_assoc($queryIncome)['income'];

$queryService = mysqli_query($conn, "SELECT * FROM type_of_service");
$rowService = mysqli_fetch_all($queryService, MYSQLI_ASSOC);

$queryCustomer= mysqli_query($conn, "SELECT * FROM customer");
$rowCustomer = mysqli_fetch_all($queryCustomer, MYSQLI_ASSOC);

if (isset($_POST['customerName'])) {
    $id_customer = $_POST['id_customer'];
    $order_date = date('Y-m-d');
    $notes       = $_POST['notes'] ?? ''; 
    $idTrans = $_POST['trxId'];
    $notes = $_POST['notes'];

    $insertTransOrder = mysqli_query($conn, "INSERT INTO trans_order (id_customer, order_code, order_date, order_status, notes) VALUES ('$id_customer', '$idTrans', '$order_date', 0, '$notes')");
    
    if ($insertTransOrder) {
    // 1. Dapatkan ID dari transaksi utama yang baru saja dibuat
    $lastId = mysqli_insert_id($conn);

    // 2. Cek apakah ada data keranjang yang dikirim dari frontend
    if (isset($_POST['cart_data']) && !empty($_POST['cart_data'])) {
        // Decode string JSON menjadi array PHP
        $cart = json_decode($_POST['cart_data'], true);

        $total = 0;

        // Siapkan query untuk mencegah SQL Injection
        $queryDetail = "INSERT INTO trans_order_detail (id_order, id_service, qty, subtotal) VALUES (?, ?, ?, ?)";
        $stmtDetail = mysqli_prepare($conn, $queryDetail);

        if (! $stmtDetail) {
            die('Prepare Detail Gagal: ' . mysqli_error($conn));
        }
        $queryService = "SELECT id, price FROM type_of_service WHERE service_name = ?";
        $stmtService = mysqli_prepare($conn, $queryService);


        // 3. Loop setiap item di dalam keranjang
        foreach ($cart as $item) {
            $service_name = $item['service'];
            $qty = floatval($item['weight']);

            // Dapatkan id dan harga layanan dari DB berdasarkan nama (lebih aman)
            mysqli_stmt_bind_param($stmtService, "s", $service_name);
            mysqli_stmt_execute($stmtService);
            $resultService = mysqli_stmt_get_result($stmtService);
            $serviceData = mysqli_fetch_assoc($resultService);

            // Jika layanan tidak ditemukan di DB, lewati item ini
            if (!$serviceData) {
                continue;
            }

            $service_id = $serviceData['id'];
            $price = floatval($serviceData['price']);

            // 4. Hitung ulang subtotal di sisi server untuk keamanan
            $subtotal = $qty * $price;
            $total += $subtotal;

            // 5. Masukkan data ke tabel trans_order_detail
            mysqli_stmt_bind_param($stmtDetail, "iidd", $lastId, $service_id, $qty, $subtotal);
            mysqli_stmt_execute($stmtDetail);
        }

        // 6. Setelah loop selesai, update total harga di tabel trans_order
        $queryUpdateTotal = "UPDATE trans_order SET total = ? WHERE id = ?";
        $stmtUpdate = mysqli_prepare($conn, $queryUpdateTotal);
        mysqli_stmt_bind_param($stmtUpdate, "di", $total, $lastId);
        mysqli_stmt_execute($stmtUpdate);

        // Redirect ke halaman sukses (opsional, karena frontend sudah menangani UI)
        // Jika tidak perlu redirect, Anda bisa echo status sukses untuk dibaca fetch
        // header("location:?page=transaction&transaction=success");
        // exit();
        
        // Menutup statement yang disiapkan
        mysqli_stmt_close($stmtDetail);
        mysqli_stmt_close($stmtService);
        mysqli_stmt_close($stmtUpdate);
    }
}
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laundry system</title>
    <link rel="shortcut icon" href="template/assets/img/laundry.png" type="image/x-icon">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            text-align: center;
            color: #4a5568;
            margin-bottom: 10px;
            font-size: 2.5em;
            font-weight: 700;
        }

        .header .subtitle {
            text-align: center;
            color: #718096;
            font-size: 1.1em;
        }

        .main-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .card h2 {
            color: #4a5568;
            margin-bottom: 20px;
            font-size: 1.8em;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #4a5568;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-success {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(72, 187, 120, 0.3);
        }

        .btn-danger {
            background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(245, 101, 101, 0.3);
        }

        .btn-warning {
            background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
            color: white;
        }

        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(237, 137, 54, 0.3);
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .service-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4);
        }

        .service-card h3 {
            font-size: 1.2em;
            margin-bottom: 10px;
        }

        .service-card .price {
            font-size: 1.5em;
            font-weight: 700;
        }

        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .cart-table th,
        .cart-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .cart-table th {
            background: #f7fafc;
            font-weight: 600;
            color: #4a5568;
        }

        .cart-table tr:hover {
            background: #f7fafc;
        }

        .total-section {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-top: 20px;
        }

        .total-section h3 {
            font-size: 1.5em;
            margin-bottom: 10px;
        }

        .total-amount {
            font-size: 2.5em;
            font-weight: 700;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background: #fed7d7;
            color: #c53030;
        }

        .status-process {
            background: #feebc8;
            color: #dd6b20;
        }

        .status-ready {
            background: #c6f6d5;
            color: #2f855a;
        }

        .status-delivered {
            background: #bee3f8;
            color: #2b6cb0;
        }

        .transaction-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .transaction-item {
            background: #f7fafc;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 4px solid #667eea;
        }

        .transaction-item h4 {
            color: #4a5568;
            margin-bottom: 5px;
        }

        .transaction-item p {
            color: #718096;
            margin-bottom: 5px;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            line-height: 1;
        }

        .close:hover {
            color: #000;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
        }

        .stat-card h3 {
            font-size: 2em;
            margin-bottom: 10px;
        }

        .stat-card p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .header h1 {
                font-size: 2em;
            }

            .services-grid {
                grid-template-columns: 1fr;
            }
        }

        .receipt {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            font-family: 'Courier New', monospace;
        }

        .receipt-header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .receipt-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .receipt-total {
            border-top: 2px solid #333;
            padding-top: 10px;
            margin-top: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>My Laundry</h1>
            <p class="subtitle">Laundry System</p>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3 id="totalTransactions"><?= $totalTransaction ?></h3>
                <p>Total Transaksi</p>
            </div>
            <div class="stat-card">
                <h3 id="totalRevenue">Rp <?= number_format($totalIncome / 1000, 0, ',', '.') ?></h3>
                <p>Total Pendapatan</p>
            </div>
            <div class="stat-card">
                <h3 id="activeOrders"><?= $activeTransaction ?></h3>
                <p>Pesanan Aktif</p>
            </div>
            <div class="stat-card">
                <h3 id="completedOrders"><?= $doneTransaction ?></h3>
                <p>Pesanan Selesai</p>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Left Panel: New Transaction -->
            <div class="card">
                <h2>🛒 Transaksi Baru</h2>
                
                <form id="transactionForm" method="POST">
                    
                    <div class="form-group">
                    <label for="customerName">Nama Pelanggan</label>
                    <select name="customerName" id="customerName">
                        <option value="">Select Customer</option>
                        <?php foreach($rowCustomer as $data): ?>
                        <option 
                            value="<?= $data['customer_name'] ?>" 
                            data-id="<?= htmlspecialchars($data['id']) ?>"
                            data-phone="<?= htmlspecialchars($data['phone']) ?>" 
                            data-address="<?= htmlspecialchars($data['address']) ?>">
                            <?= $data['customer_name'] ?>
                        </option>
                        <?php endforeach ?>
                    </select>
                    </div>

                    <input type="hidden" name="trxId" id="trxIdInput" value="">
                    <input type="hidden" name="id_customer" id="id_customer" value="">
                    <input type="hidden" name="cart_data" id="cartDataInput">

                    <div class="form-group">
                    <label for="customerPhone">No. Telepon</label>
                    <input type="text" id="customerPhone" name="customerPhone" placeholder="Masukkan nomor telepon" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="customerAddress">Alamat</label>
                        <input type="text" id="customerAddress" name="customerAddress" placeholder="Masukkan alamat pelanggan" required>
                    </div>

                    <div class="form-group">
                        <small>New Customer?</small><br>
                        <small>Add New Coustomer Click</small>
                        <a href="dashboard.php?page=tambah-customer">Here!</a>
                    </div>

                    <div class="form-group">
                        <label>Pilih Layanan</label>
                        <div class="services-grid">
                            <?php foreach ($rowService as $key => $data):?>
                            <button type="button" class="service-card" onclick="addService('<?= $data['service_name']?>', <?= $data['price']?>)">
                                <h3><?= $data['service_name']?></h3>
                                <div class="price">Rp <?= number_format($data['price'], 0, ',', '.') ?></div>
                            </button>
                            <?php endforeach ?>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="serviceWeight">Berat/Jumlah</label>
                            <input type="number" id="serviceWeight" name="serviceWeight" step="0.1" min="0.1" required>
                        </div>
                        <div class="form-group">
                            <label for="serviceType">Jenis Layanan</label>
                            <select id="serviceType" name="serviceType" required>
                                <option value="">Pilih Layanan</option>
                                <?php foreach($rowService as $data):?>
                                <option value="<?= $data['service_name'] ?>"><?= $data['service_name'] ?></option>
                                <?php endforeach?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="notes">Catatan</label>
                        <textarea id="notes" name="notes" rows="3" placeholder="Catatan khusus untuk pesanan..."></textarea>
                    </div>

                    <button type="button" class="btn btn-primary" onclick="addToCart()" style="width: 100%; margin-bottom: 10px;">
                        ➕ Tambah ke Keranjang
                    </button>
                </form>

                <!-- Cart -->
                <div id="cartSection" style="display: none;">
                    <h3>📋 Keranjang Belanja</h3>
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Layanan</th>
                                <th>Qty</th>
                                <th>Harga</th>
                                <th>Subtotal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="cartItems">
                        </tbody>
                    </table>
                    
                    <div class="total-section">
                        <h3>Total Pembayaran</h3>
                        <div class="total-amount" id="totalAmount">Rp 0</div>
                        <button class="btn btn-success" onclick="processTransaction()" style="width: 100%; margin-top: 15px;">
                            💳 Proses Transaksi
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Panel: Transaction History -->
            <div class="card">
                <h2>📊 Riwayat Transaksi</h2>
                <div class="transaction-list" id="transactionHistory">
                </div>
                
                <button class="btn btn-warning" onclick="showAllTransactions()" style="width: 100%; margin-top: 15px;">
                    📋 Lihat Semua Transaksi
                </button>
            </div>
        </div>

        <!-- Action Buttons -->
        <div style="text-align: center; margin-top: 20px;">
            <button class="btn btn-primary" onclick="showReports()" style="margin: 0 10px;">
                📈 Laporan Penjualan
            </button>
            <button class="btn btn-warning" onclick="manageServices()" style="margin: 0 10px;">
                ⚙️ Kelola Layanan
            </button>
            <button class="btn btn-danger" onclick="clearCart()" style="margin: 0 10px;">
                🗑️ Bersihkan Keranjang
            </button>
            <button class="btn btn-secondary" onclick="clearAllTransactions()">🔄 Reset Transaksi</button>
            <a href="dashboard.php?page=transaction" class="btn btn-success" style="text-decoration:none;">List</a>

        </div>
    </div>

    <!-- Modal for Transaction Details -->
    <div id="transactionModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div id="modalContent"></div>
        </div>
    </div>

    <script>
        let cart = [];
        let transactions = JSON.parse(localStorage.getItem('laundryTransactions')) || [];
        let transactionCounter = transactions.length + 1;
        

        function addService(serviceName, price) {
            document.getElementById('serviceType').value = serviceName;
            document.getElementById('serviceWeight').focus();
        }

        // function addToCart() {
        //     const serviceType = document.getElementById('serviceType').value;
        //     const weight = parseFloat(document.getElementById('serviceWeight').value);
        //     const notes = document.getElementById('notes').value;

        //     if (!serviceType || !weight || weight <= 0) {
        //         alert('Mohon lengkapi semua field yang diperlukan!');
        //         return;
        //     }

        //     const prices = {
        //         'Cuci Kering': 5000,
        //         'Cuci Setrika': 7000,
        //         'Setrika Saja': 3000,
        //         'Dry Clean': 15000,
        //         'Cuci Sepatu': 25000,
        //         'Cuci Karpet': 20000
        //     };

        //     const price = prices[serviceType];
        //     const subtotal = price * weight;

        //     const item = {
        //         id: Date.now(),
        //         service: serviceType,
        //         weight: weight,
        //         price: price,
        //         subtotal: subtotal,
        //         notes: notes
        //     };

        //     cart.push(item);
        //     updateCartDisplay();
            
        //     // Clear form
        //     document.getElementById('serviceType').value = '';
        //     document.getElementById('serviceWeight').value = '';
        //     document.getElementById('notes').value = '';
        // }
        
        document.getElementById('customerName')
        .addEventListener('change', function() {
            const selectedOption = this.selectedOptions[0];
            
            // Ambil data dari attributes
            const phone   = selectedOption.dataset.phone  || '';
            const address = selectedOption.dataset.address || '';

            // Set value
            document.getElementById('customerPhone').value   = phone;
            document.getElementById('customerAddress').value = address;
        });

        // Update addToCart function to handle decimal with comma
        window.addToCart = function() {
            const serviceType = document.getElementById('serviceType').value;
            const weightValue = document.getElementById('serviceWeight').value;
            const weight = parseDecimal(weightValue);
            const notes = document.getElementById('notes').value;

            if (!serviceType || !weightValue || weight <= 0) {
                alert('Mohon lengkapi semua field yang diperlukan!');
                return;
            }

            const prices = {
                'Cuci Kering': 5000,
                'Cuci Setrika': 7000,
                'Setrika Saja': 3000,
                'Dry Clean': 15000,
                'Cuci Sepatu': 25000,
                'Cuci Karpet': 20000
            };

            const price = prices[serviceType];
            const subtotal = price * weight;

            const item = {
                id: Date.now(),
                service: serviceType,
                weight: weight,
                price: price,
                subtotal: subtotal,
                notes: notes
            };

            cart.push(item);
            updateCartDisplay();
            
            // Clear form
            document.getElementById('serviceType').value = '';
            document.getElementById('serviceWeight').value = '';
            document.getElementById('notes').value = '';
        }
        

        function updateCartDisplay() {
            const cartItems = document.getElementById('cartItems');
            const cartSection = document.getElementById('cartSection');
            const totalAmount = document.getElementById('totalAmount');

            if (cart.length === 0) {
                cartSection.style.display = 'none';
                return;
            }

            cartSection.style.display = 'block';
            
            let html = '';
            let total = 0;

            cart.forEach(item => {
                html += `
                    <tr>
                        <td>${item.service}</td>
                        <td>${item.weight} ${item.service.includes('Sepatu') ? 'pasang' : item.service.includes('Karpet') ? 'm²' : 'kg'}</td>
                        <td>Rp ${item.price.toLocaleString()}</td>
                        <td>Rp ${item.subtotal.toLocaleString()}</td>
                        <td>
                            <button class="btn btn-danger" onclick="removeFromCart(${item.id})" style="padding: 5px 10px; font-size: 12px;">
                                🗑️
                            </button>
                        </td>
                    </tr>
                `;
                total += item.subtotal;
            });

            cartItems.innerHTML = html;
            totalAmount.textContent = `Rp ${total.toLocaleString()}`;
        }

        function removeFromCart(itemId) {
            cart = cart.filter(item => item.id !== itemId);
            updateCartDisplay();
        }

        function clearCart() {
            cart = [];
            updateCartDisplay();
            document.getElementById('transactionForm').reset();
        }

        function processTransaction() {
            const transactionForm = document.getElementById('transactionForm'); // Dapatkan elemen form
            const customerName = document.getElementById('customerName').value;
            const customerPhone = document.getElementById('customerPhone').value;

            if (!customerName || !customerPhone || cart.length === 0) {
                alert('Mohon lengkapi data pelanggan dan pastikan ada item di keranjang!');
                return;
            }

            const total = cart.reduce((sum, item) => sum + item.subtotal, 0);

            const transaction = {
                id: `TRX-${transactionCounter.toString().padStart(3, '0')}`,
                customer: {
                    name: customerName,
                    phone: customerPhone,
                    address: document.getElementById('customerAddress').value
                },
                items: [...cart],
                total: total,
                date: new Date().toISOString(),
                status: 'pending'
            };

            // Setel nilai input tersembunyi untuk dikirim
            document.getElementById("trxIdInput").value = transaction.id;
            document.getElementById('id_customer').value = document.getElementById('customerName').selectedOptions[0].getAttribute('data-id');

            // Setel nilai input tersembunyi dari variabel cart
            document.getElementById('cartDataInput').value = JSON.stringify(cart);

            // Buat objek FormData dari form
            const formData = new FormData(transactionForm);

            // Kirim data secara asinkron menggunakan fetch
            fetch('', { // Kosongkan URL untuk mengirim ke halaman yang sama
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    // Jika server mengembalikan error, lempar error untuk ditangkap .catch()
                    throw new Error('Network response was not ok');
                }
                return response.text(); // Anda bisa mengabaikan respons jika tidak ada output dari PHP
            })
            .then(data => {
                // --- PROSES BERHASIL ---
                // Semua logika ini hanya berjalan SETELAH data berhasil dikirim ke server

                console.log("Data berhasil dikirim ke server."); // Logging untuk debug
                console.log("== Server Response ==");
                console.log(data);

                // 1. Simpan ke localStorage dan update counter
                transactions.push(transaction);
                localStorage.setItem('laundryTransactions', JSON.stringify(transactions));
                transactionCounter++;

                // 2. Tampilkan struk
                showReceipt(transaction);

                // 3. Bersihkan form dan cart, lalu update UI
                clearCart();
                updateTransactionHistory();
                updateStats();
            })
            .catch(error => {
                // --- PROSES GAGAL ---
                console.error('Terjadi masalah dengan proses transaksi:', error);
                alert('Gagal memproses transaksi. Silakan coba lagi.');
            });
        }

        document.getElementById('customerName').addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            const customerId = selectedOption.getAttribute('data-id');
            
            document.getElementById('id_customer').value = customerId;
        });

        function showReceipt(transaction) {
            const receiptHtml = `
                <div class="receipt">
                    <div class="receipt-header">
                        <h2>🧺 LAUNDRY RECEIPT</h2>
                        <p>ID: ${transaction.id}</p>
                        <p>Tanggal: ${new Date(transaction.date).toLocaleString('id-ID')}</p>
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <strong>Pelanggan:</strong><br>
                        ${transaction.customer.name}<br>
                        ${transaction.customer.phone}<br>
                        ${transaction.customer.address}
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <strong>Detail Pesanan:</strong><br>
                        ${transaction.items.map(item => `
                            <div class="receipt-item">
                                <span>${item.service} (${item.weight} ${item.service.includes('Sepatu') ? 'pasang' : item.service.includes('Karpet') ? 'm²' : 'kg'})</span>
                                <span>Rp ${item.subtotal.toLocaleString()}</span>
                            </div>
                        `).join('')}
                    </div>
                    
                    <div class="receipt-total">
                        <div class="receipt-item">
                            <span>TOTAL:</span>
                            <span>Rp ${transaction.total.toLocaleString()}</span>
                        </div>
                    </div>
                    
                    <div style="text-align: center; margin-top: 20px;">
                        <p>Terima kasih atas kepercayaan Anda!</p>
                        <p>Barang akan siap dalam 1-2 hari kerja</p>
                    </div>
                </div>
                
                <div style="text-align: center; margin-top: 20px;">
                    <button class="btn btn-primary" onclick="printReceipt()">🖨️ Cetak Struk</button>
                    <button class="btn btn-success" onclick="closeModal()">✅ Selesai</button>
                </div>
            `;
            
            document.getElementById('modalContent').innerHTML = receiptHtml;
            document.getElementById('transactionModal').style.display = 'block';
        }

        function printReceipt() {
            window.print();
        }

        function updateTransactionHistory() {
            const historyContainer = document.getElementById('transactionHistory');
            const recentTransactions = transactions.slice(-5).reverse();
            
            const html = recentTransactions.map(transaction => `
                <div class="transaction-item">
                    <h4>${transaction.id} - ${transaction.customer.name}</h4>
                    <p>📞 ${transaction.customer.phone}</p>
                    <p>🛍️ ${transaction.items.map(item => `${item.service} - ${item.weight}${item.service.includes('Sepatu') ? 'pasang' : item.service.includes('Karpet') ? 'm²' : 'kg'}`).join(', ')}</p>
                    <p>💰 Rp ${transaction.total.toLocaleString()}</p>
                    <p>📅 ${new Date(transaction.date).toLocaleString('id-ID')}</p>
                    <span class="status-badge status-${transaction.status}">${getStatusText(transaction.status)}</span>
                </div>
            `).join('');
            
            historyContainer.innerHTML = html || '<p>Belum ada transaksi</p>';
        }

        function getStatusText(status) {
            const statusMap = {
                'pending': 'Proses',
                'delivered': 'Selesai'
            };
            return statusMap[status] || status;
        }

        function updateStats() {
            const totalTransactions = transactions.length;
            const totalRevenue = transactions.reduce((sum, t) => sum + t.total, 0);
            const activeOrders = transactions.filter(t => t.status !== 'delivered').length;
            const completedOrders = transactions.filter(t => t.status === 'delivered').length;
            
            document.getElementById('totalTransactions').textContent = totalTransactions;
            document.getElementById('totalRevenue').textContent = `Rp ${totalRevenue.toLocaleString()}`;
            document.getElementById('activeOrders').textContent = activeOrders;
            document.getElementById('completedOrders').textContent = completedOrders;
        }

        function showAllTransactions() {
            const allTransactionsHtml = `
                <h2>📋 Semua Transaksi</h2>
                <div style="max-height: 400px; overflow-y: auto;">
                    ${transactions.map(transaction => `
                        <div class="transaction-item">
                            <h4>${transaction.id} - ${transaction.customer.name}</h4>
                            <p>📞 ${transaction.customer.phone}</p>
                            <p>🛍️ ${transaction.items.map(item => `${item.service} - ${item.weight}${item.service.includes('Sepatu') ? 'pasang' : item.service.includes('Karpet') ? 'm²' : 'kg'}`).join(', ')}</p>
                            <p>💰 Rp ${transaction.total.toLocaleString()}</p>
                            <p>📅 ${new Date(transaction.date).toLocaleString('id-ID')}</p>
                            <span class="status-badge status-${transaction.status}">${getStatusText(transaction.status)}</span>
                            <button class="btn btn-primary" onclick="updateTransactionStatus('${transaction.id}')" style="margin-top: 10px; padding: 5px 15px; font-size: 12px;">
                                📝 Update Status
                            </button>
                        </div>
                    `).join('')}
                </div>
            `;
            
            document.getElementById('modalContent').innerHTML = allTransactionsHtml;
            document.getElementById('transactionModal').style.display = 'block';
        }

        function showReports() {
            const today = new Date();
            const thisMonth = today.getMonth();
            const thisYear = today.getFullYear();
            
            const monthlyTransactions = transactions.filter(t => {
                const tDate = new Date(t.date);
                return tDate.getMonth() === thisMonth && tDate.getFullYear() === thisYear;
            });
            
            const monthlyRevenue = monthlyTransactions.reduce((sum, t) => sum + t.total, 0);
            
            const serviceStats = {};
            transactions.forEach(t => {
                t.items.forEach(item => {
                    if (!serviceStats[item.service]) {
                        serviceStats[item.service] = { count: 0, revenue: 0 };
                    }
                    serviceStats[item.service].count++;
                    serviceStats[item.service].revenue += item.subtotal;
                });
            });
            
            const reportsHtml = `
                <h2>📈 Laporan Penjualan</h2>
                
                <div class="stats-grid" style="margin-bottom: 20px;">
                    <div class="stat-card">
                        <h3>${transactions.length}</h3>
                        <p>Total Transaksi</p>
                    </div>
                    <div class="stat-card">
                        <h3>${monthlyTransactions.length}</h3>
                        <p>Transaksi Bulan Ini</p>
                    </div>
                    <div class="stat-card">
                        <h3>Rp ${monthlyRevenue.toLocaleString()}</h3>
                        <p>Pendapatan Bulan Ini</p>
                    </div>
                </div>
                
                <h3>📊 Statistik Layanan</h3>
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Layanan</th>
                            <th>Jumlah Order</th>
                            <th>Total Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${Object.entries(serviceStats).map(([service, stats]) => `
                            <tr>
                                <td>${service}</td>
                                <td>${stats.count}</td>
                                <td>Rp ${stats.revenue.toLocaleString()}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
            
            document.getElementById('modalContent').innerHTML = reportsHtml;
            document.getElementById('transactionModal').style.display = 'block';
        }

        function manageServices() {
            const servicesHtml = `
                <h2>⚙️ Kelola Layanan</h2>
                <p>Fitur ini memungkinkan Anda mengelola jenis layanan dan harga.</p>
                
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Layanan</th>
                            <th>Harga</th>
                            <th>Satuan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Cuci Kering</td>
                            <td>Rp 5.000</td>
                            <td>per kg</td>
                            <td><span class="status-badge status-ready">Aktif</span></td>
                        </tr>
                        <tr>
                            <td>Cuci Setrika</td>
                            <td>Rp 7.000</td>
                            <td>per kg</td>
                            <td><span class="status-badge status-ready">Aktif</span></td>
                        </tr>
                        <tr>
                            <td>Setrika Saja</td>
                            <td>Rp 3.000</td>
                            <td>per kg</td>
                            <td><span class="status-badge status-ready">Aktif</span></td>
                        </tr>
                        <tr>
                            <td>Dry Clean</td>
                            <td>Rp 15.000</td>
                            <td>per kg</td>
                            <td><span class="status-badge status-ready">Aktif</span></td>
                        </tr>
                        <tr>
                            <td>Cuci Sepatu</td>
                            <td>Rp 25.000</td>
                            <td>per pasang</td>
                            <td><span class="status-badge status-ready">Aktif</span></td>
                        </tr>
                        <tr>
                            <td>Cuci Karpet</td>
                            <td>Rp 20.000</td>
                            <td>per m²</td>
                            <td><span class="status-badge status-ready">Aktif</span></td>
                        </tr>
                    </tbody>
                </table>
                
                <div style="text-align: center; margin-top: 20px;">
                    <button class="btn btn-primary" onclick="alert('Fitur akan segera tersedia!')">
                        ➕ Tambah Layanan Baru
                    </button>
                </div>
            `;
            
            document.getElementById('modalContent').innerHTML = servicesHtml;
            document.getElementById('transactionModal').style.display = 'block';
        }

        function updateTransactionStatus(transactionId) {
            const transaction = transactions.find(t => t.id === transactionId);
            if (!transaction) return;
            
            const statusOptions = [
                { value: 'pending', text: 'Sedang Proses' },
                { value: 'delivered', text: 'Selesai' }
            ];
            
            const statusHtml = `
                <h2>📝 Update Status Transaksi</h2>
                <h3>${transaction.id} - ${transaction.customer.name}</h3>
                <p>Status saat ini: <span class="status-badge status-${transaction.status}">${getStatusText(transaction.status)}</span></p>
                
                <div class="form-group">
                    <label>Pilih Status Baru:</label>
                    <select id="newStatus" style="width: 100%; padding: 10px; margin: 10px 0;">
                        ${statusOptions.map(option => `
                            <option value="${option.value}" ${transaction.status === option.value ? 'selected' : ''}>
                                ${option.text}
                            </option>
                        `).join('')}
                    </select>
                    <label>
                        Payment:
                    </label>
                    <input type="number" id="payment" name="payment" required>
                </div>
                
                <div style="text-align: center; margin-top: 20px;">
                    <button class="btn btn-success" onclick="saveStatusUpdate('${transactionId}')">
                        ✅ Simpan Update
                    </button>
                    <button class="btn btn-danger" onclick="closeModal()" style="margin-left: 10px;">
                        ❌ Batal
                    </button>
                </div>
            `;
            
            document.getElementById('modalContent').innerHTML = statusHtml;
            document.getElementById('transactionModal').style.display = 'block';
        }

        function saveStatusUpdate(transactionId) {
            const newStatus = document.getElementById('newStatus').value;
            const transactionIndex = transactions.findIndex(t => t.id === transactionId);
            
            if (transactionIndex !== -1) {
                transactions[transactionIndex].status = newStatus;
                localStorage.setItem('laundryTransactions', JSON.stringify(transactions));
                updateTransactionHistory();
                updateStats();
                closeModal();
                alert('Status berhasil diupdate!');
            }
        }

        function closeModal() {
            document.getElementById('transactionModal').style.display = 'none';
        }

        function formatNumber(input) {
            // Replace comma with dot for decimal separator
            let value = input.value.replace(',', '.');
            
            // Ensure only valid decimal number
            if (!/^\d*\.?\d*$/.test(value)) {
                value = value.slice(0, -1);
            }
            
            // Update input value
            input.value = value;
        }

        function parseDecimal(value) {
            // Handle both comma and dot as decimal separator
            return parseFloat(value.toString().replace(',', '.')) || 0;
        }

        // Initialize the application
        document.addEventListener('DOMContentLoaded', function() {
            updateTransactionHistory();
            updateStats();
            
            // Add event listener for weight input to handle decimal with comma
            const weightInput = document.getElementById('serviceWeight');
            weightInput.addEventListener('input', function() {
                formatNumber(this);
            });
            
            // Close modal when clicking outside
            window.onclick = function(event) {
                const modal = document.getElementById('transactionModal');
                if (event.target === modal) {
                    closeModal();
                }
            };
        });

        

        // Update cart display to show decimal properly
        function updateCartDisplay() {
            const cartItems = document.getElementById('cartItems');
            const cartSection = document.getElementById('cartSection');
            const totalAmount = document.getElementById('totalAmount');

            if (cart.length === 0) {
                cartSection.style.display = 'none';
                return;
            }

            cartSection.style.display = 'block';
            
            let html = '';
            let total = 0;

            cart.forEach(item => {
                const unit = item.service.includes('Sepatu') ? 'pasang' : 
                           item.service.includes('Karpet') ? 'm²' : 'kg';
                
                // Format weight to show decimal properly
                const formattedWeight = item.weight % 1 === 0 ? 
                    item.weight.toString() : 
                    item.weight.toFixed(1).replace('.', ',');
                
                html += `
                    <tr>
                        <td>${item.service}</td>
                        <td>${formattedWeight} ${unit}</td>
                        <td>Rp ${item.price.toLocaleString()}</td>
                        <td>Rp ${item.subtotal.toLocaleString()}</td>
                        <td>
                            <button class="btn btn-danger" onclick="removeFromCart(${item.id})" style="padding: 5px 10px; font-size: 12px;">
                                🗑️
                            </button>
                        </td>
                    </tr>
                `;
                total += item.subtotal;
            });

            cartItems.innerHTML = html;
            totalAmount.textContent = `Rp ${total.toLocaleString()}`;
        }

        // Add some sample data for demonstration
        function addSampleData() {
            const sampleTransactions = [
                {
                    id: 'TRX-001',
                    customer: { name: 'John Doe', phone: '0812-3456-7890', address: 'Jl. Merdeka 123' },
                    items: [{ service: 'Cuci Setrika', weight: 2.5, price: 7000, subtotal: 17500 }],
                    total: 17500,
                    date: new Date().toISOString(),
                    status: 'process'
                },
                {
                    id: 'TRX-002',
                    customer: { name: 'Jane Smith', phone: '0813-7654-3210', address: 'Jl. Sudirman 456' },
                    items: [{ service: 'Cuci Kering', weight: 3, price: 5000, subtotal: 15000 }],
                    total: 15000,
                    date: new Date(Date.now() - 3600000).toISOString(),
                    status: 'ready'
                }
            ];
            
            if (transactions.length === 0) {
                transactions = sampleTransactions;
                localStorage.setItem('laundryTransactions', JSON.stringify(transactions));
                transactionCounter = transactions.length + 1;
            }
        }

        // Initialize with sample data
        //addSampleData();

        function clearAllTransactions() {
            if (confirm('Anda yakin ingin menghapus semua transaksi?')) {
            localStorage.removeItem('laundryTransactions');
            transactions = [];
            transactionCounter = 1;
            updateTransactionHistory();
            updateStats();
            alert('Semua transaksi telah dihapus');
            }
        }

        </script>
    </body>
</html>

