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

// Get vehicle ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
  $_SESSION['errors'] = ["Invalid vehicle ID"];
  header("Location: vehicles.php");
  exit;
}

$vehicle_id = intval($_GET['id']);

// Get connection
$conn = getConnection();

// First, check the structure of the vehicles table to determine available columns
$table_structure_query = "DESCRIBE vehicles";
$structure_result = $conn->query($table_structure_query);
$columns = [];

while ($column = $structure_result->fetch_assoc()) {
  $columns[] = $column['Field'];
}

// Simple query approach - just get all fields from the vehicles table
$vehicle_query = "SELECT * FROM vehicles WHERE id = ?";
$stmt = $conn->prepare($vehicle_query);
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  $_SESSION['errors'] = ["Vehicle not found"];
  header("Location: vehicles.php");
  exit;
}

$vehicle = $result->fetch_assoc();
$stmt->close();

// Get vehicle images
$images_query = "SELECT * FROM vehicle_images WHERE vehicle_id = ?";
$images_stmt = $conn->prepare($images_query);
$images_stmt->bind_param("i", $vehicle_id);
$images_stmt->execute();
$images_result = $images_stmt->get_result();

$images = [];
while ($image = $images_result->fetch_assoc()) {
  $images[] = $image;
}
$images_stmt->close();

// Get vehicle features
function getVehicleFeatures($vehicleId)
{
  $conn = getConnection();
  $features = [];

  $query = "SELECT feature_id FROM vehicle_features WHERE vehicle_id = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("i", $vehicleId);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = $result->fetch_assoc()) {
    $features[] = $row['feature_id'];
  }

  $stmt->close();
  $conn->close();

  return $features;
}

// Get all features from database
function getAllFeatures()
{
  $conn = getConnection();
  $features = [];

  $sql = "SELECT id, name, category FROM features ORDER BY category, name";
  $result = $conn->query($sql);

  if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $features[] = $row;
    }
  }

  $conn->close();
  return $features;
}

// Get all features and organize by category
$allFeatures = getAllFeatures();
$featuresByCategory = [];
foreach ($allFeatures as $feature) {
  $category = $feature['category'] ?: 'Other';
  if (!isset($featuresByCategory[$category])) {
    $featuresByCategory[$category] = [];
  }
  $featuresByCategory[$category][] = $feature;
}

// Get vehicle features
$vehicleFeatures = getVehicleFeatures($vehicle_id);

// Function to get dropdown options
function getDropdownOptions()
{
  $conn = getConnection();
  $dropdowns = [];

  // Fetch makes - removed display_order
  $makesSql = "SELECT id, name FROM makes ORDER BY name";
  $makesResult = $conn->query($makesSql);
  $dropdowns['makes'] = [];
  if ($makesResult && $makesResult->num_rows > 0) {
    while ($row = $makesResult->fetch_assoc()) {
      $dropdowns['makes'][] = $row;
    }
  }

  // Fetch body types
  $bodyTypesSql = "SELECT id, name FROM body_types ORDER BY name";
  $bodyTypesResult = $conn->query($bodyTypesSql);
  $dropdowns['bodyTypes'] = [];
  if ($bodyTypesResult && $bodyTypesResult->num_rows > 0) {
    while ($row = $bodyTypesResult->fetch_assoc()) {
      $dropdowns['bodyTypes'][] = $row;
    }
  }

  // Fetch fuel types
  $fuelTypesSql = "SELECT id, name FROM fuel_types ORDER BY name";
  $fuelTypesResult = $conn->query($fuelTypesSql);
  $dropdowns['fuelTypes'] = [];
  if ($fuelTypesResult && $fuelTypesResult->num_rows > 0) {
    while ($row = $fuelTypesResult->fetch_assoc()) {
      $dropdowns['fuelTypes'][] = $row;
    }
  }

  // Fetch transmission types
  $transmissionsSql = "SELECT id, name FROM transmission_types ORDER BY name";
  $transmissionsResult = $conn->query($transmissionsSql);
  $dropdowns['transmissions'] = [];
  if ($transmissionsResult && $transmissionsResult->num_rows > 0) {
    while ($row = $transmissionsResult->fetch_assoc()) {
      $dropdowns['transmissions'][] = $row;
    }
  }

  // Fetch drive types
  $driveTypesSql = "SELECT id, name FROM drive_types ORDER BY name";
  $driveTypesResult = $conn->query($driveTypesSql);
  $dropdowns['driveTypes'] = [];
  if ($driveTypesResult && $driveTypesResult->num_rows > 0) {
    while ($row = $driveTypesResult->fetch_assoc()) {
      $dropdowns['driveTypes'][] = $row;
    }
  }

  // Fetch colors
  $colorsSql = "SELECT id, name, hex_code FROM colors ORDER BY name";
  $colorsResult = $conn->query($colorsSql);
  $dropdowns['colors'] = [];
  if ($colorsResult && $colorsResult->num_rows > 0) {
    while ($row = $colorsResult->fetch_assoc()) {
      $dropdowns['colors'][] = $row;
    }
  }

  // Fetch vehicle statuses
  $statusSql = "SELECT id, name, css_class FROM vehicle_status ORDER BY name";
  $statusResult = $conn->query($statusSql);
  $dropdowns['statuses'] = [];
  if ($statusResult && $statusResult->num_rows > 0) {
    while ($row = $statusResult->fetch_assoc()) {
      $dropdowns['statuses'][] = $row;
    }
  }

  $conn->close();
  return $dropdowns;
}

// Get models for selected make - removed display_order
function getModelsByMakeId($makeId)
{
  $conn = getConnection();
  $models = [];

  $stmt = $conn->prepare("SELECT id, name FROM models WHERE make_id = ? ORDER BY name");
  $stmt->bind_param("i", $makeId);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $models[] = $row;
    }
  }

  $stmt->close();
  $conn->close();
  return $models;
}

// Get dropdown options
$dropdowns = getDropdownOptions();

// Determine if the vehicle uses make_id/model_id or just make/model strings
$has_make_id = in_array('make_id', $columns);
$has_model_id = in_array('model_id', $columns);

// If make_id exists, get the models for that make
$models = [];
if ($has_make_id && !empty($vehicle['make_id'])) {
  $models = getModelsByMakeId($vehicle['make_id']);
}
// If not, we need to try to find the make_id by looking up the make name
else if (!$has_make_id && !empty($vehicle['make'])) {
  // Find the make_id based on the make name
  foreach ($dropdowns['makes'] as $make) {
    if ($make['name'] == $vehicle['make']) {
      // We found the make_id
      $models = getModelsByMakeId($make['id']);
      $vehicle['make_id'] = $make['id']; // Add this for the form
      break;
    }
  }
}

// Same for model_id if needed
if (!$has_model_id && !empty($vehicle['model']) && !empty($vehicle['make_id'])) {
  foreach (getModelsByMakeId($vehicle['make_id']) as $model) {
    if ($model['name'] == $vehicle['model']) {
      $vehicle['model_id'] = $model['id']; // Add this for the form
      break;
    }
  }
}

// Debug information - remove this in production
echo "<!-- Available columns: " . implode(', ', $columns) . " -->";
?>

<!doctype html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="../src/output.css" rel="stylesheet">
  <title>Edit Vehicle - CentralAutogy</title>
  <link rel="stylesheet" href="assets/css/index.css">
</head>

<body class="bg-gray-50">
  <?php include 'includes/header.php'; ?>
  <div class="flex h-[calc(100vh-64px)]">
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 p-6 overflow-y-auto bg-gray-50">
      <!-- Mobile menu button -->
      <div class="md:hidden mb-6">
        <button id="mobileMenuBtn" class="flex items-center justify-center bg-white shadow-md rounded-lg p-2 w-full">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
          <span class="ml-2 text-indigo-600 font-medium">Menu</span>
        </button>
      </div>

      <!-- Page Title -->
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">Edit Vehicle</h1>
          <p class="text-gray-600">Update vehicle information</p>
        </div>
        <div>
          <a href="vehicle_details.php?id=<?php echo $vehicle_id; ?>" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition duration-300 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            Back to Details
          </a>
        </div>
      </div>

      <!-- Flash Messages -->
      <?php if (isset($_SESSION['errors']) && is_array($_SESSION['errors']) && count($_SESSION['errors']) > 0): ?>
        <div class="flash-message mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow transition-opacity duration-500">
          <div class="flex justify-between items-center">
            <div>
              <div class="flex items-center mb-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <p class="font-medium">The following errors occurred:</p>
              </div>
              <ul class="list-disc list-inside pl-7">
                <?php foreach ($_SESSION['errors'] as $error): ?>
                  <li><?php echo $error; ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
            <button class="close-flash">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>
        </div>
        <?php unset($_SESSION['errors']); ?>
      <?php endif; ?>

      <!-- Edit Vehicle Form -->
      <div class="dashboard-card bg-white p-6">
        <form id="editVehicleForm" action="edit_vehicle.php" method="post" enctype="multipart/form-data" class="space-y-6">
          <input type="hidden" name="vehicle_id" value="<?php echo $vehicle_id; ?>">

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Basic Information Section -->
            <div class="bg-gray-50 p-5 rounded-lg space-y-4 col-span-1 md:col-span-2">
              <h3 class="text-lg font-bold text-gray-800 mb-2">Basic Information</h3>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label for="make" class="block text-sm font-medium text-gray-700 mb-1">Make</label>
                  <select id="make" name="make" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                    <option value="">Select Make</option>
                    <?php foreach ($dropdowns['makes'] as $make): ?>
                      <option value="<?php echo $make['id']; ?>" <?php echo (isset($vehicle['make_id']) && $vehicle['make_id'] == $make['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($make['name']); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div>
                  <label for="model" class="block text-sm font-medium text-gray-700 mb-1">Model</label>
                  <select id="model" name="model" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                    <option value="">Select Model</option>
                    <?php foreach ($models as $model): ?>
                      <option value="<?php echo $model['id']; ?>" <?php echo (isset($vehicle['model_id']) && $vehicle['model_id'] == $model['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($model['name']); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div>
                  <label for="year" class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                  <input type="number" id="year" name="year" min="1900" max="<?php echo date('Y') + 1; ?>" value="<?php echo $vehicle['year']; ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                </div>
                <?php if (in_array('stock_number', $columns)): ?>
                  <div>
                    <label for="stock_number" class="block text-sm font-medium text-gray-700 mb-1">Stock Number</label>
                    <input type="text" id="stock_number" name="stock_number" value="<?php echo htmlspecialchars($vehicle['stock_number'] ?? ''); ?>" readonly class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg focus:outline-none text-sm cursor-not-allowed">
                    <p class="text-xs text-gray-500 mt-1">Stock number cannot be changed</p>
                  </div>
                <?php endif; ?>
                <div>
                  <label for="vin" class="block text-sm font-medium text-gray-700 mb-1">VIN</label>
                  <input type="text" id="vin" name="vin" value="<?php echo htmlspecialchars($vehicle['vin']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                </div>
                <div>
                  <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price (Optional)</label>
                  <input type="text" id="price" name="price" value="<?php echo $vehicle['price'] ? $vehicle['price'] : ''; ?>" placeholder="Leave empty for no price" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                  <p class="text-xs text-gray-500 mt-1">Leave empty if price is not available or negotiable</p>
                </div>
                <div>
                  <label for="mileage" class="block text-sm font-medium text-gray-700 mb-1">Mileage</label>
                  <input type="text" id="mileage" name="mileage" value="<?php echo $vehicle['mileage']; ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                </div>
                <div>
                  <label for="body_style" class="block text-sm font-medium text-gray-700 mb-1">Body Type</label>
                  <select id="body_style" name="body_style" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                    <option value="">Select Body Type</option>
                    <?php foreach ($dropdowns['bodyTypes'] as $bodyType): ?>
                      <?php
                      $selected = false;
                      if (isset($vehicle['body_type_id']) && $vehicle['body_type_id'] == $bodyType['id']) {
                        $selected = true;
                      } else if (isset($vehicle['body_style']) && $vehicle['body_style'] == $bodyType['name']) {
                        $selected = true;
                      }
                      ?>
                      <option value="<?php echo $bodyType['id']; ?>" <?php echo $selected ? 'selected' : ''; ?>><?php echo htmlspecialchars($bodyType['name']); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div>
                  <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                  <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                    <option value="">Select Status</option>
                    <?php foreach ($dropdowns['statuses'] as $status): ?>
                      <?php
                      $selected = false;
                      if (isset($vehicle['status_id']) && $vehicle['status_id'] == $status['id']) {
                        $selected = true;
                      } else if (isset($vehicle['status']) && $vehicle['status'] == $status['name']) {
                        $selected = true;
                      }
                      ?>
                      <option value="<?php echo $status['id']; ?>" <?php echo $selected ? 'selected' : ''; ?>><?php echo htmlspecialchars($status['name']); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
            <!-- Features Section -->
            <div class="bg-gray-50 p-5 rounded-lg space-y-4 col-span-1 md:col-span-2 mt-6">
              <h3 class="text-lg font-bold text-gray-800 mb-2">Vehicle Features</h3>

              <?php if (!empty($featuresByCategory)): ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <?php foreach ($featuresByCategory as $category => $categoryFeatures): ?>
                    <div class="bg-white p-3 rounded-lg">
                      <h4 class="font-medium text-gray-700 mb-2"><?php echo htmlspecialchars($category); ?></h4>
                      <div class="space-y-2">
                        <?php foreach ($categoryFeatures as $feature): ?>
                          <div class="flex items-center">
                            <input type="checkbox"
                              id="feature_<?php echo $feature['id']; ?>"
                              name="features[]"
                              value="<?php echo $feature['id']; ?>"
                              <?php echo in_array($feature['id'], $vehicleFeatures) ? 'checked' : ''; ?>
                              class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="feature_<?php echo $feature['id']; ?>" class="ml-2 block text-sm text-gray-700">
                              <?php echo htmlspecialchars($feature['name']); ?>
                            </label>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <div class="bg-yellow-50 p-4 rounded-lg">
                  <p class="text-sm text-yellow-700">No features found. You can add features in the "Manage Dropdowns" section.</p>
                </div>
              <?php endif; ?>
            </div>
            <!-- Technical Details Section -->
            <div class="bg-gray-50 p-5 rounded-lg space-y-4">
              <h3 class="text-lg font-bold text-gray-800 mb-2">Technical Details</h3>

              <div>
                <label for="engine" class="block text-sm font-medium text-gray-700 mb-1">Engine</label>
                <input type="text" id="engine" name="engine" value="<?php echo htmlspecialchars($vehicle['engine'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
              </div>
              <div>
                <label for="fuel_type" class="block text-sm font-medium text-gray-700 mb-1">Fuel Type</label>
                <select id="fuel_type" name="fuel_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                  <option value="">Select Fuel Type</option>
                  <?php foreach ($dropdowns['fuelTypes'] as $fuelType): ?>
                    <?php
                    $selected = false;
                    if (isset($vehicle['fuel_type_id']) && $vehicle['fuel_type_id'] == $fuelType['id']) {
                      $selected = true;
                    } else if (isset($vehicle['fuel_type']) && $vehicle['fuel_type'] == $fuelType['name']) {
                      $selected = true;
                    }
                    ?>
                    <option value="<?php echo $fuelType['id']; ?>" <?php echo $selected ? 'selected' : ''; ?>><?php echo htmlspecialchars($fuelType['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div>
                <label for="transmission" class="block text-sm font-medium text-gray-700 mb-1">Transmission</label>
                <select id="transmission" name="transmission" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                  <option value="">Select Transmission</option>
                  <?php foreach ($dropdowns['transmissions'] as $transmission): ?>
                    <?php
                    $selected = false;
                    if (isset($vehicle['transmission_id']) && $vehicle['transmission_id'] == $transmission['id']) {
                      $selected = true;
                    } else if (isset($vehicle['transmission']) && $vehicle['transmission'] == $transmission['name']) {
                      $selected = true;
                    }
                    ?>
                    <option value="<?php echo $transmission['id']; ?>" <?php echo $selected ? 'selected' : ''; ?>><?php echo htmlspecialchars($transmission['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div>
                <label for="drivetrain" class="block text-sm font-medium text-gray-700 mb-1">Drive Type</label>
                <select id="drivetrain" name="drivetrain" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                  <option value="">Select Drive Type</option>
                  <?php foreach ($dropdowns['driveTypes'] as $driveType): ?>
                    <?php
                    $selected = false;
                    if (isset($vehicle['drivetrain_id']) && $vehicle['drivetrain_id'] == $driveType['id']) {
                      $selected = true;
                    } else if (isset($vehicle['drivetrain']) && $vehicle['drivetrain'] == $driveType['name']) {
                      $selected = true;
                    }
                    ?>
                    <option value="<?php echo $driveType['id']; ?>" <?php echo $selected ? 'selected' : ''; ?>><?php echo htmlspecialchars($driveType['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <!-- Appearance Section -->
            <div class="bg-gray-50 p-5 rounded-lg space-y-4">
              <h3 class="text-lg font-bold text-gray-800 mb-2">Appearance</h3>

              <div>
                <label for="exterior_color" class="block text-sm font-medium text-gray-700 mb-1">Exterior Color</label>
                <select id="exterior_color" name="exterior_color" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                  <option value="">Select Exterior Color</option>
                  <?php foreach ($dropdowns['colors'] as $color): ?>
                    <?php
                    $selected = false;
                    if (isset($vehicle['exterior_color_id']) && $vehicle['exterior_color_id'] == $color['id']) {
                      $selected = true;
                    } else if (isset($vehicle['exterior_color']) && $vehicle['exterior_color'] == $color['name']) {
                      $selected = true;
                    }
                    ?>
                    <option value="<?php echo $color['id']; ?>" <?php echo $selected ? 'selected' : ''; ?>><?php echo htmlspecialchars($color['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div>
                <label for="interior_color" class="block text-sm font-medium text-gray-700 mb-1">Interior Color</label>
                <select id="interior_color" name="interior_color" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                  <option value="">Select Interior Color</option>
                  <?php foreach ($dropdowns['colors'] as $color): ?>
                    <?php
                    $selected = false;
                    if (isset($vehicle['interior_color_id']) && $vehicle['interior_color_id'] == $color['id']) {
                      $selected = true;
                    } else if (isset($vehicle['interior_color']) && $vehicle['interior_color'] == $color['name']) {
                      $selected = true;
                    }
                    ?>
                    <option value="<?php echo $color['id']; ?>" <?php echo $selected ? 'selected' : ''; ?>><?php echo htmlspecialchars($color['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description/Notes</label>
                <textarea id="description" name="description" rows="5" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm resize-none"><?php echo htmlspecialchars($vehicle['description'] ?? ''); ?></textarea>
              </div>
            </div>

            <!-- Current Images -->
            <div class="bg-gray-50 p-5 rounded-lg space-y-4 col-span-1 md:col-span-2">
              <h3 class="text-lg font-bold text-gray-800 mb-2">Current Images</h3>

              <?php if (count($images) > 0): ?>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                  <?php foreach ($images as $image): ?>
                    <div class="relative">
                      <img src="../<?php echo htmlspecialchars($image['image_path']); ?>" alt="Vehicle Image" class="w-full h-32 object-cover rounded-lg">
                      <div class="absolute top-2 right-2">
                        <label class="inline-flex items-center p-1.5 bg-white border border-gray-200 rounded-md cursor-pointer hover:bg-red-50">
                          <input type="checkbox" name="delete_images[]" value="<?php echo $image['id']; ?>" class="h-4 w-4 text-red-600 focus:ring-red-500 rounded">
                          <span class="ml-1 text-xs text-red-600">Delete</span>
                        </label>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <div class="bg-gray-100 p-4 rounded-lg text-center">
                  <p class="text-gray-600">No images uploaded for this vehicle</p>
                </div>
              <?php endif; ?>

              <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Add New Images</label>
                <div class="file-drop-area">
                  <input type="file" id="modalImages" name="images[]" multiple class="file-input" onChange="updateFileNames()">
                  <div class="flex flex-col items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-3 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a20 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="text-sm text-gray-600 mb-1 font-medium">Drag & drop vehicle images here</p>
                    <p class="text-xs text-gray-500">or click to browse files</p>
                  </div>
                  <div id="fileNames" class="mt-3 text-gray-600 text-xs space-y-1"></div>
                </div>
                <p class="text-xs text-gray-500 mt-2">Maximum file size: 5MB. Accepted formats: JPG, JPEG, PNG, WEBP</p>
              </div>
            </div>
          </div>

          <div class="flex justify-between pt-4 border-t">
            <a href="vehicle_details.php?id=<?php echo $vehicle_id; ?>" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition duration-300">Cancel</a>
            <button type="submit" class="px-6 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-lg transition duration-300 shadow-md">
              Save Changes
            </button>
          </div>
        </form>
      </div>
    </main>
  </div>

  <!-- Store all models data from PHP for use in JavaScript -->
  <script id="allModelsData" type="application/json">
    <?php
    // Get all models grouped by make
    $allModelsQuery = "SELECT id, name, make_id FROM models ORDER BY name";
    $allModelsResult = $conn->query($allModelsQuery);
    $allModelsData = [];

    while ($model = $allModelsResult->fetch_assoc()) {
      if (!isset($allModelsData[$model['make_id']])) {
        $allModelsData[$model['make_id']] = [];
      }
      $allModelsData[$model['make_id']][] = [
        'id' => $model['id'],
        'name' => $model['name']
      ];
    }

    echo json_encode($allModelsData);
    ?>
  </script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Store models data globally
      let allModels;
      try {
        const modelsDataElement = document.getElementById('allModelsData');
        if (modelsDataElement) {
          allModels = JSON.parse(modelsDataElement.textContent);
        }
      } catch (e) {
        console.error('Error parsing models data:', e);
        allModels = {};
      }

      // Make dropdown change event
      const makeDropdown = document.getElementById('make');
      const modelDropdown = document.getElementById('model');

      if (makeDropdown && modelDropdown) {
        makeDropdown.addEventListener('change', function() {
          updateModelDropdown(this.value);
        });
      }

      function updateModelDropdown(makeId) {
        // Clear current options
        modelDropdown.innerHTML = '<option value="">Select Model</option>';

        if (!makeId) return;

        // Ensure makeId is treated as a string for consistent lookup
        const makeIdStr = makeId.toString();

        // Check if models exist for this make
        if (allModels[makeIdStr] && Array.isArray(allModels[makeIdStr]) && allModels[makeIdStr].length > 0) {
          // Add models to dropdown
          allModels[makeIdStr].forEach(function(model) {
            const option = document.createElement('option');
            option.value = model.id;
            option.textContent = model.name;
            modelDropdown.appendChild(option);
          });
        } else {
          const option = document.createElement('option');
          option.value = "";
          option.textContent = "No models available for this make";
          modelDropdown.appendChild(option);
        }
      }

      // Close flash message button
      const closeFlashButtons = document.querySelectorAll('.close-flash');
      closeFlashButtons.forEach(button => {
        button.addEventListener('click', function() {
          const flashMessage = this.closest('.flash-message');
          if (flashMessage) {
            flashMessage.style.opacity = '0';
            setTimeout(() => {
              flashMessage.style.display = 'none';
            }, 500);
          }
        });
      });
    });

    // Function to update file names when files are selected
    function updateFileNames() {
      const input = document.getElementById('modalImages');
      const fileNamesDiv = document.getElementById('fileNames');

      fileNamesDiv.innerHTML = '';

      if (input.files.length > 0) {
        for (let i = 0; i < input.files.length; i++) {
          const fileName = document.createElement('div');
          fileName.textContent = input.files[i].name;
          fileNamesDiv.appendChild(fileName);
        }
      }
    }
  </script>
</body>

</html>
<?php
// Close connection
$conn->close();
?>