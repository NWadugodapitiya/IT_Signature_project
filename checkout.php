<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$stmt = $conn->prepare("SELECT name, mobile FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$stmt = $conn->prepare("
    SELECT 
        c.id as cart_id,
        c.qty,
        p.id as product_id,
        p.name,
        p.price,
        p.quantity as stock
    FROM card c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll();

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['qty'];
}

if (count($cart_items) === 0) {
    header("Location: user.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="container py-5">
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <h4 class="mb-4">Shipping Details</h4>
                        
                        <form id="checkoutForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mobile Number</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['mobile']); ?>" readonly>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Address Line 1</label>
                                <input type="text" class="form-control" name="address1" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Address Line 2</label>
                                <input type="text" class="form-control" name="address2">
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">City</label>
                                    <input type="text" class="form-control" name="city" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Postal Code</label>
                                    <input type="text" class="form-control" name="postal_code" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Special Instructions</label>
                                <textarea class="form-control" name="instructions" rows="3"></textarea>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="mb-4">Order Summary</h4>

                        <div class="order-summary">
                            <?php foreach ($cart_items as $item): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span><?php echo htmlspecialchars($item['name']); ?> × <?php echo $item['qty']; ?></span>
                                <span>Rs. <?php echo number_format($item['price'] * $item['qty'], 2); ?></span>
                            </div>
                            <?php endforeach; ?>
                            <hr>
                            <div class="d-flex justify-content-between mb-2">
                                <strong>Total</strong>
                                <strong>Rs. <?php echo number_format($total, 2); ?></strong>
                            </div>
                        </div>

                        <button type="button" class="btn btn-primary w-100 mt-3" id="placeOrderBtn">
                            <i class="fas fa-shopping-bag me-2"></i>Place Order
                        </button>
                        <a href="user.php" class="btn btn-outline-secondary w-100 mt-2">
                            <i class="fas fa-arrow-left me-2"></i>Back to Shopping
                        </a>

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
            $('#placeOrderBtn').click(function() {
                const formData = new FormData($('#checkoutForm')[0]);
                
                // Validate form
                if (!$('#checkoutForm')[0].checkValidity()) {
                    $('#checkoutForm')[0].reportValidity();
                    return;
                }
                
                // Disable button to prevent double submission
                $(this).prop('disabled', true);
                
                $.ajax({
                    url: 'php/process_order.php',
                    type: 'POST',
                    data: {
                        address1: $('input[name="address1"]').val(),
                        address2: $('input[name="address2"]').val(),
                        city: $('input[name="city"]').val(),
                        postal_code: $('input[name="postal_code"]').val(),
                        instructions: $('textarea[name="instructions"]').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: response.message,
                                icon: 'success',
                                showConfirmButton: false,
                                timer: 2000
                            }).then(() => {
                                window.location.href = 'user.php';
                            });
                        } else {
                            Swal.fire('Error', response.message || 'Failed to place order', 'error');
                            $('#placeOrderBtn').prop('disabled', false);
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to place order', 'error');
                        $('#placeOrderBtn').prop('disabled', false);
                    }
                });
            });
        });
    </script>
</body>
</html> 