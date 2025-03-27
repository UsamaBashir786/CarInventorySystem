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

// Function to get dropdown options
function getDropdownOptions()
{
  $conn = getConnection();
  $dropdowns = [];

  // Fetch makes
  $makesSql = "SELECT id, name FROM makes ORDER BY display_order, name";
  $makesResult = $conn->query($makesSql);
  $dropdowns['makes'] = [];
  if ($makesResult && $makesResult->num_rows > 0) {
    while ($row = $makesResult->fetch_assoc()) {
      $dropdowns['makes'][] = $row;
    }
  }

  // Fetch body types
  $bodyTypesSql = "SELECT id, name FROM body_types ORDER BY display_order, name";
  $bodyTypesResult = $conn->query($bodyTypesSql);
  $dropdowns['bodyTypes'] = [];
  if ($bodyTypesResult && $bodyTypesResult->num_rows > 0) {
    while ($row = $bodyTypesResult->fetch_assoc()) {
      $dropdowns['bodyTypes'][] = $row;
    }
  }

  // Fetch fuel types
  $fuelTypesSql = "SELECT id, name FROM fuel_types ORDER BY display_order, name";
  $fuelTypesResult = $conn->query($fuelTypesSql);
  $dropdowns['fuelTypes'] = [];
  if ($fuelTypesResult && $fuelTypesResult->num_rows > 0) {
    while ($row = $fuelTypesResult->fetch_assoc()) {
      $dropdowns['fuelTypes'][] = $row;
    }
  }

  // Fetch transmission types
  $transmissionsSql = "SELECT id, name FROM transmission_types ORDER BY display_order, name";
  $transmissionsResult = $conn->query($transmissionsSql);
  $dropdowns['transmissions'] = [];
  if ($transmissionsResult && $transmissionsResult->num_rows > 0) {
    while ($row = $transmissionsResult->fetch_assoc()) {
      $dropdowns['transmissions'][] = $row;
    }
  }

  // Fetch drive types
  $driveTypesSql = "SELECT id, name FROM drive_types ORDER BY display_order, name";
  $driveTypesResult = $conn->query($driveTypesSql);
  $dropdowns['driveTypes'] = [];
  if ($driveTypesResult && $driveTypesResult->num_rows > 0) {
    while ($row = $driveTypesResult->fetch_assoc()) {
      $dropdowns['driveTypes'][] = $row;
    }
  }

  // Fetch colors
  $colorsSql = "SELECT id, name, hex_code FROM colors ORDER BY display_order, name";
  $colorsResult = $conn->query($colorsSql);
  $dropdowns['colors'] = [];
  if ($colorsResult && $colorsResult->num_rows > 0) {
    while ($row = $colorsResult->fetch_assoc()) {
      $dropdowns['colors'][] = $row;
    }
  }

  // Fetch vehicle statuses
  $statusSql = "SELECT id, name, css_class FROM vehicle_status ORDER BY display_order, name";
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

// Function to get models by make ID (for AJAX)
function getModelsByMakeId($makeId)
{
  $conn = getConnection();
  $models = [];

  $stmt = $conn->prepare("SELECT id, name FROM models WHERE make_id = ? ORDER BY display_order, name");
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

// Get all dropdown options
$dropdowns = getDropdownOptions();

// Load all models data for client-side filtering
$allModels = [];
foreach ($dropdowns['makes'] as $make) {
  $makeId = $make['id'];
  $allModels[$makeId] = getModelsByMakeId($makeId);
}
?>
<!doctype html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="../src/output.css" rel="stylesheet">
  <title>CentralAutogy - Car Inventory Management</title>
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
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Vehicle Management Dashboard</h1>
        <p class="text-gray-600">Monitor and manage your car inventory</p>
      </div>

      <?php include 'includes/stats.php'; ?>

      <!-- Recent Inventory & Add New Car -->
      <div class="flex flex-col lg:flex-row gap-6">
        <!-- Car Inventory Table -->
        <div class="dashboard-card bg-white p-6 w-full lg:w-3/4">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <h2 class="text-xl font-bold text-gray-800">Recent Inventory</h2>
            <button id="addNewCarBtn" class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white px-4 py-2 rounded-lg transition duration-300 flex items-center shadow-md">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
              </svg>
              Add New Vehicle
            </button>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="bg-gray-50">
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-600 rounded-tl-lg">Car Name</th>
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Year/Make</th>
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Mileage</th>
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Fuel Type</th>
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Status</th>
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-600 rounded-tr-lg">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr class="border-b border-gray-100 table-row">
                  <td class="px-4 py-3 text-gray-800">Toyota Camry</td>
                  <td class="px-4 py-3 text-gray-800">2022 Toyota</td>
                  <td class="px-4 py-3 text-gray-800">12,450</td>
                  <td class="px-4 py-3 text-gray-800">Gasoline</td>
                  <td class="px-4 py-3">
                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">Available</span>
                  </td>
                  <td class="px-4 py-3">
                    <div class="flex space-x-2">
                      <button class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-all" title="Edit">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                          <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                      </button>
                      <button class="p-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-all" title="Delete">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr class="border-b border-gray-100 table-row">
                  <td class="px-4 py-3 text-gray-800">Honda Civic</td>
                  <td class="px-4 py-3 text-gray-800">2021 Honda</td>
                  <td class="px-4 py-3 text-gray-800">18,700</td>
                  <td class="px-4 py-3 text-gray-800">Gasoline</td>
                  <td class="px-4 py-3">
                    <span class="px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-medium">In Transit</span>
                  </td>
                  <td class="px-4 py-3">
                    <div class="flex space-x-2">
                      <button class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-all" title="Edit">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                          <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                      </button>
                      <button class="p-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-all" title="Delete">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr class="border-b border-gray-100 table-row">
                  <td class="px-4 py-3 text-gray-800">Tesla Model 3</td>
                  <td class="px-4 py-3 text-gray-800">2023 Tesla</td>
                  <td class="px-4 py-3 text-gray-800">2,100</td>
                  <td class="px-4 py-3 text-gray-800">Electric</td>
                  <td class="px-4 py-3">
                    <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-medium">Sold</span>
                  </td>
                  <td class="px-4 py-3">
                    <div class="flex space-x-2">
                      <button class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-all" title="Edit">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                          <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                      </button>
                      <button class="p-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-all" title="Delete">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr class="border-b border-gray-100 table-row">
                  <td class="px-4 py-3 text-gray-800">Ford Mustang</td>
                  <td class="px-4 py-3 text-gray-800">2022 Ford</td>
                  <td class="px-4 py-3 text-gray-800">8,900</td>
                  <td class="px-4 py-3 text-gray-800">Gasoline</td>
                  <td class="px-4 py-3">
                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">Available</span>
                  </td>
                  <td class="px-4 py-3">
                    <div class="flex space-x-2">
                      <button class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-all" title="Edit">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                          <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                      </button>
                      <button class="p-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-all" title="Delete">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr class="border-b border-gray-100 table-row">
                  <td class="px-4 py-3 text-gray-800">BMW X5</td>
                  <td class="px-4 py-3 text-gray-800">2023 BMW</td>
                  <td class="px-4 py-3 text-gray-800">3,200</td>
                  <td class="px-4 py-3 text-gray-800">Diesel</td>
                  <td class="px-4 py-3">
                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">Available</span>
                  </td>
                  <td class="px-4 py-3">
                    <div class="flex space-x-2">
                      <button class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-all" title="Edit">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                          <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                      </button>
                      <button class="p-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-all" title="Delete">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="mt-6 flex justify-between items-center">
            <div>
              <span class="text-sm text-gray-600">Showing 5 of 152 vehicles</span>
            </div>
            <div class="flex space-x-1">
              <button class="px-3 py-1.5 rounded-md bg-white border border-gray-200 text-gray-600 text-sm hover:bg-gray-50 transition-all">Previous</button>
              <button class="px-3 py-1.5 rounded-md bg-indigo-600 text-white text-sm hover:bg-indigo-700 transition-all">1</button>
              <button class="px-3 py-1.5 rounded-md bg-white border border-gray-200 text-gray-600 text-sm hover:bg-gray-50 transition-all">2</button>
              <button class="px-3 py-1.5 rounded-md bg-white border border-gray-200 text-gray-600 text-sm hover:bg-gray-50 transition-all">3</button>
              <button class="px-3 py-1.5 rounded-md bg-white border border-gray-200 text-gray-600 text-sm hover:bg-gray-50 transition-all">Next</button>
            </div>
          </div>
        </div>

        <!-- Add New Car Form -->
        <div class="dashboard-card bg-white p-6 w-full lg:w-1/4">
          <h2 class="text-xl font-bold text-gray-800 mb-6">Quick Add Vehicle</h2>
          <form id="quickAddForm" class="space-y-4" method="post" action="process_add_vehicle.php">
            <div>
              <label for="make" class="block text-sm font-medium text-gray-700 mb-1">Make</label>
              <select id="make" name="make" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-700 text-sm">
                <option value="">Select Make</option>
                <?php foreach ($dropdowns['makes'] as $make): ?>
                  <option value="<?php echo $make['id']; ?>"><?php echo htmlspecialchars($make['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label for="model" class="block text-sm font-medium text-gray-700 mb-1">Model</label>
              <select id="model" name="model" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-700 text-sm">
                <option value="">Select Make First</option>
              </select>
            </div>
            <div>
              <label for="year" class="block text-sm font-medium text-gray-700 mb-1">Year</label>
              <input type="number" id="year" name="year" min="1900" max="<?php echo date('Y') + 1; ?>" placeholder="e.g. <?php echo date('Y'); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-700 text-sm">
            </div>
            <div>
              <label for="body_style" class="block text-sm font-medium text-gray-700 mb-1">Body Type</label>
              <select id="body_style" name="body_style" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-700 text-sm">
                <option value="">Select Body Type</option>
                <?php foreach ($dropdowns['bodyTypes'] as $bodyType): ?>
                  <option value="<?php echo $bodyType['id']; ?>"><?php echo htmlspecialchars($bodyType['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label for="mileage" class="block text-sm font-medium text-gray-700 mb-1">Mileage</label>
              <input type="text" id="mileage" name="mileage" placeholder="e.g. 15000" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-700 text-sm">
            </div>
            <div>
              <label for="fuel_type" class="block text-sm font-medium text-gray-700 mb-1">Fuel Type</label>
              <select id="fuel_type" name="fuel_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-700 text-sm">
                <option value="">Select Fuel Type</option>
                <?php foreach ($dropdowns['fuelTypes'] as $fuelType): ?>
                  <option value="<?php echo $fuelType['id']; ?>"><?php echo htmlspecialchars($fuelType['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label for="transmission" class="block text-sm font-medium text-gray-700 mb-1">Transmission</label>
              <select id="transmission" name="transmission" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-700 text-sm">
                <option value="">Select Transmission</option>
                <?php foreach ($dropdowns['transmissions'] as $transmission): ?>
                  <option value="<?php echo $transmission['id']; ?>"><?php echo htmlspecialchars($transmission['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-700 text-sm">
                <option value="">Select Status</option>
                <?php foreach ($dropdowns['statuses'] as $status): ?>
                  <option value="<?php echo $status['id']; ?>"><?php echo htmlspecialchars($status['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="pt-2">
              <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white py-2.5 px-4 rounded-lg transition duration-300 font-medium text-sm shadow-md">
                Add Vehicle
              </button>
            </div>
          </form>
        </div>
      </div>

    </main>
  </div>

  <!-- Add New Car Modal -->
  <div id="addCarModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
      <div class="bg-white rounded-xl w-full max-w-3xl mx-auto shadow-2xl modal-animation">
        <div class="flex justify-between items-center border-b p-6">
          <div class="flex items-center">
            <div class="bg-indigo-100 p-2 rounded-lg mr-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
                <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm7 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H14a1 1 0 001-1v-3h-5v-1h9V8h-1a1 1 0 00-1-1h-6a1 1 0 00-1 1v7.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1V5a1 1 0 00-1-1H3z" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-800">Add New Vehicle</h3>
          </div>
          <button id="closeModalBtn" class="text-gray-400 hover:text-gray-500 focus:outline-none transition-all p-1 hover:bg-gray-100 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="p-6 max-h-[70vh] overflow-y-auto">
          <form id="addCarForm" class="grid grid-cols-1 md:grid-cols-2 gap-6" method="post" action="process_add_vehicle.php" enctype="multipart/form-data">
            <div>
              <label for="modalMake" class="block text-sm font-medium text-gray-700 mb-1">Make</label>
              <select id="modalMake" name="make" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                <option value="">Select Make</option>
                <?php foreach ($dropdowns['makes'] as $make): ?>
                  <option value="<?php echo $make['id']; ?>"><?php echo htmlspecialchars($make['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label for="modalModel" class="block text-sm font-medium text-gray-700 mb-1">Model</label>
              <select id="modalModel" name="model" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                <option value="">Select Make First</option>
              </select>
            </div>
            <div>
              <label for="modalYear" class="block text-sm font-medium text-gray-700 mb-1">Year</label>
              <input type="number" id="modalYear" name="year" min="1900" max="<?php echo date('Y') + 1; ?>" placeholder="e.g. <?php echo date('Y'); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
            <div>
              <label for="modalBodyStyle" class="block text-sm font-medium text-gray-700 mb-1">Body Type</label>
              <select id="modalBodyStyle" name="body_style" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                <option value="">Select Body Type</option>
                <?php foreach ($dropdowns['bodyTypes'] as $bodyType): ?>
                  <option value="<?php echo $bodyType['id']; ?>"><?php echo htmlspecialchars($bodyType['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label for="modalMileage" class="block text-sm font-medium text-gray-700 mb-1">Mileage</label>
              <input type="text" id="modalMileage" name="mileage" placeholder="e.g. 15000" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
            <div>
              <label for="modalPrice" class="block text-sm font-medium text-gray-700 mb-1">Price</label>
              <input type="text" id="modalPrice" name="price" placeholder="e.g. 25000.00" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
            <div>
              <label for="modalVIN" class="block text-sm font-medium text-gray-700 mb-1">VIN</label>
              <input type="text" id="modalVIN" name="vin" placeholder="Vehicle Identification Number" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
            <div>
              <label for="modalFuelType" class="block text-sm font-medium text-gray-700 mb-1">Fuel Type</label>
              <select id="modalFuelType" name="fuel_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                <option value="">Select Fuel Type</option>
                <?php foreach ($dropdowns['fuelTypes'] as $fuelType): ?>
                  <option value="<?php echo $fuelType['id']; ?>"><?php echo htmlspecialchars($fuelType['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label for="modalTransmission" class="block text-sm font-medium text-gray-700 mb-1">Transmission</label>
              <select id="modalTransmission" name="transmission" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                <option value="">Select Transmission</option>
                <?php foreach ($dropdowns['transmissions'] as $transmission): ?>
                  <option value="<?php echo $transmission['id']; ?>"><?php echo htmlspecialchars($transmission['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label for="modalDrive" class="block text-sm font-medium text-gray-700 mb-1">Drive Type</label>
              <select id="modalDrive" name="drivetrain" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                <option value="">Select Drive Type</option>
                <?php foreach ($dropdowns['driveTypes'] as $driveType): ?>
                  <option value="<?php echo $driveType['id']; ?>"><?php echo htmlspecialchars($driveType['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label for="modalEngine" class="block text-sm font-medium text-gray-700 mb-1">Engine</label>
              <input type="text" id="modalEngine" name="engine" placeholder="e.g. 2.0L 4-Cylinder" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
            <div>
              <label for="modalExteriorColor" class="block text-sm font-medium text-gray-700 mb-1">Exterior Color</label>
              <select id="modalExteriorColor" name="exterior_color" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                <option value="">Select Exterior Color</option>
                <?php foreach ($dropdowns['colors'] as $color): ?>
                  <option value="<?php echo $color['id']; ?>"><?php echo htmlspecialchars($color['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label for="modalInteriorColor" class="block text-sm font-medium text-gray-700 mb-1">Interior Color</label>
              <select id="modalInteriorColor" name="interior_color" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                <option value="">Select Interior Color</option>
                <?php foreach ($dropdowns['colors'] as $color): ?>
                  <option value="<?php echo $color['id']; ?>"><?php echo htmlspecialchars($color['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label for="modalStatus" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select id="modalStatus" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                <option value="">Select Status</option>
                <?php foreach ($dropdowns['statuses'] as $status): ?>
                  <option value="<?php echo $status['id']; ?>"><?php echo htmlspecialchars($status['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label for="modalNotes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
              <textarea id="modalNotes" name="description" rows="2" placeholder="Additional information about the vehicle" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm resize-none"></textarea>
            </div>

            <div class="col-span-1 md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle Images</label>
              <div class="file-drop-area">
                <input type="file" id="modalImages" name="images[]" multiple class="file-input" onChange="updateFileNames()">
                <div class="flex flex-col items-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-3 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <p class="text-sm text-gray-600 mb-1 font-medium">Drag & drop vehicle images here</p>
                  <p class="text-xs text-gray-500">or click to browse files</p>
                </div>
                <div id="fileNames" class="mt-3 text-gray-600 text-xs space-y-1"></div>
              </div>
            </div>
          </form>
        </div>
        <div class="flex justify-end border-t p-6 space-x-3">
          <button id="cancelBtn" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-300 font-medium text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Cancel
          </button>
          <button id="saveVehicleBtn" class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 transition duration-300 font-medium text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-md">
            Save Vehicle
          </button>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      console.log('DOM content loaded');

      // Pre-loaded models data from PHP
      const allModels = <?php echo json_encode($allModels); ?>;
      console.log('All models object:', allModels);
      console.log('Keys in allModels:', Object.keys(allModels));

      // Check if the make dropdown exists
      const makeDropdown = document.getElementById('make');
      const modelDropdown = document.getElementById('model');

      console.log('Make dropdown found:', makeDropdown ? 'Yes' : 'No');
      console.log('Model dropdown found:', modelDropdown ? 'Yes' : 'No');

      if (makeDropdown) {
        console.log('Make dropdown options:', Array.from(makeDropdown.options).map(o => `${o.textContent} (value: '${o.value}')`));
      }

      if (makeDropdown && modelDropdown) {
        // IMPORTANT: Disable any existing event handlers that might be conflicting
        const newMakeDropdown = makeDropdown.cloneNode(true);
        makeDropdown.parentNode.replaceChild(newMakeDropdown, makeDropdown);

        // Add event listener to the new element
        newMakeDropdown.addEventListener('change', function() {
          const makeId = this.value;
          console.log('Make dropdown changed to:', makeId);
          console.log('Selected index:', this.selectedIndex);
          console.log('Selected option text:', this.options[this.selectedIndex].text);

          // Clear the model dropdown
          modelDropdown.innerHTML = '<option value="">Select Model</option>';

          // Check if make ID exists
          if (makeId) {
            console.log('Looking for models with make ID:', makeId);
            console.log('allModels has this key:', allModels.hasOwnProperty(makeId) ? 'Yes' : 'No');

            if (allModels[makeId]) {
              console.log('Found models:', allModels[makeId].length);

              // Add models to dropdown
              allModels[makeId].forEach(function(model) {
                const option = document.createElement('option');
                option.value = model.id;
                option.textContent = model.name;
                modelDropdown.appendChild(option);
                console.log('Added model option:', model.name, 'with ID:', model.id);
              });

              console.log('Final model dropdown options count:', modelDropdown.options.length);
            } else {
              console.error('No models found for make ID:', makeId);
              console.log('Available keys in allModels:', Object.keys(allModels));
              modelDropdown.innerHTML = '<option value="">No models available for this make</option>';
            }
          } else {
            console.log('No make selected (empty value)');
          }
        });

        // Force an initial triggering of the event for debugging
        console.log('Setting up initial make value for testing...');
        setTimeout(function() {
          if (newMakeDropdown.options.length > 1) {
            newMakeDropdown.value = newMakeDropdown.options[1].value; // Select first non-empty option
            console.log('Set make dropdown to:', newMakeDropdown.value);

            // Create and dispatch a change event
            const event = new Event('change');
            newMakeDropdown.dispatchEvent(event);
            console.log('Change event dispatched');
          }
        }, 500); // Short delay to ensure DOM is ready
      }

      // Same approach for modal dropdowns
      const modalMakeDropdown = document.getElementById('modalMake');
      const modalModelDropdown = document.getElementById('modalModel');

      console.log('Modal make dropdown found:', modalMakeDropdown ? 'Yes' : 'No');
      console.log('Modal model dropdown found:', modalModelDropdown ? 'Yes' : 'No');

      if (modalMakeDropdown && modalModelDropdown) {
        // IMPORTANT: Disable any existing event handlers
        const newModalMakeDropdown = modalMakeDropdown.cloneNode(true);
        modalMakeDropdown.parentNode.replaceChild(newModalMakeDropdown, modalMakeDropdown);

        // Add event listener to the new element
        newModalMakeDropdown.addEventListener('change', function() {
          const makeId = this.value;
          console.log('Modal make dropdown changed to:', makeId);

          // Clear the model dropdown
          modalModelDropdown.innerHTML = '<option value="">Select Model</option>';

          // Check if make ID exists
          if (makeId && allModels[makeId]) {
            console.log('Found models for modal:', allModels[makeId].length);

            // Add models to dropdown
            allModels[makeId].forEach(function(model) {
              const option = document.createElement('option');
              option.value = model.id;
              option.textContent = model.name;
              modalModelDropdown.appendChild(option);
              console.log('Added modal model option:', model.name);
            });
          } else if (makeId) {
            console.error('No models found for modal make ID:', makeId);
            modalModelDropdown.innerHTML = '<option value="">No models available for this make</option>';
          }
        });
      }

      // Modal controls
      const addNewCarBtn = document.getElementById('addNewCarBtn');
      const addCarModal = document.getElementById('addCarModal');
      const closeModalBtn = document.getElementById('closeModalBtn');
      const cancelBtn = document.getElementById('cancelBtn');
      const saveVehicleBtn = document.getElementById('saveVehicleBtn');
      const addCarForm = document.getElementById('addCarForm');

      if (addNewCarBtn && addCarModal && closeModalBtn && cancelBtn) {
        addNewCarBtn.addEventListener('click', function() {
          addCarModal.classList.remove('hidden');
        });

        [closeModalBtn, cancelBtn].forEach(btn => {
          btn.addEventListener('click', function() {
            addCarModal.classList.add('hidden');
          });
        });

        saveVehicleBtn.addEventListener('click', function() {
          addCarForm.submit();
        });
      }

      // File upload preview
      window.updateFileNames = function() {
        const fileInput = document.getElementById('modalImages');
        const fileNamesDiv = document.getElementById('fileNames');

        if (fileInput && fileNamesDiv) {
          fileNamesDiv.innerHTML = '';

          if (fileInput.files.length > 0) {
            Array.from(fileInput.files).forEach(file => {
              const fileNameEl = document.createElement('div');
              fileNameEl.textContent = file.name;
              fileNamesDiv.appendChild(fileNameEl);
            });
          }
        }
      };

      // Add a global helper function for debugging
      window.debugModels = function(makeId) {
        console.log('=== DEBUG MODELS ===');
        console.log('Requested make ID:', makeId);
        console.log('Available keys:', Object.keys(allModels));
        console.log('Models for this make:', allModels[makeId]);
        console.log('===================');
      };
    });
  </script>
  <script src="assets/js/index.js"></script>
</body>

</html>