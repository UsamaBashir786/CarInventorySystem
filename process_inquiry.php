<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log function to help with debugging
function logError($message) {
    error_log("[" . date('Y-m-d H:i:s') . "] " . $message . "\n", 3, "inquiry_error.log");
}

// Include database connection
require_once 'config/db.php';

// Set headers for JSON response
header('Content-Type: application/json');

try {
    // Check if the request is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
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
        throw new Exception('Missing required fields: ' . implode(', ', $missing_fields));
    }

    // Sanitize input data
    $vehicle_id = filter_var($_POST['vehicle_id'], FILTER_SANITIZE_NUMBER_INT);
    $full_name = htmlspecialchars(trim($_POST['full_name']));
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars(trim($_POST['phone']));
    $contact_method = htmlspecialchars(trim($_POST['contact_method']));
    $message = isset($_POST['message']) ? htmlspecialchars(trim($_POST['message'])) : '';
    $terms_agreed = isset($_POST['terms_agreed']) && $_POST['terms_agreed'] == 1 ? 1 : 0;

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address');
    }

    // Validate vehicle ID
    if (!is_numeric($vehicle_id) || $vehicle_id <= 0) {
        throw new Exception('Invalid vehicle ID');
    }

    // Log incoming data for debugging
    logError("Processing inquiry for vehicle ID: $vehicle_id, Email: $email, Contact method: $contact_method");

    // Get user ID if logged in
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    // Check database connection
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Check if vehicle_inquiries table exists
    $tableCheckQuery = "SHOW TABLES LIKE 'vehicle_inquiries'";
    $tableResult = $conn->query($tableCheckQuery);
    
    if ($tableResult->num_rows == 0) {
        // Create table if it doesn't exist
        $createTableSQL = "CREATE TABLE IF NOT EXISTS `vehicle_inquiries` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `vehicle_id` int(11) NOT NULL,
            `user_id` int(11) DEFAULT NULL,
            `full_name` varchar(100) NOT NULL,
            `email` varchar(100) NOT NULL,
            `phone` varchar(20) NOT NULL,
            `contact_method` enum('email','phone','text') NOT NULL,
            `message` text DEFAULT NULL,
            `terms_agreed` tinyint(1) NOT NULL DEFAULT 0,
            `status` enum('New','In Progress','Contacted','Closed') NOT NULL DEFAULT 'New',
            `submitted_at` datetime NOT NULL,
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `vehicle_id` (`vehicle_id`),
            KEY `user_id` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $conn->query($createTableSQL);
        logError("Created vehicle_inquiries table");
    }

    // Get the current timestamp
    $submitted_at = date('Y-m-d H:i:s');

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
    if (!$stmt) {
        throw new Exception('Prepare statement failed: ' . $conn->error);
    }

    // Bind parameters
    $bindResult = $stmt->bind_param(
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

    if (!$bindResult) {
        throw new Exception('Parameter binding failed: ' . $stmt->error);
    }

    // Execute the statement
    $result = $stmt->execute();
    if (!$result) {
        throw new Exception('Execute statement failed: ' . $stmt->error);
    }

    $inquiry_id = $stmt->insert_id;
    $stmt->close();

    // Get vehicle details for confirmation
    $vehicle_query = "SELECT year, make, model FROM vehicles WHERE id = ?";
    $vehicle_stmt = $conn->prepare($vehicle_query);
    if (!$vehicle_stmt) {
        throw new Exception('Prepare vehicle statement failed: ' . $conn->error);
    }

    $vehicle_stmt->bind_param("i", $vehicle_id);
    $vehicle_stmt->execute();
    $vehicle_result = $vehicle_stmt->get_result();
    $vehicle_data = $vehicle_result->fetch_assoc();
    $vehicle_stmt->close();

    // Send success response
    echo json_encode([
        'success' => true,
        'message' => 'Your inquiry has been submitted successfully.',
        'inquiry_id' => $inquiry_id,
        'vehicle' => $vehicle_data
    ]);

    // Log success
    logError("Successfully added inquiry #$inquiry_id for vehicle #$vehicle_id");

} catch (Exception $e) {
    // Log the exception and return error response
    logError("Error in process_inquiry.php: " . $e->getMessage() . " - Line: " . $e->getLine());
    
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'details' => 'Please try again or contact support.'
    ]);
}

// Close the database connection if open
if (isset($conn)) {
    $conn->close();
}
?>