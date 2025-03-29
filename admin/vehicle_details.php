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

// First, check the structure of the vehicles table to determine column names
$conn = getConnection();
$table_structure_query = "DESCRIBE vehicles";
$structure_result = $conn->query($table_structure_query);
$columns = [];

while ($column = $structure_result->fetch_assoc()) {
  $columns[] = $column['Field'];
}

// Process form submission for updating vehicle
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['vehicle_id'])) {
  // Get vehicle ID
  $vehicle_id = intval($_POST['vehicle_id']);

  // Get form data with validation
  $make = isset($_POST['make']) ? intval($_POST['make']) : 0;
  $model = isset($_POST['model']) ? intval($_POST['model']) : 0;
  $year = isset($_POST['year']) ? intval($_POST['year']) : 0;
  $body_style = isset($_POST['body_style']) ? intval($_POST['body_style']) : 0;
  $mileage = isset($_POST['mileage']) ? filter_var($_POST['mileage'], FILTER_SANITIZE_NUMBER_INT) : 0;
  $price = (isset($_POST['price']) && trim($_POST['price']) !== '') ?
    filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) :
    null;
  $vin = isset($_POST['vin']) ? mysqli_real_escape_string($conn, trim($_POST['vin'])) : '';
  $fuel_type = isset($_POST['fuel_type']) ? intval($_POST['fuel_type']) : 0;
  $transmission = isset($_POST['transmission']) ? intval($_POST['transmission']) : 0;
  $drivetrain = isset($_POST['drivetrain']) ? intval($_POST['drivetrain']) : 0;
  $engine = isset($_POST['engine']) ? mysqli_real_escape_string($conn, trim($_POST['engine'])) : '';
  $exterior_color = isset($_POST['exterior_color']) ? intval($_POST['exterior_color']) : 0;
  $interior_color = isset($_POST['interior_color']) ? intval($_POST['interior_color']) : 0;
  $status = isset($_POST['status']) ? intval($_POST['status']) : 0;
  $description = isset($_POST['description']) ? mysqli_real_escape_string($conn, trim($_POST['description'])) : '';

  // Validate required fields
  $errors = [];

  if ($vehicle_id <= 0) {
    $errors[] = "Invalid vehicle ID";
  }

  if ($make <= 0) {
    $errors[] = "Make is required";
  }

  if ($model <= 0) {
    $errors[] = "Model is required";
  }

  if ($year < 1900 || $year > (date('Y') + 1)) {
    $errors[] = "Please enter a valid year";
  }

  if ($status <= 0) {
    $errors[] = "Status is required";
  }

  // If there are validation errors
  if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['form_data'] = $_POST; // Store form data for refilling the form
    header("Location: edit_vehicle_form.php?id=" . $vehicle_id);
    exit;
  }

  // Get make and model names from IDs
  $make_name = '';
  $model_name = '';

  // Get make name from ID
  $make_query = "SELECT name FROM makes WHERE id = ?";
  $make_stmt = $conn->prepare($make_query);
  $make_stmt->bind_param("i", $make);
  $make_stmt->execute();
  $make_result = $make_stmt->get_result();
  if ($make_row = $make_result->fetch_assoc()) {
    $make_name = $make_row['name'];
  }
  $make_stmt->close();

  // Get model name from ID
  $model_query = "SELECT name FROM models WHERE id = ?";
  $model_stmt = $conn->prepare($model_query);
  $model_stmt->bind_param("i", $model);
  $model_stmt->execute();
  $model_result = $model_stmt->get_result();
  if ($model_row = $model_result->fetch_assoc()) {
    $model_name = $model_row['name'];
  }
  $model_stmt->close();

  // Get body style name
  $body_style_name = '';
  if ($body_style > 0) {
    $body_query = "SELECT name FROM body_types WHERE id = ?";
    $body_stmt = $conn->prepare($body_query);
    $body_stmt->bind_param("i", $body_style);
    $body_stmt->execute();
    $body_result = $body_stmt->get_result();
    if ($body_row = $body_result->fetch_assoc()) {
      $body_style_name = $body_row['name'];
    }
    $body_stmt->close();
  }

  // Get transmission name
  $transmission_name = '';
  if ($transmission > 0) {
    $trans_query = "SELECT name FROM transmission_types WHERE id = ?";
    $trans_stmt = $conn->prepare($trans_query);
    $trans_stmt->bind_param("i", $transmission);
    $trans_stmt->execute();
    $trans_result = $trans_stmt->get_result();
    if ($trans_row = $trans_result->fetch_assoc()) {
      $transmission_name = $trans_row['name'];
    }
    $trans_stmt->close();
  }

  // Get fuel type name
  $fuel_type_name = '';
  if ($fuel_type > 0) {
    $fuel_query = "SELECT name FROM fuel_types WHERE id = ?";
    $fuel_stmt = $conn->prepare($fuel_query);
    $fuel_stmt->bind_param("i", $fuel_type);
    $fuel_stmt->execute();
    $fuel_result = $fuel_stmt->get_result();
    if ($fuel_row = $fuel_result->fetch_assoc()) {
      $fuel_type_name = $fuel_row['name'];
    }
    $fuel_stmt->close();
  }
  // Add this near the other data retrieval functions
  function getVehicleFeaturesWithDetails($vehicleId)
  {
    $conn = getConnection();
    $features = [];

    $query = "SELECT f.id, f.name, f.category 
            FROM features f 
            JOIN vehicle_features vf ON f.id = vf.feature_id 
            WHERE vf.vehicle_id = ? 
            ORDER BY f.category, f.name";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $vehicleId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
      $features[] = $row;
    }

    $stmt->close();
    $conn->close();

    return $features;
  }

  // 2. Now, when retrieving the vehicle data:
  $vehicle_id = intval($_GET['id']);
  // ... existing code to get vehicle details
  $features = getVehicleFeaturesWithDetails($vehicle_id);

  // 3. Organize features by category
  $featuresByCategory = [];
  foreach ($features as $feature) {
    if (!isset($featuresByCategory[$feature['category']])) {
      $featuresByCategory[$feature['category']] = [];
    }
    $featuresByCategory[$feature['category']][] = $feature;
  }
  // Get drivetrain name
  $drivetrain_name = '';
  if ($drivetrain > 0) {
    $drive_query = "SELECT name FROM drive_types WHERE id = ?";
    $drive_stmt = $conn->prepare($drive_query);
    $drive_stmt->bind_param("i", $drivetrain);
    $drive_stmt->execute();
    $drive_result = $drive_stmt->get_result();
    if ($drive_row = $drive_result->fetch_assoc()) {
      $drivetrain_name = $drive_row['name'];
    }
    $drive_stmt->close();
  }

  // Get exterior color name
  $exterior_color_name = '';
  if ($exterior_color > 0) {
    $ext_query = "SELECT name FROM colors WHERE id = ?";
    $ext_stmt = $conn->prepare($ext_query);
    $ext_stmt->bind_param("i", $exterior_color);
    $ext_stmt->execute();
    $ext_result = $ext_stmt->get_result();
    if ($ext_row = $ext_result->fetch_assoc()) {
      $exterior_color_name = $ext_row['name'];
    }
    $ext_stmt->close();
  }

  // Get interior color name
  $interior_color_name = '';
  if ($interior_color > 0) {
    $int_query = "SELECT name FROM colors WHERE id = ?";
    $int_stmt = $conn->prepare($int_query);
    $int_stmt->bind_param("i", $interior_color);
    $int_stmt->execute();
    $int_result = $int_stmt->get_result();
    if ($int_row = $int_result->fetch_assoc()) {
      $interior_color_name = $int_row['name'];
    }
    $int_stmt->close();
  }

  // Get status name
  $status_name = '';
  if ($status > 0) {
    $status_query = "SELECT name FROM vehicle_status WHERE id = ?";
    $status_stmt = $conn->prepare($status_query);
    $status_stmt->bind_param("i", $status);
    $status_stmt->execute();
    $status_result = $status_stmt->get_result();
    if ($status_row = $status_result->fetch_assoc()) {
      $status_name = $status_row['name'];
    }
    $status_stmt->close();
  }

  // Build the SQL query based on the actual columns in the database
  $sql_parts = [];
  $types = "";
  $params = [];

  // Check if each column exists and add it to the query if it does
  if (in_array('make', $columns)) {
    $sql_parts[] = "make = ?";
    $types .= "s";
    $params[] = $make_name;
  } else if (in_array('make_id', $columns)) {
    $sql_parts[] = "make_id = ?";
    $types .= "i";
    $params[] = $make;
  }

  if (in_array('model', $columns)) {
    $sql_parts[] = "model = ?";
    $types .= "s";
    $params[] = $model_name;
  } else if (in_array('model_id', $columns)) {
    $sql_parts[] = "model_id = ?";
    $types .= "i";
    $params[] = $model;
  }

  if (in_array('year', $columns)) {
    $sql_parts[] = "year = ?";
    $types .= "i";
    $params[] = $year;
  }

  if (in_array('body_style', $columns)) {
    $sql_parts[] = "body_style = ?";
    $types .= "s";
    $params[] = $body_style_name;
  } else if (in_array('body_type_id', $columns)) {
    $sql_parts[] = "body_type_id = ?";
    $types .= "i";
    $params[] = $body_style;
  }

  if (in_array('mileage', $columns)) {
    $sql_parts[] = "mileage = ?";
    $types .= "i";
    $params[] = $mileage;
  }

  // To this
  if (in_array('price', $columns)) {
    if ($price === null) {
      $sql_parts[] = "price = NULL";
    } else {
      $sql_parts[] = "price = ?";
      $types .= "d";
      $params[] = $price;
    }
  }

  if (in_array('vin', $columns)) {
    $sql_parts[] = "vin = ?";
    $types .= "s";
    $params[] = $vin;
  }

  if (in_array('fuel_type', $columns)) {
    $sql_parts[] = "fuel_type = ?";
    $types .= "s";
    $params[] = $fuel_type_name;
  } else if (in_array('fuel_type_id', $columns)) {
    $sql_parts[] = "fuel_type_id = ?";
    $types .= "i";
    $params[] = $fuel_type;
  }

  if (in_array('transmission', $columns)) {
    $sql_parts[] = "transmission = ?";
    $types .= "s";
    $params[] = $transmission_name;
  } else if (in_array('transmission_id', $columns)) {
    $sql_parts[] = "transmission_id = ?";
    $types .= "i";
    $params[] = $transmission;
  }

  if (in_array('drivetrain', $columns)) {
    $sql_parts[] = "drivetrain = ?";
    $types .= "s";
    $params[] = $drivetrain_name;
  } else if (in_array('drivetrain_id', $columns)) {
    $sql_parts[] = "drivetrain_id = ?";
    $types .= "i";
    $params[] = $drivetrain;
  }

  if (in_array('engine', $columns)) {
    $sql_parts[] = "engine = ?";
    $types .= "s";
    $params[] = $engine;
  }

  if (in_array('exterior_color', $columns)) {
    $sql_parts[] = "exterior_color = ?";
    $types .= "s";
    $params[] = $exterior_color_name;
  } else if (in_array('exterior_color_id', $columns)) {
    $sql_parts[] = "exterior_color_id = ?";
    $types .= "i";
    $params[] = $exterior_color;
  }

  if (in_array('interior_color', $columns)) {
    $sql_parts[] = "interior_color = ?";
    $types .= "s";
    $params[] = $interior_color_name;
  } else if (in_array('interior_color_id', $columns)) {
    $sql_parts[] = "interior_color_id = ?";
    $types .= "i";
    $params[] = $interior_color;
  }

  if (in_array('status', $columns)) {
    $sql_parts[] = "status = ?";
    $types .= "s";
    $params[] = $status_name;
  } else if (in_array('status_id', $columns)) {
    $sql_parts[] = "status_id = ?";
    $types .= "i";
    $params[] = $status;
  }

  if (in_array('description', $columns)) {
    $sql_parts[] = "description = ?";
    $types .= "s";
    $params[] = $description;
  }

  if (in_array('updated_by', $columns)) {
    $sql_parts[] = "updated_by = ?";
    $types .= "i";
    $params[] = $_SESSION["admin_id"];
  }

  if (in_array('updated_at', $columns)) {
    $sql_parts[] = "updated_at = NOW()";
  }

  // Add vehicle_id parameter at the end
  $types .= "i";
  $params[] = $vehicle_id;

  // Create the SQL query
  $sql = "UPDATE vehicles SET " . implode(", ", $sql_parts) . " WHERE id = ?";

  // Debug - remove in production
  // $_SESSION['debug_sql'] = $sql;
  // $_SESSION['debug_types'] = $types;
  // $_SESSION['debug_params'] = $params;

  $stmt = $conn->prepare($sql);

  if (!$stmt) {
    $_SESSION['errors'] = ["Failed to prepare query: " . $conn->error];
    $_SESSION['form_data'] = $_POST;
    header("Location: edit_vehicle_form.php?id=" . $vehicle_id);
    exit;
  }

  // Dynamically bind parameters
  if (!empty($params)) {
    $refs = [];
    foreach ($params as $key => $value) {
      $refs[$key] = &$params[$key];
    }

    // Call bind_param with dynamically created references
    array_unshift($refs, $types);
    call_user_func_array([$stmt, 'bind_param'], $refs);
  }

  if ($stmt->execute()) {
    // Process images if uploaded
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
      processVehicleImages($conn, $vehicle_id);
    }

    // Process deleted images
    if (isset($_POST['delete_images']) && !empty($_POST['delete_images'])) {
      deleteVehicleImages($conn, $vehicle_id, $_POST['delete_images']);
    }

    $_SESSION['success'] = "Vehicle updated successfully!";
    header("Location: vehicle_details.php?id=" . $vehicle_id);
    exit;
  } else {
    $_SESSION['errors'] = ["Failed to update vehicle: " . $stmt->error];
    $_SESSION['form_data'] = $_POST;
    header("Location: edit_vehicle_form.php?id=" . $vehicle_id);
    exit;
  }

  $stmt->close();
  $conn->close();
} else {
  // If not a POST request or vehicle_id not provided, redirect
  header("Location: vehicles.php");
  exit;
}

// Function to process and save vehicle images
function processVehicleImages($conn, $vehicle_id)
{
  // Create upload directory if it doesn't exist
  $target_dir = "../uploads/vehicles/{$vehicle_id}/";
  if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
  }

  $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
  $max_file_size = 5 * 1024 * 1024; // 5MB

  // Get current highest display order
  $display_order = 1;
  if ($conn->query("SHOW COLUMNS FROM `vehicle_images` LIKE 'display_order'")->num_rows > 0) {
    $order_query = "SELECT MAX(display_order) as max_order FROM vehicle_images WHERE vehicle_id = ?";
    $order_stmt = $conn->prepare($order_query);
    $order_stmt->bind_param("i", $vehicle_id);
    $order_stmt->execute();
    $order_result = $order_stmt->get_result();
    $order_row = $order_result->fetch_assoc();
    $display_order = ($order_row['max_order'] !== null) ? $order_row['max_order'] + 1 : 1;
    $order_stmt->close();
  }

  // Process each uploaded file
  $file_count = count($_FILES['images']['name']);

  for ($i = 0; $i < $file_count; $i++) {
    if ($_FILES['images']['error'][$i] == 0) {
      $tmp_name = $_FILES['images']['tmp_name'][$i];
      $original_name = $_FILES['images']['name'][$i];
      $file_size = $_FILES['images']['size'][$i];

      // Get file extension
      $file_ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

      // Check file size and type
      if ($file_size > $max_file_size) {
        $_SESSION['warnings'][] = "File {$original_name} exceeds the maximum size of 5MB.";
        continue;
      }

      if (!in_array($file_ext, $allowed_extensions)) {
        $_SESSION['warnings'][] = "File {$original_name} has an invalid extension. Only JPG, JPEG, PNG, and WEBP are allowed.";
        continue;
      }

      // Generate a unique filename
      $new_filename = uniqid('vehicle_') . '.' . $file_ext;
      $target_file = $target_dir . $new_filename;

      // Move the uploaded file
      if (move_uploaded_file($tmp_name, $target_file)) {
        // Insert image record into database
        $image_path = "uploads/vehicles/{$vehicle_id}/{$new_filename}";

        // Check if the columns exist in vehicle_images table
        $columns = [];
        $result = $conn->query("DESCRIBE vehicle_images");
        while ($row = $result->fetch_assoc()) {
          $columns[] = $row['Field'];
        }

        $sql_parts = [];
        $types = "is"; // vehicle_id and image_path are always included
        $params = [$vehicle_id, $image_path];

        $sql_parts[] = "vehicle_id";
        $sql_parts[] = "image_path";

        if (in_array('original_filename', $columns)) {
          $sql_parts[] = "original_filename";
          $types .= "s";
          $params[] = $original_name;
        }

        if (in_array('display_order', $columns)) {
          $sql_parts[] = "display_order";
          $types .= "i";
          $params[] = $display_order;
        }

        if (in_array('uploaded_by', $columns) && isset($_SESSION["admin_id"])) {
          $sql_parts[] = "uploaded_by";
          $types .= "i";
          $params[] = $_SESSION["admin_id"];
        }

        if (in_array('uploaded_at', $columns)) {
          $sql_parts[] = "uploaded_at";
          $sql_parts[] = "NOW()";
        } else {
          $sql_parts[] = "created_at";
          $sql_parts[] = "NOW()";
        }

        $sql = "INSERT INTO vehicle_images (" . implode(", ", array_filter($sql_parts, function ($item) {
          return $item !== "NOW()";
        })) .
          ") VALUES (" . implode(", ", array_map(function ($item) {
            return ($item === "NOW()") ? $item : "?";
          }, $sql_parts)) . ")";

        $stmt = $conn->prepare($sql);

        if ($stmt) {
          $refs = [];
          foreach ($params as $key => $value) {
            $refs[$key] = &$params[$key];
          }

          // Call bind_param with dynamically created references
          array_unshift($refs, $types);
          call_user_func_array([$stmt, 'bind_param'], $refs);

          $stmt->execute();
          $stmt->close();

          $display_order++;
        } else {
          $_SESSION['warnings'][] = "Failed to save image record for {$original_name}.";
        }
      } else {
        $_SESSION['warnings'][] = "Failed to upload {$original_name}.";
      }
    }
  }
}

// Function to delete vehicle images
function deleteVehicleImages($conn, $vehicle_id, $image_ids)
{
  if (!is_array($image_ids)) {
    $image_ids = [$image_ids];
  }

  foreach ($image_ids as $image_id) {
    $image_id = intval($image_id);

    // Get image path
    $stmt = $conn->prepare("SELECT image_path FROM vehicle_images WHERE id = ? AND vehicle_id = ?");
    $stmt->bind_param("ii", $image_id, $vehicle_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
      $image_path = "../" . $row['image_path'];

      // Delete from database
      $delete_stmt = $conn->prepare("DELETE FROM vehicle_images WHERE id = ?");
      $delete_stmt->bind_param("i", $image_id);
      $delete_stmt->execute();
      $delete_stmt->close();

      // Delete file from server
      if (file_exists($image_path)) {
        unlink($image_path);
      }
    }

    $stmt->close();
  }
}
