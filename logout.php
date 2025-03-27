<?php
// Initialize the session
session_start();

// Include database connection
require_once "config/db.php";

// If the user is logged in and has a remember token, delete it
if (isset($_SESSION["user_id"]) && isset($_COOKIE["remember_token"])) {
  $token = $_COOKIE["remember_token"];

  // Delete the token from database
  $stmt = $conn->prepare("DELETE FROM user_tokens WHERE user_id = ? AND token = ?");
  $stmt->bind_param("is", $_SESSION["user_id"], $token);
  $stmt->execute();
  $stmt->close();

  // Delete the cookie by setting it to expire in the past
  setcookie("remember_token", "", time() - 3600, "/");
}

// Log the user session out if it exists
if (isset($_SESSION["user_id"])) {
  $session_id = session_id();

  $stmt = $conn->prepare("DELETE FROM user_sessions WHERE user_id = ? AND session_id = ?");
  $stmt->bind_param("is", $_SESSION["user_id"], $session_id);
  $stmt->execute();
  $stmt->close();
}

// Unset all of the session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page
header("location: login.php");
exit;
