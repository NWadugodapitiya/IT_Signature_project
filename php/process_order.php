<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Please login first']));
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    
    // Get shipping details from form
    $address1 = trim($_POST['address1'] ?? '');
    $address2 = trim($_POST['address2'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');
    $instructions = trim($_POST['instructions'] ?? '');
    
    // Validate required fields
    if (empty($address1) || empty($city) || empty($postal_code)) {
        die(json_encode(['success' => false, 'message' => 'Please fill in all required fields']));
    }
    
    // Get cart items
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
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();
    
    if (empty($cart_items)) {
        die(json_encode(['success' => false, 'message' => 'Your cart is empty']));
    }
    
    // Calculate total
    $total = 0;
    foreach ($cart_items as $item) {
        $total += $item['price'] * $item['qty'];
    }
    
    try {
        $conn->beginTransaction();
        
        // Create order with shipping details
        $stmt = $conn->prepare("
            INSERT INTO orders (
                user_id, 
                total_amount, 
                address_line1, 
                address_line2, 
                city, 
                postal_code, 
                special_instructions,
                status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        
        $stmt->execute([
            $user_id,
            $total,
            $address1,
            $address2 ?: null,
            $city,
            $postal_code,
            $instructions ?: null
        ]);
        
        $order_id = $conn->lastInsertId();
        
        // Add order items and update stock
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $update_stock = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ? AND quantity >= ?");
        
        foreach ($cart_items as $item) {
            // Check stock availability
            if ($item['stock'] < $item['qty']) {
                $conn->rollBack();
                die(json_encode(['success' => false, 'message' => "Insufficient stock for {$item['name']}"]));
            }
            
            // Add order item
            $stmt->execute([
                $order_id,
                $item['product_id'],
                $item['qty'],
                $item['price']
            ]);
            
            // Update product stock
            $result = $update_stock->execute([$item['qty'], $item['product_id'], $item['qty']]);
            
            if ($update_stock->rowCount() === 0) {
                $conn->rollBack();
                die(json_encode(['success' => false, 'message' => "Failed to update stock for {$item['name']}"]));
            }
        }
        
        // Clear cart
        $stmt = $conn->prepare("DELETE FROM card WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Order placed successfully!',
            'order_id' => $order_id
        ]);
        
    } catch (Exception $e) {
        $conn->rollBack();
        error_log($e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to process order. Please try again.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?> 