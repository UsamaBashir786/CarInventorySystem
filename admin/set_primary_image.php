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

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Get the image ID and vehicle ID
  $image_id = isset($_POST['image_id']) ? intval($_POST['image_id']) : 0;
  $vehicle_id = isset($_POST['vehicle_id']) ? intval($_POST['vehicle_id']) : 0;

  // Validate input
  if ($image_id <= 0 || $vehicle_id <= 0) {
    $_SESSION['errors'] = ["Invalid image or vehicle ID"];
    header("Location: vehicle_details.php?id=" . $vehicle_id . "#images");
    exit;
  }

  // Get database connection
  $conn = getConnection();

  // Start transaction
  $conn->begin_transaction();

  try {
    // First, reset all images for this vehicle to not be primary
    $reset_stmt = $conn->prepare("UPDATE vehicle_images SET is_primary = 0 WHERE vehicle_id = ?");
    $reset_stmt->bind_param("i", $vehicle_id);
    $reset_stmt->execute();
    $reset_stmt->close();

    // Then, set the selected image as primary
    $update_stmt = $conn->prepare("UPDATE vehicle_images SET is_primary = 1 WHERE id = ? AND vehicle_id = ?");
    $update_stmt->bind_param("ii", $image_id, $vehicle_id);
    $update_stmt->execute();
    
    // Check if the update was successful
    if ($update_stmt->affected_rows === 0) {
      throw new Exception("Failed to set primary image. Image might not exist or doesn't belong to this vehicle.");
    }
    
    $update_stmt->close();

    // Commit the transaction
    $conn->commit();
    
    $_SESSION['success'] = "Primary image updated successfully.";
  } catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    $_SESSION['errors'] = ["Error: " . $e->getMessage()];
  }

  // Close the connection
  $conn->close();

  // Redirect back to the vehicle details page
  header("Location: vehicle_details.php?id=" . $vehicle_id . "#images");
  exit;
} else {
  // If accessed directly without POST data, redirect to vehicles page
  header("Location: vehicles.php");
  exit;
}