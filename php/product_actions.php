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
        case 'add':
            $name = trim($_POST['name']);
            $price = floatval($_POST['price']);
            $quantity = intval($_POST['quantity']);
            
            if (empty($name) || $price <= 0 || $quantity < 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid input']);
                exit;
            }
            
            try {
                $stmt = $conn->prepare("INSERT INTO products (name, price, quantity) VALUES (?, ?, ?)");
                $success = $stmt->execute([$name, $price, $quantity]);
                
                echo json_encode(['success' => $success]);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Database error']);
            }
            break;
            
        case 'delete':
            $id = intval($_POST['id']);
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
                exit;
            }
            
            try {
                $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
                $success = $stmt->execute([$id]);
                
                echo json_encode(['success' => $success]);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Database error']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?> 