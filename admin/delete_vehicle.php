<?php
session_start();
// Check if admin is logged in
if (!isset($_SESSION["admin_id"])) {
  header('Content-Type: application/json');
  echo json_encode(['error' => 'Unauthorized access']);
  exit;
}

// Include database connection
require_once '../config/db.php';

// Function to get database connection if not already defined
if (!function_exists('getConnection')) {
  function getConnection()
  {
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "centralautogy";

    $conn = new mysqli($host, $username, $password, $database);

    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
  }
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Content-Type: application/json');
  echo json_encode(['error' => 'Invalid request method']);
  exit;
}

// Get vehicle ID from request
$vehicle_id = isset($_POST['vehicle_id']) ? intval($_POST['vehicle_id']) : 0;

if ($vehicle_id <= 0) {
  header('Content-Type: application/json');
  echo json_encode(['error' => 'Invalid vehicle ID']);
  exit;
}

// Get connection
$conn = getConnection();

// Start transaction
$conn->begin_transaction();

try {
  // First, get image paths to delete files later
  $image_stmt = $conn->prepare("SELECT image_path FROM vehicle_images WHERE vehicle_id = ?");
  $image_stmt->bind_param("i", $vehicle_id);
  $image_stmt->execute();
  $image_result = $image_stmt->get_result();

  $image_paths = [];
  while ($row = $image_result->fetch_assoc()) {
    $image_paths[] = $row['image_path'];
  }
  $image_stmt->close();

  // Delete vehicle images from database
  $delete_images = $conn->prepare("DELETE FROM vehicle_images WHERE vehicle_id = ?");
  $delete_images->bind_param("i", $vehicle_id);
  $delete_images->execute();
  $delete_images->close();

  // Delete the vehicle
  $delete_vehicle = $conn->prepare("DELETE FROM vehicles WHERE id = ?");
  $delete_vehicle->bind_param("i", $vehicle_id);
  $result = $delete_vehicle->execute();
  $delete_vehicle->close();

  if ($result && $delete_vehicle->affected_rows > 0) {
    // Commit transaction
    $conn->commit();

    // Delete image files from server
    foreach ($image_paths as $path) {
      $full_path = "../" . $path;
      if (file_exists($full_path)) {
        unlink($full_path);
      }
    }

    // Try to remove the vehicle directory
    $vehicle_dir = "../uploads/vehicles/{$vehicle_id}";
    if (is_dir($vehicle_dir)) {
      rmdir($vehicle_dir);
    }

    $_SESSION['success'] = "Vehicle deleted successfully!";

    // Check if AJAX request
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
      header('Content-Type: application/json');
      echo json_encode(['success' => true]);
    } else {
      header("Location: vehicles.php");
    }
  } else {
    // If no rows affected, vehicle might not exist
    $conn->rollback();

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
      header('Content-Type: application/json');
      echo json_encode(['error' => 'Vehicle not found or already deleted']);
    } else {
      $_SESSION['errors'] = ["Vehicle not found or already deleted"];
      header("Location: vehicles.php");
    }
  }
} catch (Exception $e) {
  // Rollback transaction on error
  $conn->rollback();

  if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error deleting vehicle: ' . $e->getMessage()]);
  } else {
    $_SESSION['errors'] = ["Error deleting vehicle: " . $e->getMessage()];
    header("Location: vehicles.php");
  }
}

// Close connection
$conn->close();
exit;
