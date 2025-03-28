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

// Get vehicle ID and new status from request
$vehicle_id = isset($_POST['vehicle_id']) ? intval($_POST['vehicle_id']) : 0;
$status_id = isset($_POST['status_id']) ? intval($_POST['status_id']) : 0;

if ($vehicle_id <= 0 || $status_id <= 0) {
  header('Content-Type: application/json');
  echo json_encode(['error' => 'Invalid vehicle ID or status ID']);
  exit;
}

// Get connection
$conn = getConnection();

// Verify status ID exists
$status_check = $conn->prepare("SELECT id FROM vehicle_status WHERE id = ?");
$status_check->bind_param("i", $status_id);
$status_check->execute();
$status_result = $status_check->get_result();

if ($status_result->num_rows === 0) {
  header('Content-Type: application/json');
  echo json_encode(['error' => 'Invalid status ID']);
  $status_check->close();
  $conn->close();
  exit;
}
$status_check->close();

// Update vehicle status
$update_stmt = $conn->prepare("UPDATE vehicles SET status_id = ?, updated_by = ?, updated_at = NOW() WHERE id = ?");
$admin_id = $_SESSION["admin_id"];
$update_stmt->bind_param("iii", $status_id, $admin_id, $vehicle_id);
$result = $update_stmt->execute();

if ($result && $update_stmt->affected_rows > 0) {
  // Get the updated status information for the response
  $status_info = $conn->prepare("SELECT vs.name, vs.css_class FROM vehicle_status vs WHERE vs.id = ?");
  $status_info->bind_param("i", $status_id);
  $status_info->execute();
  $status_info_result = $status_info->get_result();
  $status_data = $status_info_result->fetch_assoc();
  $status_info->close();

  header('Content-Type: application/json');
  echo json_encode([
    'success' => true,
    'message' => 'Vehicle status updated successfully',
    'status' => [
      'id' => $status_id,
      'name' => $status_data['name'],
      'css_class' => $status_data['css_class']
    ]
  ]);
} else {
  header('Content-Type: application/json');
  echo json_encode([
    'error' => 'Failed to update vehicle status',
    'details' => $update_stmt->error
  ]);
}

$update_stmt->close();
$conn->close();
exit;
