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

// Check if vehicle ID is provided and is numeric
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['vehicle_id']) && is_numeric($_POST['vehicle_id'])) {
  $vehicleId = (int)$_POST['vehicle_id'];

  // Get database connection
  $conn = getConnection();

  // Start transaction for safer deletion
  $conn->begin_transaction();

  try {
    // First, get vehicle details for confirmation message
    $stmt = $conn->prepare("SELECT CONCAT(year, ' ', make, ' ', model) AS vehicle_name FROM vehicles WHERE id = ?");
    $stmt->bind_param("i", $vehicleId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
      // Vehicle not found
      throw new Exception("Vehicle not found.");
    }

    $vehicleData = $result->fetch_assoc();
    $vehicleName = $vehicleData['vehicle_name'];
    $stmt->close();

    // Check for related records in other tables that might cause constraints
    // This is an example - modify according to your actual database structure
    $tables = [
      'vehicle_images' => 'vehicle_id',
      'vehicle_features' => 'vehicle_id',
      'maintenance_records' => 'vehicle_id',
      // Add any other tables that have foreign key constraints
    ];

    foreach ($tables as $table => $foreignKey) {
      // Check if table exists first to avoid errors
      $tableCheck = $conn->query("SHOW TABLES LIKE '$table'");
      if ($tableCheck->num_rows > 0) {
        // Delete related records
        $stmt = $conn->prepare("DELETE FROM $table WHERE $foreignKey = ?");
        $stmt->bind_param("i", $vehicleId);
        $stmt->execute();
        $stmt->close();
      }
    }

    // Now delete the vehicle
    $stmt = $conn->prepare("DELETE FROM vehicles WHERE id = ?");
    $stmt->bind_param("i", $vehicleId);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
      // No rows affected, likely vehicle doesn't exist
      throw new Exception("Failed to delete vehicle. Vehicle may not exist.");
    }

    // Commit the transaction if everything went well
    $conn->commit();

    // Set success message
    $_SESSION['success'] = "Vehicle '{$vehicleName}' has been successfully deleted.";

    $stmt->close();
    $conn->close();

    // Redirect back to the vehicles page
    header("Location: index.php");
    exit;
  } catch (Exception $e) {
    // Roll back the transaction if something failed
    $conn->rollback();

    // Set error message
    $_SESSION['error'] = "Error deleting vehicle: " . $e->getMessage();

    $conn->close();

    // Redirect back to the vehicles page
    header("Location: index.php");
    exit;
  }
} else {
  // Invalid request
  $_SESSION['error'] = "Invalid request. Please try again.";
  header("Location: index.php");
  exit;
}
