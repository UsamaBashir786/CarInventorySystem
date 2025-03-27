<?php
session_start();
// Check if admin is logged in
if (!isset($_SESSION["admin_id"])) {
  header("location: login.php");
  exit;
}

// Include database connection
require_once '../config/db.php';

// Function to get database connection if not already defined
if (!function_exists('getConnection')) {
  function getConnection() {
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

// Get make_id from request
$makeId = isset($_GET['make_id']) ? intval($_GET['make_id']) : 0;

if ($makeId > 0) {
  $conn = getConnection();
  
  // Prepare SQL to get models for this make
  $stmt = $conn->prepare("SELECT id, name FROM models WHERE make_id = ? ORDER BY display_order, name");
  
  if (!$stmt) {
    echo json_encode(['error' => 'Failed to prepare statement: ' . $conn->error]);
    exit;
  }
  
  $stmt->bind_param("i", $makeId);
  $success = $stmt->execute();
  
  if (!$success) {
    echo json_encode(['error' => 'Failed to execute query: ' . $stmt->error]);
    exit;
  }
  
  $result = $stmt->get_result();
  $models = [];
  
  while ($row = $result->fetch_assoc()) {
    $models[] = $row;
  }
  
  $stmt->close();
  $conn->close();
  
  echo json_encode(['models' => $models]);
} else {
  echo json_encode(['error' => 'Invalid make ID']);
}