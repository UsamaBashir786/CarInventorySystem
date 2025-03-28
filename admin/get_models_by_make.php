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

// Check if make_id is provided
if (!isset($_GET['make_id']) || empty($_GET['make_id'])) {
  header('Content-Type: application/json');
  echo json_encode(['error' => 'Make ID is required']);
  exit;
}

// Get make ID from request
$make_id = intval($_GET['make_id']);

// Get connection
$conn = getConnection();

// Prepare and execute query to get models for the specified make
$stmt = $conn->prepare("SELECT id, name FROM models WHERE make_id = ? ORDER BY display_order, name");
$stmt->bind_param("i", $make_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch all models
$models = [];
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $models[] = $row;
  }
}

// Close connection
$stmt->close();
$conn->close();

// Return models as JSON
header('Content-Type: application/json');
echo json_encode($models);
exit;
?>