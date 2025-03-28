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
  $makeId = (string)$make['id']; // Convert ID to string for JavaScript
  $allModels[$makeId] = getModelsByMakeId($make['id']);
}

// Pagination variables
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Items per page
$offset = ($page - 1) * $limit;

// Filtering parameters
$filterMake = isset($_GET['filter_make']) ? $_GET['filter_make'] : '';
$filterStatus = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';
$filterFuelType = isset($_GET['filter_fuel_type']) ? $_GET['filter_fuel_type'] : '';
$filterBodyStyle = isset($_GET['filter_body_style']) ? $_GET['filter_body_style'] : '';
$filterYearMin = isset($_GET['filter_year_min']) ? (int)$_GET['filter_year_min'] : '';
$filterYearMax = isset($_GET['filter_year_max']) ? (int)$_GET['filter_year_max'] : '';
$filterPriceMin = isset($_GET['filter_price_min']) ? (float)$_GET['filter_price_min'] : '';
$filterPriceMax = isset($_GET['filter_price_max']) ? (float)$_GET['filter_price_max'] : '';
$searchKeyword = isset($_GET['search']) ? trim($_GET['search']) : '';

// Sorting parameters
$sortField = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$sortOrder = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'ASC' : 'DESC';

// Function to get vehicles based on filters and pagination
function getVehicles($page, $limit, $filters, $sort, $order, $search)
{
  $conn = getConnection();
  $offset = ($page - 1) * $limit;

  // Base query
  $query = "SELECT 
      id, 
      CONCAT(make, ' ', model) AS vehicle_name,
      year, 
      price,
      mileage,
      body_style,
      fuel_type,
      transmission,
      status,
      DATE_FORMAT(created_at, '%Y-%m-%d') AS date_added
  FROM 
      vehicles
  WHERE 1=1";

  $countQuery = "SELECT COUNT(*) as total FROM vehicles WHERE 1=1";

  $params = [];
  $types = "";

  // Add filters to query
  if (!empty($filters['make'])) {
    $query .= " AND make = ?";
    $countQuery .= " AND make = ?";
    $params[] = $filters['make'];
    $types .= "s";
  }

  if (!empty($filters['status'])) {
    $query .= " AND status = ?";
    $countQuery .= " AND status = ?";
    $params[] = $filters['status'];
    $types .= "s";
  }

  if (!empty($filters['fuel_type'])) {
    $query .= " AND fuel_type = ?";
    $countQuery .= " AND fuel_type = ?";
    $params[] = $filters['fuel_type'];
    $types .= "s";
  }

  if (!empty($filters['body_style'])) {
    $query .= " AND body_style = ?";
    $countQuery .= " AND body_style = ?";
    $params[] = $filters['body_style'];
    $types .= "s";
  }

  if (!empty($filters['year_min'])) {
    $query .= " AND year >= ?";
    $countQuery .= " AND year >= ?";
    $params[] = $filters['year_min'];
    $types .= "i";
  }

  if (!empty($filters['year_max'])) {
    $query .= " AND year <= ?";
    $countQuery .= " AND year <= ?";
    $params[] = $filters['year_max'];
    $types .= "i";
  }

  if (!empty($filters['price_min'])) {
    $query .= " AND price >= ?";
    $countQuery .= " AND price >= ?";
    $params[] = $filters['price_min'];
    $types .= "d";
  }

  if (!empty($filters['price_max'])) {
    $query .= " AND price <= ?";
    $countQuery .= " AND price <= ?";
    $params[] = $filters['price_max'];
    $types .= "d";
  }

  if (!empty($search)) {
    $searchParam = "%{$search}%";
    $query .= " AND (make LIKE ? OR model LIKE ? OR CONCAT(make, ' ', model) LIKE ? OR vin LIKE ?)";
    $countQuery .= " AND (make LIKE ? OR model LIKE ? OR CONCAT(make, ' ', model) LIKE ? OR vin LIKE ?)";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ssss";
  }

  // Add sorting
  $validSortFields = ['vehicle_name', 'year', 'price', 'mileage', 'status', 'date_added'];
  $sortField = in_array($sort, $validSortFields) ? $sort : 'created_at';
  $sortOrder = $order === 'ASC' ? 'ASC' : 'DESC';

  if ($sortField === 'vehicle_name') {
    $query .= " ORDER BY make $sortOrder, model $sortOrder";
  } else {
    $query .= " ORDER BY $sortField $sortOrder";
  }

  // Add pagination
  $query .= " LIMIT ?, ?";
  $params[] = $offset;
  $params[] = $limit;
  $types .= "ii";

  // Get total count (for pagination)
  $countStmt = $conn->prepare($countQuery);
  if (!empty($params) && !empty(substr($types, 0, -2))) {
    $countStmt->bind_param(substr($types, 0, -2), ...array_slice($params, 0, -2));
  }
  $countStmt->execute();
  $totalResult = $countStmt->get_result();
  $totalRow = $totalResult->fetch_assoc();
  $total = $totalRow['total'];
  $countStmt->close();

  // Get vehicles
  $stmt = $conn->prepare($query);
  if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
  }
  $stmt->execute();
  $result = $stmt->get_result();

  $vehicles = [];
  if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      // Map status to CSS class
      $statusClass = 'bg-gray-100 text-gray-800';
      switch (strtolower($row['status'])) {
        case 'available':
          $statusClass = 'bg-green-100 text-green-800';
          break;
        case 'sold':
          $statusClass = 'bg-purple-100 text-purple-800';
          break;
        case 'pending':
          $statusClass = 'bg-amber-100 text-amber-800';
          break;
        case 'reserved':
          $statusClass = 'bg-blue-100 text-blue-800';
          break;
      }

      // Add CSS class to the vehicle data
      $row['css_class'] = $statusClass;

      // Add to vehicles array
      $vehicles[] = $row;
    }
  }

  $stmt->close();
  $conn->close();

  return [
    'vehicles' => $vehicles,
    'total' => $total,
    'pages' => ceil($total / $limit)
  ];
}

// Create filters array for the query
$filters = [
  'make' => $filterMake,
  'status' => $filterStatus,
  'fuel_type' => $filterFuelType,
  'body_style' => $filterBodyStyle,
  'year_min' => $filterYearMin,
  'year_max' => $filterYearMax,
  'price_min' => $filterPriceMin,
  'price_max' => $filterPriceMax
];

// Get vehicles data
$result = getVehicles($page, $limit, $filters, $sortField, $sortOrder, $searchKeyword);
$vehicles = $result['vehicles'];
$totalVehicles = $result['total'];
$totalPages = $result['pages'];

// Get current year for the year filter
$currentYear = date('Y');
?>
<!doctype html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="../src/output.css" rel="stylesheet">
  <title>CentralAutogy - Vehicle Inventory</title>
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

      <!-- Page Title and Actions -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">Vehicle Inventory</h1>
          <p class="text-gray-600">Manage your entire vehicle inventory</p>
        </div>
        <div class="flex space-x-3">
          <a href="index.php" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-600 flex items-center hover:bg-gray-50 transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            Dashboard
          </a>
          <button id="addNewCarBtn" class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white px-4 py-2 rounded-lg transition duration-300 flex items-center shadow-md">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Add New Vehicle
          </button>
        </div>
      </div>

      <!-- Search and Filters -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form action="vehicles.php" method="GET" class="space-y-4">
          <div class="flex flex-col md:flex-row gap-4 md:items-end">
            <div class="flex-1">
              <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                  </svg>
                </div>
                <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($searchKeyword); ?>" class="pl-10 w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm" placeholder="Search by make, model, or VIN">
              </div>
            </div>
            <div>
              <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-all flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                </svg>
                Search
              </button>
            </div>
            <div>
              <button type="button" id="toggleFiltersBtn" class="w-full px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-all flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                </svg>
                Filters
              </button>
            </div>
            <?php if (!empty($filterMake) || !empty($filterStatus) || !empty($filterFuelType) || !empty($filterBodyStyle) || !empty($filterYearMin) || !empty($filterYearMax) || !empty($filterPriceMin) || !empty($filterPriceMax) || !empty($searchKeyword)): ?>
              <div>
                <a href="vehicles.php" class="w-full px-4 py-2 bg-red-50 text-red-600 border border-red-100 rounded-lg hover:bg-red-100 transition-all flex items-center justify-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                  </svg>
                  Clear Filters
                </a>
              </div>
            <?php endif; ?>
          </div>

          <div id="filterOptions" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-4 <?php echo empty($filterMake) && empty($filterStatus) && empty($filterFuelType) && empty($filterBodyStyle) && empty($filterYearMin) && empty($filterYearMax) && empty($filterPriceMin) && empty($filterPriceMax) ? 'hidden' : ''; ?>">
            <div>
              <label for="filter_make" class="block text-sm font-medium text-gray-700 mb-1">Make</label>
              <select id="filter_make" name="filter_make" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                <option value="">All Makes</option>
                <?php foreach ($dropdowns['makes'] as $make): ?>
                  <option value="<?php echo htmlspecialchars($make['name']); ?>" <?php echo $filterMake === $make['name'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($make['name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label for="filter_status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select id="filter_status" name="filter_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                <option value="">All Statuses</option>
                <?php foreach ($dropdowns['statuses'] as $status): ?>
                  <option value="<?php echo htmlspecialchars($status['name']); ?>" <?php echo $filterStatus === $status['name'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($status['name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label for="filter_fuel_type" class="block text-sm font-medium text-gray-700 mb-1">Fuel Type</label>
              <select id="filter_fuel_type" name="filter_fuel_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                <option value="">All Fuel Types</option>
                <?php foreach ($dropdowns['fuelTypes'] as $fuelType): ?>
                  <option value="<?php echo htmlspecialchars($fuelType['name']); ?>" <?php echo $filterFuelType === $fuelType['name'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($fuelType['name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label for="filter_body_style" class="block text-sm font-medium text-gray-700 mb-1">Body Type</label>
              <select id="filter_body_style" name="filter_body_style" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                <option value="">All Body Types</option>
                <?php foreach ($dropdowns['bodyTypes'] as $bodyType): ?>
                  <option value="<?php echo htmlspecialchars($bodyType['name']); ?>" <?php echo $filterBodyStyle === $bodyType['name'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($bodyType['name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label for="filter_year_min" class="block text-sm font-medium text-gray-700 mb-1">Year (Min)</label>
              <input type="number" id="filter_year_min" name="filter_year_min" min="1900" max="<?php echo $currentYear + 1; ?>" value="<?php echo $filterYearMin; ?>" placeholder="Min Year" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
            <div>
              <label for="filter_year_max" class="block text-sm font-medium text-gray-700 mb-1">Year (Max)</label>
              <input type="number" id="filter_year_max" name="filter_year_max" min="1900" max="<?php echo $currentYear + 1; ?>" value="<?php echo $filterYearMax; ?>" placeholder="Max Year" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
            <div>
              <label for="filter_price_min" class="block text-sm font-medium text-gray-700 mb-1">Price (Min)</label>
              <input type="number" id="filter_price_min" name="filter_price_min" min="0" step="0.01" value="<?php echo $filterPriceMin; ?>" placeholder="Min Price" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
            <div>
              <label for="filter_price_max" class="block text-sm font-medium text-gray-700 mb-1">Price (Max)</label>
              <input type="number" id="filter_price_max" name="filter_price_max" min="0" step="0.01" value="<?php echo $filterPriceMax; ?>" placeholder="Max Price" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
          </div>

          <!-- Hidden sort fields to maintain sort when filtering -->
          <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sortField); ?>">
          <input type="hidden" name="order" value="<?php echo htmlspecialchars($sortOrder); ?>">
        </form>
      </div>

      <!-- Vehicle Inventory Table -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
          <h2 class="text-xl font-bold text-gray-800">Vehicle Inventory</h2>
          <div class="text-sm text-gray-600">
            Showing <?php echo min($totalVehicles, $limit); ?> of <?php echo $totalVehicles; ?> vehicles
          </div>
        </div>

        <?php if (empty($vehicles)): ?>
          <div class="bg-yellow-50 p-4 rounded-lg mb-6">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">No vehicles found</h3>
                <div class="mt-2 text-sm text-yellow-700">
                  <p>No vehicles match your search criteria. Try adjusting your filters or adding new vehicles.</p>
                </div>
              </div>
            </div>
          </div>
        <?php else: ?>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="bg-gray-50">
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-600 rounded-tl-lg">
                    <a href="vehicles.php?sort=vehicle_name&order=<?php echo $sortField === 'vehicle_name' && $sortOrder === 'ASC' ? 'desc' : 'asc'; ?>&<?php echo http_build_query(array_filter(['search' => $searchKeyword, 'filter_make' => $filterMake, 'filter_status' => $filterStatus, 'filter_fuel_type' => $filterFuelType, 'filter_body_style' => $filterBodyStyle, 'filter_year_min' => $filterYearMin, 'filter_year_max' => $filterYearMax, 'filter_price_min' => $filterPriceMin, 'filter_price_max' => $filterPriceMax])); ?>" class="flex items-center">
                      Vehicle Name
                      <?php if ($sortField === 'vehicle_name'): ?>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20" fill="currentColor">
                          <?php if ($sortOrder === 'ASC'): ?>
                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                          <?php else: ?>
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                          <?php endif; ?>
                        </svg>
                      <?php endif; ?>
                    </a>
                  </th>
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">
                    <a href="vehicles.php?sort=year&order=<?php echo $sortField === 'year' && $sortOrder === 'ASC' ? 'desc' : 'asc'; ?>&<?php echo http_build_query(array_filter(['search' => $searchKeyword, 'filter_make' => $filterMake, 'filter_status' => $filterStatus, 'filter_fuel_type' => $filterFuelType, 'filter_body_style' => $filterBodyStyle, 'filter_year_min' => $filterYearMin, 'filter_year_max' => $filterYearMax, 'filter_price_min' => $filterPriceMin, 'filter_price_max' => $filterPriceMax])); ?>" class="flex items-center">
                      Year
                      <?php if ($sortField === 'year'): ?>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20" fill="currentColor">
                          <?php if ($sortOrder === 'ASC'): ?>
                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                          <?php else: ?>
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                          <?php endif; ?>
                        </svg>
                      <?php endif; ?>
                    </a>
                  </th>
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">
                    <a href="vehicles.php?sort=price&order=<?php echo $sortField === 'price' && $sortOrder === 'ASC' ? 'desc' : 'asc'; ?>&<?php echo http_build_query(array_filter(['search' => $searchKeyword, 'filter_make' => $filterMake, 'filter_status' => $filterStatus, 'filter_fuel_type' => $filterFuelType, 'filter_body_style' => $filterBodyStyle, 'filter_year_min' => $filterYearMin, 'filter_year_max' => $filterYearMax, 'filter_price_min' => $filterPriceMin, 'filter_price_max' => $filterPriceMax])); ?>" class="flex items-center">
                      Price
                      <?php if ($sortField === 'price'): ?>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20" fill="currentColor">
                          <?php if ($sortOrder === 'ASC'): ?>
                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                          <?php else: ?>
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                          <?php endif; ?>
                        </svg>
                      <?php endif; ?>
                    </a>
                  </th>
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">
                    <a href="vehicles.php?sort=mileage&order=<?php echo $sortField === 'mileage' && $sortOrder === 'ASC' ? 'desc' : 'asc'; ?>&<?php echo http_build_query(array_filter(['search' => $searchKeyword, 'filter_make' => $filterMake, 'filter_status' => $filterStatus, 'filter_fuel_type' => $filterFuelType, 'filter_body_style' => $filterBodyStyle, 'filter_year_min' => $filterYearMin, 'filter_year_max' => $filterYearMax, 'filter_price_min' => $filterPriceMin, 'filter_price_max' => $filterPriceMax])); ?>" class="flex items-center">
                      Mileage
                      <?php if ($sortField === 'mileage'): ?>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20" fill="currentColor">
                          <?php if ($sortOrder === 'ASC'): ?>
                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                          <?php else: ?>
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                          <?php endif; ?>
                        </svg>
                      <?php endif; ?>
                    </a>
                  </th>
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Body Style</th>
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Fuel Type</th>
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Transmission</th>
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">
                    <a href="vehicles.php?sort=status&order=<?php echo $sortField === 'status' && $sortOrder === 'ASC' ? 'desc' : 'asc'; ?>&<?php echo http_build_query(array_filter(['search' => $searchKeyword, 'filter_make' => $filterMake, 'filter_status' => $filterStatus, 'filter_fuel_type' => $filterFuelType, 'filter_body_style' => $filterBodyStyle, 'filter_year_min' => $filterYearMin, 'filter_year_max' => $filterYearMax, 'filter_price_min' => $filterPriceMin, 'filter_price_max' => $filterPriceMax])); ?>" class="flex items-center">
                      Status
                      <?php if ($sortField === 'status'): ?>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20" fill="currentColor">
                          <?php if ($sortOrder === 'ASC'): ?>
                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                          <?php else: ?>
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                          <?php endif; ?>
                        </svg>
                      <?php endif; ?>
                    </a>
                  </th>
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">
                    <a href="vehicles.php?sort=date_added&order=<?php echo $sortField === 'date_added' && $sortOrder === 'ASC' ? 'desc' : 'asc'; ?>&<?php echo http_build_query(array_filter(['search' => $searchKeyword, 'filter_make' => $filterMake, 'filter_status' => $filterStatus, 'filter_fuel_type' => $filterFuelType, 'filter_body_style' => $filterBodyStyle, 'filter_year_min' => $filterYearMin, 'filter_year_max' => $filterYearMax, 'filter_price_min' => $filterPriceMin, 'filter_price_max' => $filterPriceMax])); ?>" class="flex items-center">
                      Date Added
                      <?php if ($sortField === 'date_added'): ?>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20" fill="currentColor">
                          <?php if ($sortOrder === 'ASC'): ?>
                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                          <?php else: ?>
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                          <?php endif; ?>
                        </svg>
                      <?php endif; ?>
                    </a>
                  </th>
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-600 rounded-tr-lg">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($vehicles as $vehicle): ?>
                  <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-800 font-medium"><?php echo htmlspecialchars($vehicle['vehicle_name']); ?></td>
                    <td class="px-4 py-3 text-gray-800"><?php echo htmlspecialchars($vehicle['year']); ?></td>
                    <td class="px-4 py-3 text-gray-800">$<?php echo number_format($vehicle['price'], 2); ?></td>
                    <td class="px-4 py-3 text-gray-800"><?php echo number_format($vehicle['mileage']); ?></td>
                    <td class="px-4 py-3 text-gray-800"><?php echo htmlspecialchars($vehicle['body_style']); ?></td>
                    <td class="px-4 py-3 text-gray-800"><?php echo htmlspecialchars($vehicle['fuel_type']); ?></td>
                    <td class="px-4 py-3 text-gray-800"><?php echo htmlspecialchars($vehicle['transmission']); ?></td>
                    <td class="px-4 py-3">
                      <span class="px-3 py-1 <?php echo htmlspecialchars($vehicle['css_class']); ?> rounded-full text-xs font-medium">
                        <?php echo htmlspecialchars($vehicle['status']); ?>
                      </span>
                    </td>
                    <td class="px-4 py-3 text-gray-800"><?php echo htmlspecialchars($vehicle['date_added']); ?></td>
                    <td class="px-4 py-3">
                      <div class="flex space-x-2">
                        <a href="edit_vehicle_form.php?id=<?php echo $vehicle['id']; ?>" class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-all" title="Edit">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                          </svg>
                        </a>
                        <button onclick="confirmDelete(<?php echo $vehicle['id']; ?>, '<?php echo addslashes($vehicle['year'] . ' ' . $vehicle['vehicle_name']); ?>')" class="p-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-all" title="Delete">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                          </svg>
                        </button>
                        <a href="vehicle_details.php?id=<?php echo $vehicle['id']; ?>" class="p-1.5 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 transition-all" title="View Details">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                          </svg>
                        </a>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <?php if ($totalPages > 1): ?>
            <div class="mt-6 flex justify-between items-center">
              <div class="text-sm text-gray-600">
                Page <?php echo $page; ?> of <?php echo $totalPages; ?>
              </div>
              <div class="flex space-x-1">
                <?php if ($page > 1): ?>
                  <a href="vehicles.php?page=<?php echo $page - 1; ?>&sort=<?php echo $sortField; ?>&order=<?php echo $sortOrder; ?>&<?php echo http_build_query(array_filter(['search' => $searchKeyword, 'filter_make' => $filterMake, 'filter_status' => $filterStatus, 'filter_fuel_type' => $filterFuelType, 'filter_body_style' => $filterBodyStyle, 'filter_year_min' => $filterYearMin, 'filter_year_max' => $filterYearMax, 'filter_price_min' => $filterPriceMin, 'filter_price_max' => $filterPriceMax])); ?>" class="px-3 py-1.5 rounded-md bg-white border border-gray-200 text-gray-600 text-sm hover:bg-gray-50 transition-all">
                    Previous
                  </a>
                <?php endif; ?>

                <?php
                // Show max 5 page numbers
                $startPage = max(1, min($page - 2, $totalPages - 4));
                $endPage = min($startPage + 4, $totalPages);

                for ($i = $startPage; $i <= $endPage; $i++):
                ?>
                  <a href="vehicles.php?page=<?php echo $i; ?>&sort=<?php echo $sortField; ?>&order=<?php echo $sortOrder; ?>&<?php echo http_build_query(array_filter(['search' => $searchKeyword, 'filter_make' => $filterMake, 'filter_status' => $filterStatus, 'filter_fuel_type' => $filterFuelType, 'filter_body_style' => $filterBodyStyle, 'filter_year_min' => $filterYearMin, 'filter_year_max' => $filterYearMax, 'filter_price_min' => $filterPriceMin, 'filter_price_max' => $filterPriceMax])); ?>" class="px-3 py-1.5 rounded-md <?php echo $i === $page ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'; ?> text-sm transition-all">
                    <?php echo $i; ?>
                  </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                  <a href="vehicles.php?page=<?php echo $page + 1; ?>&sort=<?php echo $sortField; ?>&order=<?php echo $sortOrder; ?>&<?php echo http_build_query(array_filter(['search' => $searchKeyword, 'filter_make' => $filterMake, 'filter_status' => $filterStatus, 'filter_fuel_type' => $filterFuelType, 'filter_body_style' => $filterBodyStyle, 'filter_year_min' => $filterYearMin, 'filter_year_max' => $filterYearMax, 'filter_price_min' => $filterPriceMin, 'filter_price_max' => $filterPriceMax])); ?>" class="px-3 py-1.5 rounded-md bg-white border border-gray-200 text-gray-600 text-sm hover:bg-gray-50 transition-all">
                    Next
                  </a>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </main>
  </div>

  <!-- Add New Car Modal (Reusing from index.php) -->
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a20 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <p class="text-sm text-gray-600 mb-1 font-medium">Drag & drop vehicle images here</p>
                  <p class="text-xs text-gray-500">or click to browse files</p>
                </div>
                <div id="fileNames" class="mt-3 text-gray-600 text-xs space-y-1"></div>
              </div>
              <p class="text-xs text-gray-500 mt-2">Maximum file size: 5MB. Accepted formats: JPG, JPEG, PNG, WEBP</p>
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

  <!-- Delete Confirmation Modal -->
  <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
      <div class="bg-white rounded-xl w-full max-w-md mx-auto shadow-2xl modal-animation">
        <div class="p-6">
          <div class="flex items-center justify-center mb-4">
            <div class="bg-red-100 rounded-full p-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-600" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
              </svg>
            </div>
          </div>
          <h3 class="text-xl font-bold text-center text-gray-800 mb-4">Confirm Deletion</h3>
          <p class="text-center text-gray-600 mb-6" id="deleteMessage">Are you sure you want to delete this vehicle? This action cannot be undone.</p>
          <div class="flex justify-center space-x-3">
            <button id="cancelDeleteBtn" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-300 font-medium text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
              Cancel
            </button>
            <form id="deleteForm" action="process_delete_vehicle.php" method="post">
              <input type="hidden" id="deleteVehicleId" name="vehicle_id" value="">
              <button type="submit" class="px-5 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-300 font-medium text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 shadow-md">
                Delete Vehicle
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Store models data globally with explicit object conversion
      window.allModels = JSON.parse('<?php echo json_encode($allModels, JSON_HEX_APOS); ?>');

      // Toggle filters visibility
      const toggleFiltersBtn = document.getElementById('toggleFiltersBtn');
      const filterOptions = document.getElementById('filterOptions');

      if (toggleFiltersBtn && filterOptions) {
        toggleFiltersBtn.addEventListener('click', function() {
          filterOptions.classList.toggle('hidden');
        });
      }

      // Model dropdown population
      function updateModelDropdown(makeId, modelDropdown) {
        // Clear current options
        modelDropdown.innerHTML = '<option value="">Select Model</option>';

        if (!makeId) return;

        // Ensure makeId is treated as a string for consistent lookup
        const makeIdStr = makeId.toString();

        // Check if models exist for this make and is an array
        if (window.allModels[makeIdStr] && Array.isArray(window.allModels[makeIdStr]) && window.allModels[makeIdStr].length > 0) {
          // Add models to dropdown
          for (let i = 0; i < window.allModels[makeIdStr].length; i++) {
            const model = window.allModels[makeIdStr][i];
            const option = document.createElement('option');
            option.value = model.id;
            option.textContent = model.name;
            modelDropdown.appendChild(option);
          }
        } else {
          modelDropdown.innerHTML = '<option value="">No models available for this make</option>';
        }
      }

      // Modal make and model selections
      const modalMakeDropdown = document.getElementById('modalMake');
      const modalModelDropdown = document.getElementById('modalModel');

      if (modalMakeDropdown && modalModelDropdown) {
        modalMakeDropdown.addEventListener('change', function() {
          updateModelDropdown(this.value, modalModelDropdown);
        });
      }

      // Modal controls
      const addNewCarBtn = document.getElementById('addNewCarBtn');
      const addCarModal = document.getElementById('addCarModal');
      const closeModalBtn = document.getElementById('closeModalBtn');
      const cancelBtn = document.getElementById('cancelBtn');
      const saveVehicleBtn = document.getElementById('saveVehicleBtn');
      const addCarForm = document.getElementById('addCarForm');

      if (addNewCarBtn && addCarModal) {
        addNewCarBtn.addEventListener('click', function() {
          addCarModal.classList.remove('hidden');
        });

        if (closeModalBtn) {
          closeModalBtn.addEventListener('click', function() {
            addCarModal.classList.add('hidden');
          });
        }

        if (cancelBtn) {
          cancelBtn.addEventListener('click', function() {
            addCarModal.classList.add('hidden');
          });
        }

        if (saveVehicleBtn && addCarForm) {
          saveVehicleBtn.addEventListener('click', function() {
            addCarForm.submit();
          });
        }
      }

      // Delete confirmation modal
      const deleteModal = document.getElementById('deleteModal');
      const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
      const deleteForm = document.getElementById('deleteForm');
      const deleteVehicleId = document.getElementById('deleteVehicleId');
      const deleteMessage = document.getElementById('deleteMessage');

      window.confirmDelete = function(id, vehicleName) {
        if (deleteModal && deleteVehicleId && deleteMessage) {
          deleteVehicleId.value = id;
          deleteMessage.textContent = `Are you sure you want to delete ${vehicleName}? This action cannot be undone.`;
          deleteModal.classList.remove('hidden');
        }
      };

      if (cancelDeleteBtn) {
        cancelDeleteBtn.addEventListener('click', function() {
          deleteModal.classList.add('hidden');
        });
      }
    });
  </script>
</body>

</html>