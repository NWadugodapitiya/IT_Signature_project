<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
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
            <h4><?php echo htmlspecialchars($user['name']); ?></h4>
            <p class="text-muted">User Account</p>
        </div>
        <ul class="sidebar-nav">
            <li class="active">
                <a href="#"><i class="fas fa-store"></i> Products</a>
            </li>
            <li>
                <a href="#" class="cart-trigger">
                    <i class="fas fa-shopping-cart"></i> Cart
                    <span class="badge bg-primary cart-count" style="margin-left: 10px;">0</span>
                </a>
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
                    <div class="dropdown me-3">
                        <button class="btn btn-primary add-to-cart cart-trigger">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="badge bg-primary cart-count">0</span>
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <div class="container-fluid py-4">
            <!-- Products Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Available Products</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <?php
                                $stmt = $conn->query("SELECT * FROM products ORDER BY name");
                                while ($product = $stmt->fetch()) {
                                ?>
                                <div class="col-md-4 col-lg-3">
                                    <div class="card product-card h-100" data-product-id="<?php echo $product['id']; ?>">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                            <p class="card-text text-primary fw-bold">Rs. <?php echo number_format($product['price'], 2); ?></p>
                                            <p class="card-text <?php echo $product['quantity'] > 0 ? ($product['quantity'] > 10 ? 'text-success' : 'text-warning') : 'text-danger'; ?>">
                                                <?php echo $product['quantity'] > 0 ? 'In Stock: ' . $product['quantity'] : 'Out of Stock'; ?>
                                            </p>
                                            <button class="btn btn-primary w-100 add-to-cart" data-product-id="<?php echo $product['id']; ?>" <?php echo $product['quantity'] <= 0 ? 'disabled' : ''; ?>>
                                                <i class="fas fa-cart-plus me-2"></i>Add to Cart
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shopping Cart Modal -->
            <div class="modal fade" id="cartModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Shopping Cart</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Total</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="cart-items">
                                        <!-- card data  -->
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-end mt-3">
                                <h5>Total: <span class="cart-total">Rs. 0.00</span></h5>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Continue Shopping</button>
                            <button type="button" class="btn btn-primary checkout-btn">Proceed to Checkout</button>
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
    <script src="js/dashboard.js"></script>
</body>
</html> 