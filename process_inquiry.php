<?php
// Include database connection
require_once 'config/db.php';

// Set header to return JSON
header('Content-Type: application/json');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Invalid request method']);
  exit;
}

// Get form data
$vehicle_id = isset($_POST['vehicle_id']) ? intval($_POST['vehicle_id']) : 0;
$full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$contact_method = isset($_POST['contact_method']) ? trim($_POST['contact_method']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';
$terms_agreed = isset($_POST['terms_agreed']) ? 1 : 0;
$ip_address = $_SERVER['REMOTE_ADDR'];

// Validate required fields
if (empty($vehicle_id) || empty($full_name) || empty($email) || empty($phone) || empty($contact_method) || !$terms_agreed) {
  echo json_encode(['success' => false, 'message' => 'Missing required fields']);
  exit;
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  echo json_encode(['success' => false, 'message' => 'Invalid email address']);
  exit;
}

// Prepare and execute query
$query = "INSERT INTO vehicle_inquiries (vehicle_id, full_name, email, phone, contact_method, message, terms_agreed, ip_address) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($query);
$stmt->bind_param("isssssss", $vehicle_id, $full_name, $email, $phone, $contact_method, $message, $terms_agreed, $ip_address);

if ($stmt->execute()) {
  // Send notification email to admin (optional)
  $admin_email = "admin@centralautogy.com"; // Change this to your admin email
  $subject = "New Vehicle Inquiry - #" . $vehicle_id;
  $email_message = "New inquiry received for vehicle #$vehicle_id\n\n";
  $email_message .= "Name: $full_name\n";
  $email_message .= "Email: $email\n";
  $email_message .= "Phone: $phone\n";
  $email_message .= "Preferred Contact: $contact_method\n";
  $email_message .= "Message: $message\n";

  // Uncomment the line below to enable email notifications
  // mail($admin_email, $subject, $email_message);

  echo json_encode(['success' => true, 'message' => 'Inquiry submitted successfully']);
} else {
  echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
