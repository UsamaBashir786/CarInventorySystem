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
                      <a href="vehicles.php?sort=vehicle_name&order=<?php echo $sortField === 'vehicle_name' && $sortOrder === 'ASC' ? 'desc' : 'asc'; ?>&<?php echo http_build_query(array_filter(['search' => $searchKeyword, 'filter_make' => $filterMake, 'filter_status' => $filterStatus, 'filter_fuel_type' => $filterFuelType, 'filter_body_style' => $filterBodyStyle, 'filter_year_min' => $filterYearMin, 'filter_year_max' => $filterYearMax, 'filter_price_min' => $filterPriceMin, 'filter_price_max' => $filterPriceMax])); ?>" class="flex items-center hover:text-indigo-700 <?php echo $sortField === 'vehicle_name' ? 'text-indigo-700 font-semibold' : ''; ?>">
                        <span>Vehicle</span>
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
                      <a href="vehicles.php?sort=year&order=<?php echo $sortField === 'year' && $sortOrder === 'ASC' ? 'desc' : 'asc'; ?>&<?php echo http_build_query(array_filter(['search' => $searchKeyword, 'filter_make' => $filterMake, 'filter_status' => $filterStatus, 'filter_fuel_type' => $filterFuelType, 'filter_body_style' => $filterBodyStyle, 'filter_year_min' => $filterYearMin, 'filter_year_max' => $filterYearMax, 'filter_price_min' => $filterPriceMin, 'filter_price_max' => $filterPriceMax])); ?>" class="flex items-center hover:text-indigo-700 <?php echo $sortField === 'year' ? 'text-indigo-700 font-semibold' : ''; ?>">
                        <span>Year</span>
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
                      <a href="vehicles.php?sort=price&order=<?php echo $sortField === 'price' && $sortOrder === 'ASC' ? 'desc' : 'asc'; ?>&<?php echo http_build_query(array_filter(['search' => $searchKeyword, 'filter_make' => $filterMake, 'filter_status' => $filterStatus, 'filter_fuel_type' => $filterFuelType, 'filter_body_style' => $filterBodyStyle, 'filter_year_min' => $filterYearMin, 'filter_year_max' => $filterYearMax, 'filter_price_min' => $filterPriceMin, 'filter_price_max' => $filterPriceMax])); ?>" class="flex items-center hover:text-indigo-700 <?php echo $sortField === 'price' ? 'text-indigo-700 font-semibold' : ''; ?>">
                        <span>Price</span>
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
                      <a href="vehicles.php?sort=mileage&order=<?php echo $sortField === 'mileage' && $sortOrder === 'ASC' ? 'desc' : 'asc'; ?>&<?php echo http_build_query(array_filter(['search' => $searchKeyword, 'filter_make' => $filterMake, 'filter_status' => $filterStatus, 'filter_fuel_type' => $filterFuelType, 'filter_body_style' => $filterBodyStyle, 'filter_year_min' => $filterYearMin, 'filter_year_max' => $filterYearMax, 'filter_price_min' => $filterPriceMin, 'filter_price_max' => $filterPriceMax])); ?>" class="flex items-center hover:text-indigo-700 <?php echo $sortField === 'mileage' ? 'text-indigo-700 font-semibold' : ''; ?>">
                        <span>Mileage</span>
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
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Status</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600 rounded-tr-lg">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($vehicles as $vehicle): ?>
                    <tr class="border-t border-gray-200 hover:bg-gray-50 transition duration-150">
                      <td class="px-4 py-3 text-gray-800 font-medium"><?php echo htmlspecialchars($vehicle['vehicle_name']); ?></td>
                      <td class="px-4 py-3 text-gray-800"><?php echo $vehicle['year']; ?></td>
                      <td class="px-4 py-3 text-gray-800">
                        <?php if ($vehicle['price'] !== null && $vehicle['price'] != 0): ?>
                          $<?php echo number_format($vehicle['price'], 2); ?>
                        <?php else: ?>
                          <span class="text-gray-500 italic">Contact for price</span>
                        <?php endif; ?>
                      </td>
                      <td class="px-4 py-3 text-gray-800"><?php echo number_format($vehicle['mileage']); ?> mi</td>
                      <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded-full text-xs font-medium <?php echo $vehicle['css_class']; ?>">
                          <?php echo htmlspecialchars($vehicle['status']); ?>
                        </span>
                      </td>
                      <td class="px-4 py-3">
                        <div class="flex space-x-2">
                          <a href="vehicle_details.php?id=<?php echo $vehicle['id']; ?>" class="px-2 py-1 bg-indigo-50 text-indigo-600 rounded hover:bg-indigo-100 transition-all text-sm flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                              <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                              <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                            </svg>
                            View
                          </a>
                          <a href="edit_vehicle_form.php?id=<?php echo $vehicle['id']; ?>" class="px-2 py-1 bg-amber-50 text-amber-600 rounded hover:bg-amber-100 transition-all text-sm flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                              <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                            </svg>
                            Edit
                          </a>
                          <button class="px-2 py-1 bg-red-50 text-red-600 rounded hover:bg-red-100 transition-all text-sm flex items-center delete-vehicle" data-id="<?php echo $vehicle['id']; ?>" data-name="<?php echo htmlspecialchars($vehicle['vehicle_name'] . ' ' . $vehicle['year']); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                              <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            Delete
                          </button>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
              <div class="flex justify-center mt-6">
                <nav class="inline-flex rounded-md shadow">
                  <?php if ($page > 1): ?>
                    <a href="vehicles.php?page=<?php echo $page - 1; ?>&<?php echo http_build_query(array_filter(['search' => $searchKeyword, 'filter_make' => $filterMake, 'filter_status' => $filterStatus, 'filter_fuel_type' => $filterFuelType, 'filter_body_style' => $filterBodyStyle, 'filter_year_min' => $filterYearMin, 'filter_year_max' => $filterYearMax, 'filter_price_min' => $filterPriceMin, 'filter_price_max' => $filterPriceMax, 'sort' => $sortField, 'order' => $sortOrder])); ?>" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50">
                      Previous
                    </a>
                  <?php else: ?>
                    <span class="px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-l-md cursor-not-allowed">
                      Previous
                    </span>
                  <?php endif; ?>

                  <?php
                  $startPage = max(1, min($page - 2, $totalPages - 4));
                  $endPage = min($totalPages, max($page + 2, 5));

                  for ($i = $startPage; $i <= $endPage; $i++):
                  ?>
                    <a href="vehicles.php?page=<?php echo $i; ?>&<?php echo http_build_query(array_filter(['search' => $searchKeyword, 'filter_make' => $filterMake, 'filter_status' => $filterStatus, 'filter_fuel_type' => $filterFuelType, 'filter_body_style' => $filterBodyStyle, 'filter_year_min' => $filterYearMin, 'filter_year_max' => $filterYearMax, 'filter_price_min' => $filterPriceMin, 'filter_price_max' => $filterPriceMax, 'sort' => $sortField, 'order' => $sortOrder])); ?>" class="px-4 py-2 text-sm font-medium <?php echo $i === $page ? 'text-indigo-700 bg-indigo-50 border-indigo-100' : 'text-gray-700 bg-white hover:bg-gray-50 border-gray-300'; ?> border-t border-b <?php echo $i === $startPage ? 'border-l' : ''; ?> <?php echo $i === $endPage ? 'border-r' : ''; ?>">
                      <?php echo $i; ?>
                    </a>
                  <?php endfor; ?>

                  <?php if ($page < $totalPages): ?>
                    <a href="vehicles.php?page=<?php echo $page + 1; ?>&<?php echo http_build_query(array_filter(['search' => $searchKeyword, 'filter_make' => $filterMake, 'filter_status' => $filterStatus, 'filter_fuel_type' => $filterFuelType, 'filter_body_style' => $filterBodyStyle, 'filter_year_min' => $filterYearMin, 'filter_year_max' => $filterYearMax, 'filter_price_min' => $filterPriceMin, 'filter_price_max' => $filterPriceMax, 'sort' => $sortField, 'order' => $sortOrder])); ?>" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50">
                      Next
                    </a>
                  <?php else: ?>
                    <span class="px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-r-md cursor-not-allowed">
                      Next
                    </span>
                  <?php endif; ?>
                </nav>
              </div>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </main>
    </div>

    <!-- Delete Vehicle Confirmation Modal -->
    <div id="deleteVehicleModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
      <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
          <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                  Delete Vehicle
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500" id="deleteVehicleMessage">
                    Are you sure you want to delete this vehicle? This action cannot be undone.
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <form id="deleteVehicleForm" action="process_delete_vehicle.php" method="POST">
              <input type="hidden" id="deleteVehicleId" name="vehicle_id" value="">
              <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                Delete
              </button>
            </form>
            <button type="button" id="cancelDeleteBtn" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add New Vehicle Modal -->
    <div id="addVehicleModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
      <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
          <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
            <div class="sm:flex sm:items-start">
              <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                  Add New Vehicle
                </h3>

                <form id="addVehicleForm" action="process_add_vehicle.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label for="modalMake" class="block text-sm font-medium text-gray-700 mb-1">Make</label>
                      <select id="modalMake" name="make" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                        <option value="">Select Make</option>
                        <?php foreach ($dropdowns['makes'] as $make): ?>
                          <option value="<?php echo $make['id']; ?>"><?php echo htmlspecialchars($make['name']); ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div>
                      <label for="modalModel" class="block text-sm font-medium text-gray-700 mb-1">Model</label>
                      <select id="modalModel" name="model" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                        <option value="">Select Model</option>
                        <!-- Models will be loaded dynamically based on selected make -->
                      </select>
                    </div>
                  </div>

                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label for="modalYear" class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                      <input type="number" id="modalYear" name="year" required min="1900" max="<?php echo date('Y') + 1; ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                    </div>
                    <div>
                      <label for="modalVin" class="block text-sm font-medium text-gray-700 mb-1">VIN</label>
                      <input type="text" id="modalVin" name="vin" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                    </div>
                  </div>

                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label for="modalPrice" class="block text-sm font-medium text-gray-700 mb-1">Price (Optional)</label>
                      <input type="text" id="modalPrice" name="price" placeholder="Leave empty for no price" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                      <p class="text-xs text-gray-500 mt-1">Leave empty if price is not available or negotiable</p>
                    </div>
                    <div>
                      <label for="modalMileage" class="block text-sm font-medium text-gray-700 mb-1">Mileage</label>
                      <input type="text" id="modalMileage" name="mileage" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                    </div>
                  </div>

                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label for="modalBodyStyle" class="block text-sm font-medium text-gray-700 mb-1">Body Type</label>
                      <select id="modalBodyStyle" name="body_style" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                        <option value="">Select Body Type</option>
                        <?php foreach ($dropdowns['bodyTypes'] as $bodyType): ?>
                          <option value="<?php echo $bodyType['id']; ?>"><?php echo htmlspecialchars($bodyType['name']); ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div>
                      <label for="modalStatus" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                      <select id="modalStatus" name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                        <option value="">Select Status</option>
                        <?php foreach ($dropdowns['statuses'] as $status): ?>
                          <option value="<?php echo $status['id']; ?>"><?php echo htmlspecialchars($status['name']); ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>

                  <div class="grid grid-cols-1 gap-4">
                    <div>
                      <label for="modalImages" class="block text-sm font-medium text-gray-700 mb-1">Images (Optional)</label>
                      <input type="file" id="modalImages" name="images[]" multiple class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                      <p class="text-xs text-gray-500 mt-1">You can select multiple images. Maximum 5MB per image. Formats: JPG, JPEG, PNG.</p>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button type="submit" form="addVehicleForm" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
              Add Vehicle
            </button>
            <button type="button" id="cancelAddBtn" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Store all models data from PHP for use in JavaScript -->
    <script id="allModelsData" type="application/json">
      <?php echo json_encode($allModels); ?>
    </script>

    <script>