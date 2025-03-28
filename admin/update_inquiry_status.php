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
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_inquiry_status'])) {
  $inquiry_id = isset($_POST['inquiry_id']) ? intval($_POST['inquiry_id']) : 0;
  $new_status = isset($_POST['new_status']) ? $_POST['new_status'] : '';
  $vehicle_id = isset($_POST['vehicle_id']) ? intval($_POST['vehicle_id']) : 0;

  // Validate input
  if ($inquiry_id <= 0 || empty($new_status) || $vehicle_id <= 0) {
    $_SESSION['errors'] = ["Invalid inquiry ID, status, or vehicle ID"];
    header("Location: vehicle_details.php?id=" . $vehicle_id . "#inquiries");
    exit;
  }

  // Validate status value
  $valid_statuses = ['New', 'In Progress', 'Contacted', 'Closed'];
  if (!in_array($new_status, $valid_statuses)) {
    $_SESSION['errors'] = ["Invalid status value"];
    header("Location: vehicle_details.php?id=" . $vehicle_id . "#inquiries");
    exit;
  }

  // Get database connection
  $conn = getConnection();

  // Update the inquiry status
  $update_query = "UPDATE vehicle_inquiries SET status = ? WHERE id = ? AND vehicle_id = ?";
  $update_stmt = $conn->prepare($update_query);
  $update_stmt->bind_param("sii", $new_status, $inquiry_id, $vehicle_id);

  if ($update_stmt->execute()) {
    // Log the status change
    $admin_id = $_SESSION["admin_id"];
    $log_query = "INSERT INTO admin_activity_logs (admin_id, activity_type, activity_details, ip_address, user_agent) 
                  VALUES (?, 'update_inquiry', ?, ?, ?)";

    $activity_details = "Updated inquiry #" . $inquiry_id . " status to " . $new_status;
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    $log_stmt = $conn->prepare($log_query);
    $log_stmt->bind_param("isss", $admin_id, $activity_details, $ip_address, $user_agent);
    $log_stmt->execute();
    $log_stmt->close();

    $_SESSION['success'] = "Inquiry status updated successfully.";
  } else {
    $_SESSION['errors'] = ["Failed to update inquiry status: " . $update_stmt->error];
  }

  $update_stmt->close();
  $conn->close();

  // Redirect back to the vehicle details page
  header("Location: vehicle_details.php?id=" . $vehicle_id . "#inquiries");
  exit;
} else {
  // If accessed directly without POST data, redirect to vehicles page
  header("Location: vehicles.php");
  exit;
}
