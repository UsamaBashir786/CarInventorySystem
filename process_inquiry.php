<?php
session_start();
// Include database connection
require_once 'config/db.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Invalid request method']);
  exit;
}

// Check required fields
$required_fields = ['vehicle_id', 'full_name', 'email', 'phone', 'contact_method'];
$missing_fields = [];

foreach ($required_fields as $field) {
  if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
    $missing_fields[] = $field;
  }
}

if (!empty($missing_fields)) {
  echo json_encode(['success' => false, 'message' => 'Missing required fields: ' . implode(', ', $missing_fields)]);
  exit;
}

// Sanitize input data
$vehicle_id = filter_var($_POST['vehicle_id'], FILTER_SANITIZE_NUMBER_INT);
$full_name = filter_var($_POST['full_name'], FILTER_SANITIZE_STRING);
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
$contact_method = filter_var($_POST['contact_method'], FILTER_SANITIZE_STRING);
$message = isset($_POST['message']) ? filter_var($_POST['message'], FILTER_SANITIZE_STRING) : '';
$terms_agreed = isset($_POST['terms_agreed']) && $_POST['terms_agreed'] == 1 ? 1 : 0;

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  echo json_encode(['success' => false, 'message' => 'Invalid email address']);
  exit;
}

// Validate vehicle ID
if (!is_numeric($vehicle_id) || $vehicle_id <= 0) {
  echo json_encode(['success' => false, 'message' => 'Invalid vehicle ID']);
  exit;
}

// Get user ID if logged in
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Get the current timestamp
$submitted_at = date('Y-m-d H:i:s');

try {
  // Prepare the insert statement
  $query = "INSERT INTO vehicle_inquiries (
                vehicle_id, 
                user_id, 
                full_name, 
                email, 
                phone, 
                contact_method, 
                message, 
                terms_agreed, 
                status, 
                submitted_at
              ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'New', ?)";

  $stmt = $conn->prepare($query);

  // Bind parameters
  $stmt->bind_param(
    "iisssssis",
    $vehicle_id,
    $user_id,
    $full_name,
    $email,
    $phone,
    $contact_method,
    $message,
    $terms_agreed,
    $submitted_at
  );

  // Execute the statement
  $result = $stmt->execute();

  if ($result) {
    // Get vehicle details for notification/email
    $vehicle_query = "SELECT year, make, model FROM vehicles WHERE id = ?";
    $vehicle_stmt = $conn->prepare($vehicle_query);
    $vehicle_stmt->bind_param("i", $vehicle_id);
    $vehicle_stmt->execute();
    $vehicle_result = $vehicle_stmt->get_result();
    $vehicle_data = $vehicle_result->fetch_assoc();

    // Here you would typically send an email notification to the dealership
    // and/or to the customer for confirmation (omitted in this example)

    // Return success response
    echo json_encode([
      'success' => true,
      'message' => 'Your inquiry has been submitted successfully.',
      'inquiry_id' => $stmt->insert_id
    ]);
  } else {
    // Log the error for debugging
    error_log("Database error in process_inquiry.php: " . $stmt->error);

    echo json_encode(['success' => false, 'message' => 'Failed to save your inquiry. Please try again.']);
  }

  $stmt->close();
} catch (Exception $e) {
  // Log the exception for debugging
  error_log("Exception in process_inquiry.php: " . $e->getMessage());

  echo json_encode(['success' => false, 'message' => 'An error occurred while processing your request.']);
}

// Close the database connection
$conn->close();
