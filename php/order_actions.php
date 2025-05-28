<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Admin') {
    die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'get_order_items':
            $order_id = intval($_POST['order_id']);
            
            try {
                $stmt = $conn->prepare("
                    SELECT 
                        oi.quantity,
                        oi.price,
                        p.name as product_name
                    FROM order_items oi
                    JOIN products p ON oi.product_id = p.id
                    WHERE oi.order_id = ?
                ");
                $stmt->execute([$order_id]);
                $items = $stmt->fetchAll();
                
                echo json_encode(['success' => true, 'items' => $items]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Failed to fetch order items']);
            }
            break;
            
        case 'update_status':
            $order_id = intval($_POST['order_id']);
            $status = $_POST['status'];
            
            // Validate status
            $valid_statuses = ['pending', 'processing', 'completed', 'cancelled'];
            if (!in_array($status, $valid_statuses)) {
                die(json_encode(['success' => false, 'message' => 'Invalid status']));
            }
            
            try {
                $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
                $stmt->execute([$status, $order_id]);
                
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true, 'message' => 'Order status updated successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Order not found']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Failed to update order status']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?> 