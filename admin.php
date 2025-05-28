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
            <li class="active">
                <a href="#"><i class="fas fa-box"></i> Products</a>
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
            
            <!-- Product Management Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Product Management</h5>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addProductModal">
                                <i class="fas fa-plus me-2"></i>Add New Product
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

</body>
</html> 