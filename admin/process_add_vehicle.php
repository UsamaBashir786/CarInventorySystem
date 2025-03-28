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
  $conn = getConnection();

  // Process the form data and get all values
  // Make
  $makeId = isset($_POST['make']) ? intval($_POST['make']) : 0;
  $make = '';
  if ($makeId > 0) {
    $makeStmt = $conn->prepare("SELECT name FROM makes WHERE id = ?");
    $makeStmt->bind_param("i", $makeId);
    $makeStmt->execute();
    $makeResult = $makeStmt->get_result();
    if ($makeResult->num_rows > 0) {
      $makeRow = $makeResult->fetch_assoc();
      $make = $makeRow['name'];
    }
    $makeStmt->close();
  }

  // Model
  $modelId = isset($_POST['model']) ? intval($_POST['model']) : 0;
  $model = '';
  if ($modelId > 0) {
    $modelStmt = $conn->prepare("SELECT name FROM models WHERE id = ?");
    $modelStmt->bind_param("i", $modelId);
    $modelStmt->execute();
    $modelResult = $modelStmt->get_result();
    if ($modelResult->num_rows > 0) {
      $modelRow = $modelResult->fetch_assoc();
      $model = $modelRow['name'];
    }
    $modelStmt->close();
  }

  // Other fields
  $year = isset($_POST['year']) ? intval($_POST['year']) : 0;
  $price = isset($_POST['price']) ? floatval(str_replace(',', '', $_POST['price'])) : 0;
  $mileage = isset($_POST['mileage']) ? intval(str_replace(',', '', $_POST['mileage'])) : 0;
  $vin = isset($_POST['vin']) ? trim($_POST['vin']) : '';
  $condition = 'New'; // Default condition
  
  // Body style
  $bodyStyleId = isset($_POST['body_style']) ? intval($_POST['body_style']) : 0;
  $bodyStyle = '';
  if ($bodyStyleId > 0) {
    $styleStmt = $conn->prepare("SELECT name FROM body_types WHERE id = ?");
    $styleStmt->bind_param("i", $bodyStyleId);
    $styleStmt->execute();
    $styleResult = $styleStmt->get_result();
    if ($styleResult->num_rows > 0) {
      $styleRow = $styleResult->fetch_assoc();
      $bodyStyle = $styleRow['name'];
    }
    $styleStmt->close();
  }

  // Fuel type
  $fuelTypeId = isset($_POST['fuel_type']) ? intval($_POST['fuel_type']) : 0;
  $fuelType = '';
  if ($fuelTypeId > 0) {
    $fuelStmt = $conn->prepare("SELECT name FROM fuel_types WHERE id = ?");
    $fuelStmt->bind_param("i", $fuelTypeId);
    $fuelStmt->execute();
    $fuelResult = $fuelStmt->get_result();
    if ($fuelResult->num_rows > 0) {
      $fuelRow = $fuelResult->fetch_assoc();
      $fuelType = $fuelRow['name'];
    }
    $fuelStmt->close();
  }

  // Transmission
  $transmissionId = isset($_POST['transmission']) ? intval($_POST['transmission']) : 0;
  $transmission = '';
  if ($transmissionId > 0) {
    $transStmt = $conn->prepare("SELECT name FROM transmission_types WHERE id = ?");
    $transStmt->bind_param("i", $transmissionId);
    $transStmt->execute();
    $transResult = $transStmt->get_result();
    if ($transResult->num_rows > 0) {
      $transRow = $transResult->fetch_assoc();
      $transmission = $transRow['name'];
    }
    $transStmt->close();
  }

  // Drivetrain
  $drivetrainId = isset($_POST['drivetrain']) ? intval($_POST['drivetrain']) : 0;
  $drivetrain = '';
  if ($drivetrainId > 0) {
    $driveStmt = $conn->prepare("SELECT name FROM drive_types WHERE id = ?");
    $driveStmt->bind_param("i", $drivetrainId);
    $driveStmt->execute();
    $driveResult = $driveStmt->get_result();
    if ($driveResult->num_rows > 0) {
      $driveRow = $driveResult->fetch_assoc();
      $drivetrain = $driveRow['name'];
    }
    $driveStmt->close();
  }

  // Colors
  $exteriorColorId = isset($_POST['exterior_color']) ? intval($_POST['exterior_color']) : 0;
  $exteriorColor = '';
  if ($exteriorColorId > 0) {
    $extColorStmt = $conn->prepare("SELECT name FROM colors WHERE id = ?");
    $extColorStmt->bind_param("i", $exteriorColorId);
    $extColorStmt->execute();
    $extColorResult = $extColorStmt->get_result();
    if ($extColorResult->num_rows > 0) {
      $extColorRow = $extColorResult->fetch_assoc();
      $exteriorColor = $extColorRow['name'];
    }
    $extColorStmt->close();
  }

  $interiorColorId = isset($_POST['interior_color']) ? intval($_POST['interior_color']) : 0;
  $interiorColor = '';
  if ($interiorColorId > 0) {
    $intColorStmt = $conn->prepare("SELECT name FROM colors WHERE id = ?");
    $intColorStmt->bind_param("i", $interiorColorId);
    $intColorStmt->execute();
    $intColorResult = $intColorStmt->get_result();
    if ($intColorResult->num_rows > 0) {
      $intColorRow = $intColorResult->fetch_assoc();
      $interiorColor = $intColorRow['name'];
    }
    $intColorStmt->close();
  }

  // Status
  $statusId = isset($_POST['status']) ? intval($_POST['status']) : 0;
  $status = 'available'; // Default status
  if ($statusId > 0) {
    $statusStmt = $conn->prepare("SELECT name FROM vehicle_status WHERE id = ?");
    $statusStmt->bind_param("i", $statusId);
    $statusStmt->execute();
    $statusResult = $statusStmt->get_result();
    if ($statusResult->num_rows > 0) {
      $statusRow = $statusResult->fetch_assoc();
      $status = $statusRow['name'];
    }
    $statusStmt->close();
  }

  // Other fields
  $engine = isset($_POST['engine']) ? trim($_POST['engine']) : '';
  $description = isset($_POST['description']) ? trim($_POST['description']) : '';

  // Default color
  $color = $exteriorColor ?: 'Unknown';

  // Very simple approach - direct query without prepared statement to debug
  $query = "INSERT INTO vehicles (
    make, model, year, price, mileage, color, vin, `condition`, body_style, 
    transmission, fuel_type, engine, drivetrain, exterior_color, interior_color, 
    description, status, created_at
  ) VALUES (
    '$make', '$model', $year, $price, $mileage, '$color', '$vin', '$condition', '$bodyStyle',
    '$transmission', '$fuelType', '$engine', '$drivetrain', '$exteriorColor', '$interiorColor',
    '$description', '$status', NOW()
  )";

  // Execute the query
  $result = $conn->query($query);

  if ($result) {
    $vehicleId = $conn->insert_id;

    // Handle image uploads if any
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
      $uploadDir = '../uploads/vehicles/';

      // Create directory if it doesn't exist
      if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
      }

      // Process each uploaded file
      $fileCount = count($_FILES['images']['name']);

      for ($i = 0; $i < $fileCount; $i++) {
        if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
          $tmpName = $_FILES['images']['tmp_name'][$i];
          $fileName = basename($_FILES['images']['name'][$i]);
          $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

          // Only allow certain file types
          $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

          if (in_array($fileExt, $allowedExt)) {
            // Generate a unique filename
            $newFileName = 'vehicle_' . $vehicleId . '_' . uniqid() . '.' . $fileExt;
            $destination = $uploadDir . $newFileName;

            // Move the file to the upload directory
            if (move_uploaded_file($tmpName, $destination)) {
              // Save image record to database
              $imgPath = 'uploads/vehicles/' . $newFileName;
              $imgSql = "INSERT INTO vehicle_images (vehicle_id, image_path, is_primary) VALUES (?, ?, ?)";
              $imgStmt = $conn->prepare($imgSql);

              // First image is primary
              $isPrimary = ($i === 0) ? 1 : 0;

              $imgStmt->bind_param("isi", $vehicleId, $imgPath, $isPrimary);
              $imgStmt->execute();
              $imgStmt->close();
            }
          }
        }
      }
    }

    $_SESSION['success'] = "Vehicle added successfully";
  } else {
    $_SESSION['error'] = "Error adding vehicle: " . $conn->error;
  }

  $conn->close();

  // Redirect back to the dashboard
  header("Location: index.php");
  exit;
} else {
  // If accessed directly without form submission
  header("Location: index.php");
  exit;
}