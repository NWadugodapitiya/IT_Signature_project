<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Please login first']));
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = $_SESSION['user_id'];
    
    switch ($action) {
        case 'add':
            $product_id = intval($_POST['product_id']);
            $qty = intval($_POST['qty']);
            
    // Check if product exists and has enough stock
            $stmt = $conn->prepare("SELECT quantity, price FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
            
            if (!$product) {
                echo json_encode(['success' => false, 'message' => 'Product not found']);
                exit;
            }
            
            if ($product['quantity'] < $qty) {
                echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
                exit;
            }
            
         // Check if product already in user's cart
            $stmt = $conn->prepare("SELECT id, qty FROM card WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user_id, $product_id]);
            $cart_item = $stmt->fetch();
            
            try {
                $conn->beginTransaction();
                
                if ($cart_item) {
                // Update quantity if already in cart
                    $new_qty = $cart_item['qty'] + $qty;
                    if ($new_qty > $product['quantity']) {
                        $conn->rollBack();
                        echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
                        exit;
                    }
                    
                    $stmt = $conn->prepare("UPDATE card SET qty = ?, datetime = NOW() WHERE id = ? AND user_id = ?");
                    $stmt->execute([$new_qty, $cart_item['id'], $user_id]);
                } else {
                    // Add new item to cart
                    $stmt = $conn->prepare("INSERT INTO card (user_id, product_id, qty, datetime) VALUES (?, ?, ?, NOW())");
                    $stmt->execute([$user_id, $product_id, $qty]);
                }
                
                // Update product quantity
                $stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ? AND quantity >= ?");
                $stmt->execute([$qty, $product_id, $qty]);
                
                if ($stmt->rowCount() === 0) {
                    $conn->rollBack();
                    echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
                    exit;
                }
                
                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Product added to cart']);
            } catch (Exception $e) {
                $conn->rollBack();
                echo json_encode(['success' => false, 'message' => 'Database error']);
            }
            break;
            
        case 'update':
            $cart_id = intval($_POST['cart_id']);
            $qty = intval($_POST['qty']);
            
            // Get current cart item details
            $stmt = $conn->prepare("
                SELECT c.qty as current_qty, p.quantity as stock, p.id as product_id 
                FROM card c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.id = ? AND c.user_id = ?
            ");
            $stmt->execute([$cart_id, $user_id]);
            $item = $stmt->fetch();
            
            if (!$item) {
                echo json_encode(['success' => false, 'message' => 'Cart item not found']);
                exit;
            }
            
            try {
                $conn->beginTransaction();
                
                if ($qty <= 0) {
                    // Remove item from cart
                    $stmt = $conn->prepare("DELETE FROM card WHERE id = ? AND user_id = ?");
                    $stmt->execute([$cart_id, $user_id]);
                    
                    // Return quantity to product stock
                    $stmt = $conn->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?");
                    $stmt->execute([$item['current_qty'], $item['product_id']]);
                } else {
                    $qty_difference = $qty - $item['current_qty'];
                    
                    if ($qty_difference > 0) {
                        // Check if enough stock available
                        if ($qty_difference > $item['stock']) {
                            $conn->rollBack();
                            echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
                            exit;
                        }
                        
                        // Decrease product stock
                        $stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ? AND quantity >= ?");
                        $stmt->execute([$qty_difference, $item['product_id'], $qty_difference]);
                        
                        if ($stmt->rowCount() === 0) {
                            $conn->rollBack();
                            echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
                            exit;
                        }
                    } else {
                        // Return excess quantity to product stock
                        $stmt = $conn->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?");
                        $stmt->execute([abs($qty_difference), $item['product_id']]);
                    }
                    
                    // Update cart quantity
                    $stmt = $conn->prepare("UPDATE card SET qty = ?, datetime = NOW() WHERE id = ? AND user_id = ?");
                    $stmt->execute([$qty, $cart_id, $user_id]);
                }
                
                $conn->commit();
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                $conn->rollBack();
                echo json_encode(['success' => false, 'message' => 'Database error']);
            }
            break;
            
        case 'remove':
            $cart_id = intval($_POST['cart_id']);
            
            // Get cart item details
            $stmt = $conn->prepare("
                SELECT c.qty, p.id as product_id 
                FROM card c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.id = ? AND c.user_id = ?
            ");
            $stmt->execute([$cart_id, $user_id]);
            $item = $stmt->fetch();
            
            if (!$item) {
                echo json_encode(['success' => false, 'message' => 'Cart item not found']);
                exit;
            }
            
            try {
                $conn->beginTransaction();
                
                // Remove from cart
                $stmt = $conn->prepare("DELETE FROM card WHERE id = ? AND user_id = ?");
                $stmt->execute([$cart_id, $user_id]);
                
                // Return quantity to product stock
                $stmt = $conn->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?");
                $stmt->execute([$item['qty'], $item['product_id']]);
                
                $conn->commit();
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                $conn->rollBack();
                echo json_encode(['success' => false, 'message' => 'Database error']);
            }
            break;
            
        case 'get_cart':
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
                ORDER BY c.datetime DESC
            ");
            $stmt->execute([$user_id]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'items' => $items
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?> 