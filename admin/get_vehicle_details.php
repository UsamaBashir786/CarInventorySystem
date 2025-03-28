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

// Check if vehicle_id is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
  header('Content-Type: application/json');
  echo json_encode(['error' => 'Vehicle ID is required']);
  exit;
}

// Get vehicle ID from request
$vehicle_id = intval($_GET['id']);

// Get connection
$conn = getConnection();

// Prepare and execute query to get vehicle details
$query = "SELECT 
    v.*,
    m1.name as make_name,
    m2.name as model_name,
    bt.name as body_type_name,
    ft.name as fuel_type_name,
    tt.name as transmission_name,
    dt.name as drivetrain_name,
    ec.name as exterior_color_name,
    ec.hex_code as exterior_color_hex,
    ic.name as interior_color_name,
    ic.hex_code as interior_color_hex,
    vs.name as status_name,
    vs.css_class as status_css,
    a.username as created_by_username,
    a2.username as updated_by_username
FROM 
    vehicles v
    LEFT JOIN makes m1 ON v.make_id = m1.id
    LEFT JOIN models m2 ON v.model_id = m2.id
    LEFT JOIN body_types bt ON v.body_type_id = bt.id
    LEFT JOIN fuel_types ft ON v.fuel_type_id = ft.id
    LEFT JOIN transmission_types tt ON v.transmission_id = tt.id
    LEFT JOIN drive_types dt ON v.drivetrain_id = dt.id
    LEFT JOIN colors ec ON v.exterior_color_id = ec.id
    LEFT JOIN colors ic ON v.interior_color_id = ic.id
    LEFT JOIN vehicle_status vs ON v.status_id = vs.id
    LEFT JOIN admins a ON v.created_by = a.id
    LEFT JOIN admins a2 ON v.updated_by = a2.id
WHERE 
    v.id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  header('Content-Type: application/json');
  echo json_encode(['error' => 'Vehicle not found']);
  exit;
}

$vehicle = $result->fetch_assoc();
$stmt->close();

// Get vehicle images
$images_query = "SELECT * FROM vehicle_images WHERE vehicle_id = ? ORDER BY display_order ASC";
$images_stmt = $conn->prepare($images_query);
$images_stmt->bind_param("i", $vehicle_id);
$images_stmt->execute();
$images_result = $images_stmt->get_result();

$images = [];
while ($image = $images_result->fetch_assoc()) {
  $images[] = $image;
}
$images_stmt->close();

// Add images to vehicle data
$vehicle['images'] = $images;

// Close connection
$conn->close();

// Return vehicle data as JSON
header('Content-Type: application/json');
echo json_encode($vehicle);
exit;
