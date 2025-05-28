<?php
    session_start();
    require_once 'config.php';

    // Check if user is logged in and is admin
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Admin') {
        header("Location: index.php");
        exit();
    }

    // Get admin info
    $stmt = $conn->prepare("SELECT name FROM users WHERE id = ? AND type = 'Admin'");
    $stmt->execute([$_SESSION['user_id']]);
    $admin = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h4>Admin Panel</h4>
            <p class="text-muted"><?php echo htmlspecialchars($admin['name']); ?></p>
        </div>
        <ul class="sidebar-nav">
            <li>
                <a href="#" data-section="products"><i class="fas fa-box"></i> Products</a>
            </li>
            <li>
                <a href="#" data-section="orders"><i class="fas fa-shopping-bag"></i> Orders</a>
            </li>
            <li class="sidebar-bottom">
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </div>

    <div class="main-content">

        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container-fluid">
                <button class="btn btn-link sidebar-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <a href="#" class="dropdown-toggle text-dark text-decoration-none" data-bs-toggle="dropdown">
                            <i class="fas fa-user-shield me-2"></i> Admin
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <div class="container-fluid py-4">
            
            <!-- Products Section -->
            <div class="section" id="products-section">
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Manage Products</h5>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                                    <i class="fas fa-plus me-2"></i>Add Product
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Price</th>
                                                <th>Quantity</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $stmt = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
                                            while ($product = $stmt->fetch()) {
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                                <td>Rs.<?php echo number_format($product['price'], 2); ?></td>
                                                <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-danger delete-product" 
                                                        data-id="<?php echo $product['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($product['name']); ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Orders Section -->
            <div class="section" id="orders-section" style="display: none;">
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Manage Orders</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Order ID</th>
                                                <th>Customer</th>
                                                <th>Total Amount</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $stmt = $conn->query("
                                                SELECT 
                                                    o.id,
                                                    u.name as customer_name,
                                                    o.total_amount,
                                                    o.status,
                                                    o.created_at,
                                                    o.address_line1,
                                                    o.address_line2,
                                                    o.city,
                                                    o.postal_code,
                                                    o.special_instructions
                                                FROM orders o
                                                JOIN users u ON o.user_id = u.id
                                                ORDER BY o.created_at DESC
                                            ");
                                            while ($order = $stmt->fetch()) {
                                                $statusClass = match($order['status']) {
                                                    'pending' => 'bg-warning',
                                                    'processing' => 'bg-info',
                                                    'completed' => 'bg-success',
                                                    'cancelled' => 'bg-danger',
                                                    default => 'bg-secondary'
                                                };
                                            ?>
                                            <tr>
                                                <td>#<?php echo $order['id']; ?></td>
                                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                                <td>Rs. <?php echo number_format($order['total_amount'], 2); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $statusClass; ?>">
                                                        <?php echo ucfirst($order['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-info view-order" 
                                                        data-id="<?php echo $order['id']; ?>"
                                                        data-customer="<?php echo htmlspecialchars($order['customer_name']); ?>"
                                                        data-address1="<?php echo htmlspecialchars($order['address_line1']); ?>"
                                                        data-address2="<?php echo htmlspecialchars($order['address_line2']); ?>"
                                                        data-city="<?php echo htmlspecialchars($order['city']); ?>"
                                                        data-postal="<?php echo htmlspecialchars($order['postal_code']); ?>"
                                                        data-instructions="<?php echo htmlspecialchars($order['special_instructions']); ?>"
                                                        data-status="<?php echo $order['status']; ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-primary update-status" 
                                                        data-id="<?php echo $order['id']; ?>"
                                                        data-status="<?php echo $order['status']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- View Order Modal -->
            <div class="modal fade" id="viewOrderModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Order Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6>Customer Information</h6>
                                    <p class="customer-name mb-1"></p>
                                    <p class="customer-address mb-1"></p>
                                    <p class="customer-city-postal mb-1"></p>
                                    <p class="special-instructions text-muted"></p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Order Status</h6>
                                    <span class="status-badge"></span>
                                </div>
                            </div>
                            <h6>Order Items</h6>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="order-items">

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Update Status Modal -->
            <div class="modal fade" id="updateStatusModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Update Order Status</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="updateStatusForm">
                                <input type="hidden" name="order_id" id="statusOrderId">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status" required>
                                        <option value="pending">Pending</option>
                                        <option value="processing">Processing</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Update Status</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Product Modal -->
            <div class="modal fade" id="addProductModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add New Product</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="addProductForm" action="php/product_actions.php" method="POST">
                                <input type="hidden" name="action" value="add">
                                <div class="mb-3">
                                    <label for="productName" class="form-label">Product Name</label>
                                    <input type="text" class="form-control" id="productName" name="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="productPrice" class="form-label">Price (Rs.)</label>
                                    <input type="number" class="form-control" id="productPrice" name="price" step="0.01" min="0" required>
                                </div>
                                <div class="mb-3">
                                    <label for="productQuantity" class="form-label">Quantity</label>
                                    <input type="number" class="form-control" id="productQuantity" name="quantity" min="0" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Add Product</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Custom JS -->
    <script>
        $(document).ready(function() {

            // Delete Product
            $('.delete-product').click(function() {
                var id = $(this).data('id');
                var name = $(this).data('name');
                
                Swal.fire({
                    title: 'Delete Product',
                    text: 'Are you sure you want to delete "' + name + '"?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post('php/product_actions.php', {
                            action: 'delete',
                            id: id
                        }, function(response) {
                            if(response.success) {
                                Swal.fire('Deleted!', 'Product has been deleted.', 'success')
                                .then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error!', 'Failed to delete product.', 'error');
                            }
                        }, 'json');
                    }
                });
            });

            // Add Product Form Submit
            $('#addProductForm').submit(function(e) {
                e.preventDefault();
                $.post('php/product_actions.php', $(this).serialize(), function(response) {
                    if(response.success) {
                        Swal.fire('Success!', 'Product has been added.', 'success')
                        .then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error!', 'Failed to add product.', 'error');
                    }
                }, 'json');
            });
        });
    </script>
    <!-- Dashboard JS -->
    <script src="js/dashboard.js"></script>
    <script src="js/admin.js"></script>

</body>
</html> 