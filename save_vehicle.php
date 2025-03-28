<?php
session_start();
require_once 'config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to save vehicles']);
    exit;
}

// Check for required parameters
if (!isset($_POST['vehicle_id']) || !isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$user_id = $_SESSION['user_id'];
$vehicle_id = intval($_POST['vehicle_id']);
$action = $_POST['action'];

// Validate vehicle_id
if ($vehicle_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid vehicle ID']);
    exit;
}

// Check if vehicle exists
$vehicleCheck = "SELECT id FROM vehicles WHERE id = ?";
$checkStmt = $conn->prepare($vehicleCheck);
$checkStmt->bind_param("i", $vehicle_id);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Vehicle not found']);
    $checkStmt->close();
    exit;
}
$checkStmt->close();

if ($action === 'add') {
    // Add vehicle to saved list
    $query = "INSERT INTO saved_vehicles (user_id, vehicle_id) VALUES (?, ?) 
              ON DUPLICATE KEY UPDATE saved_at = CURRENT_TIMESTAMP";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $vehicle_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Vehicle saved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error saving vehicle: ' . $conn->error]);
    }
    $stmt->close();
} else if ($action === 'remove') {
    // Remove vehicle from saved list
    $query = "DELETE FROM saved_vehicles WHERE user_id = ? AND vehicle_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $vehicle_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Vehicle removed from saved list']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error removing vehicle: ' . $conn->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>