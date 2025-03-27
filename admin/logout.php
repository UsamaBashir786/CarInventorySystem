<?php
// Initialize the session
session_start();

// Include database connection
require_once "../config/db.php";

// Get admin ID before session destruction
$admin_id = isset($_SESSION["admin_id"]) ? $_SESSION["admin_id"] : null;

// Log the logout activity if admin ID is available
if ($admin_id) {
  $activity_sql = "INSERT INTO admin_activity_logs (admin_id, activity_type, ip_address, user_agent) 
                   VALUES (?, 'logout', ?, ?)";
  if ($activity_stmt = $conn->prepare($activity_sql)) {
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $activity_stmt->bind_param("iss", $admin_id, $ip_address, $user_agent);
    $activity_stmt->execute();
    $activity_stmt->close();
  }

  // If the admin has a remember token, delete it
  if (isset($_COOKIE["admin_remember_token"])) {
    $token = $_COOKIE["admin_remember_token"];

    // Delete the token from database
    $stmt = $conn->prepare("DELETE FROM admin_tokens WHERE admin_id = ? AND token = ?");
    $stmt->bind_param("is", $admin_id, $token);
    $stmt->execute();
    $stmt->close();

    // Delete the cookie by setting it to expire in the past
    setcookie("admin_remember_token", "", time() - 3600, "/");
  }
}

// Unset all of the session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Close the database connection
$conn->close();

// Redirect to login page with success message
header("location: login.php?logout=success");
exit;
