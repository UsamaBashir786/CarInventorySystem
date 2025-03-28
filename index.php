<?php
session_start();
// Include database connection
require_once 'config/db.php';

// Initialize filters and search params from GET request
$searchKeyword = isset($_GET['search']) ? trim($_GET['search']) : '';
$filterMake = isset($_GET['filter_make']) ? (int)$_GET['filter_make'] : 0;
$filterBodyType = isset($_GET['filter_body_type']) ? (int)$_GET['filter_body_type'] : 0;
$filterFuelType = isset($_GET['filter_fuel_type']) ? (int)$_GET['filter_fuel_type'] : 0;
$filterStatus = isset($_GET['filter_status']) ? (int)$_GET['filter_status'] : 0;
$filterYearMin = isset($_GET['filter_year_min']) ? (int)$_GET['filter_year_min'] : 0;
$filterYearMax = isset($_GET['filter_year_max']) ? (int)$_GET['filter_year_max'] : 0;
$filterPriceMin = isset($_GET['filter_price_min']) ? (float)$_GET['filter_price_min'] : 0;
$filterPriceMax = isset($_GET['filter_price_max']) ? (float)$_GET['filter_price_max'] : 0;

// Sorting parameters
$sortField = isset($_GET['sort']) ? $_GET['sort'] : 'year';
$sortOrder = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'ASC' : 'DESC';

// Pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 6; // Number of cars per page
$offset = ($page - 1) * $itemsPerPage;

// Function to get filter options
function getFilterOptions($conn)
{
  $filters = [];

  // Get makes
  $makesQuery = "SELECT id, name FROM makes ORDER BY display_order, name";
  $makesResult = $conn->query($makesQuery);
  $filters['makes'] = [];
  if ($makesResult && $makesResult->num_rows > 0) {
    while ($row = $makesResult->fetch_assoc()) {
      $filters['makes'][] = $row;
    }
  }

  // Get body types
  $bodyTypesQuery = "SELECT id, name FROM body_types ORDER BY display_order, name";
  $bodyTypesResult = $conn->query($bodyTypesQuery);
  $filters['bodyTypes'] = [];
  if ($bodyTypesResult && $bodyTypesResult->num_rows > 0) {
    while ($row = $bodyTypesResult->fetch_assoc()) {
      $filters['bodyTypes'][] = $row;
    }
  }

  // Get fuel types
  $fuelTypesQuery = "SELECT id, name FROM fuel_types ORDER BY display_order, name";
  $fuelTypesResult = $conn->query($fuelTypesQuery);
  $filters['fuelTypes'] = [];
  if ($fuelTypesResult && $fuelTypesResult->num_rows > 0) {
    while ($row = $fuelTypesResult->fetch_assoc()) {
      $filters['fuelTypes'][] = $row;
    }
  }

  // Get vehicle statuses
  $statusQuery = "SELECT id, name, css_class FROM vehicle_status ORDER BY display_order, name";
  $statusResult = $conn->query($statusQuery);
  $filters['statuses'] = [];
  if ($statusResult && $statusResult->num_rows > 0) {
    while ($row = $statusResult->fetch_assoc()) {
      $filters['statuses'][] = $row;
    }
  }

  return $filters;
}

// Function to get vehicles based on filters
function getVehicles(
  $conn,
  $searchKeyword,
  $filterMake,
  $filterBodyType,
  $filterFuelType,
  $filterStatus,
  $filterYearMin,
  $filterYearMax,
  $filterPriceMin,
  $filterPriceMax,
  $sortField,
  $sortOrder,
  $offset,
  $itemsPerPage,
  &$totalVehicles
) {

  // Start building the base query
  $query = "SELECT v.*, m.name as make_name, vs.css_class as status_class FROM vehicles v
  LEFT JOIN makes m ON v.make = m.name
  LEFT JOIN vehicle_status vs ON v.status = vs.name
  WHERE 1=1";

  $countQuery = "SELECT COUNT(*) as total FROM vehicles v 
      LEFT JOIN makes m ON v.make = m.name
      LEFT JOIN vehicle_status vs ON v.status = vs.name
      WHERE 1=1";

  $params = [];
  $types = "";

  // Add search keyword filter
  if (!empty($searchKeyword)) {
    $query .= " AND (v.make LIKE ? OR v.model LIKE ? OR CONCAT(v.make, ' ', v.model) LIKE ? OR v.vin LIKE ?)";
    $countQuery .= " AND (v.make LIKE ? OR v.model LIKE ? OR CONCAT(v.make, ' ', v.model) LIKE ? OR v.vin LIKE ?)";
    $searchParam = "%$searchKeyword%";

    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ssss";
  }

  // Add other filters
  if ($filterMake > 0) {
    $query .= " AND m.id = ?";
    $countQuery .= " AND m.id = ?";
    $params[] = $filterMake;
    $types .= "i";
  }

  if ($filterBodyType > 0) {
    $query .= " AND v.body_style = (SELECT name FROM body_types WHERE id = ?)";
    $countQuery .= " AND v.body_style = (SELECT name FROM body_types WHERE id = ?)";
    $params[] = $filterBodyType;
    $types .= "i";
  }

  if ($filterFuelType > 0) {
    $query .= " AND v.fuel_type = (SELECT name FROM fuel_types WHERE id = ?)";
    $countQuery .= " AND v.fuel_type = (SELECT name FROM fuel_types WHERE id = ?)";
    $params[] = $filterFuelType;
    $types .= "i";
  }

  if ($filterStatus > 0) {
    $query .= " AND v.status = (SELECT name FROM vehicle_status WHERE id = ?)";
    $countQuery .= " AND v.status = (SELECT name FROM vehicle_status WHERE id = ?)";
    $params[] = $filterStatus;
    $types .= "i";
  }

  if ($filterYearMin > 0) {
    $query .= " AND v.year >= ?";
    $countQuery .= " AND v.year >= ?";
    $params[] = $filterYearMin;
    $types .= "i";
  }

  if ($filterYearMax > 0) {
    $query .= " AND v.year <= ?";
    $countQuery .= " AND v.year <= ?";
    $params[] = $filterYearMax;
    $types .= "i";
  }

  if ($filterPriceMin > 0) {
    $query .= " AND v.price >= ?";
    $countQuery .= " AND v.price >= ?";
    $params[] = $filterPriceMin;
    $types .= "d";
  }

  if ($filterPriceMax > 0) {
    $query .= " AND v.price <= ?";
    $countQuery .= " AND v.price <= ?";
    $params[] = $filterPriceMax;
    $types .= "d";
  }

  // Get the total count of vehicles matching the filters
  $countStmt = $conn->prepare($countQuery);
  if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
  }
  $countStmt->execute();
  $countResult = $countStmt->get_result();
  $countRow = $countResult->fetch_assoc();
  $totalVehicles = $countRow['total'];
  $countStmt->close();

  // Add sorting
  $validSortFields = ['price', 'year', 'mileage', 'created_at'];
  $sortField = in_array($sortField, $validSortFields) ? $sortField : 'created_at';
  $sortOrder = ($sortOrder === 'ASC') ? 'ASC' : 'DESC';

  $query .= " ORDER BY v.$sortField $sortOrder";

  // Add pagination
  $query .= " LIMIT ?, ?";
  $params[] = $offset;
  $params[] = $itemsPerPage;
  $types .= "ii";

  // Prepare and execute the main query
  $stmt = $conn->prepare($query);
  if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
  }
  $stmt->execute();
  $result = $stmt->get_result();

  $vehicles = [];
  if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      // Get the primary image for this vehicle
      $imageQuery = "SELECT image_path FROM vehicle_images WHERE vehicle_id = ? AND is_primary = 1 LIMIT 1";
      $imageStmt = $conn->prepare($imageQuery);
      $imageStmt->bind_param("i", $row['id']);
      $imageStmt->execute();
      $imageResult = $imageStmt->get_result();

      if ($imageResult && $imageResult->num_rows > 0) {
        $imageRow = $imageResult->fetch_assoc();
        $row['primary_image'] = $imageRow['image_path'];
      } else {
        // Fallback to any image if no primary image
        $anyImageQuery = "SELECT image_path FROM vehicle_images WHERE vehicle_id = ? LIMIT 1";
        $anyImageStmt = $conn->prepare($anyImageQuery);
        $anyImageStmt->bind_param("i", $row['id']);
        $anyImageStmt->execute();
        $anyImageResult = $anyImageStmt->get_result();

        if ($anyImageResult && $anyImageResult->num_rows > 0) {
          $anyImageRow = $anyImageResult->fetch_assoc();
          $row['primary_image'] = $anyImageRow['image_path'];
        } else {
          // Use a placeholder if no images available
          $row['primary_image'] = 'assets/images/placeholder.jpg';
        }

        $anyImageStmt->close();
      }

      $imageStmt->close();
      $vehicles[] = $row;
    }
  }

  $stmt->close();
  return $vehicles;
}

// Get filter options
$filterOptions = getFilterOptions($conn);

// Get total number of vehicles and current page vehicles
$totalVehicles = 0;
$vehicles = getVehicles(
  $conn,
  $searchKeyword,
  $filterMake,
  $filterBodyType,
  $filterFuelType,
  $filterStatus,
  $filterYearMin,
  $filterYearMax,
  $filterPriceMin,
  $filterPriceMax,
  $sortField,
  $sortOrder,
  $offset,
  $itemsPerPage,
  $totalVehicles
);

// Calculate total pages for pagination
$totalPages = ceil($totalVehicles / $itemsPerPage);
?>

<!doctype html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="src/output.css" rel="stylesheet">
  <title>CentralAutogy - Find Your Perfect Car</title>
  <link rel="shortcut icon" href="assets/img/fav.png" type="image/x-icon">
  <link rel="stylesheet" href="assets/css/index.css">
  <style>
    /* Custom styles for the range slider */
    .range-slider {
      -webkit-appearance: none;
      width: 100%;
      height: 6px;
      background: #ddd;
      outline: none;
      border-radius: 8px;
    }

    .range-slider::-webkit-slider-thumb {
      -webkit-appearance: none;
      appearance: none;
      width: 18px;
      height: 18px;
      background: #4f46e5;
      border-radius: 50%;
      cursor: pointer;
    }

    .range-slider::-moz-range-thumb {
      width: 18px;
      height: 18px;
      background: #4f46e5;
      border-radius: 50%;
      cursor: pointer;
    }

    /* List view styling */
    #carsContainer.list-view {
      grid-template-columns: 1fr !important;
    }

    #carsContainer.list-view .car-card {
      display: flex;
      flex-direction: row;
    }

    #carsContainer.list-view .car-image {
      width: 300px;
      height: 100%;
      object-fit: cover;
    }

    #carsContainer.list-view .car-card>div:first-child {
      width: 300px;
    }

    #carsContainer.list-view .car-card>div:last-child {
      flex: 1;
    }

    /* Mobile filter drawer */
    .mobile-filter-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 40;
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s ease;
    }

    .mobile-filter-drawer {
      position: fixed;
      top: 0;
      right: -100%;
      width: 85%;
      max-width: 350px;
      height: 100%;
      background-color: white;
      z-index: 50;
      overflow-y: auto;
      transition: all 0.3s ease;
      box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
    }

    .mobile-filter-overlay.open {
      opacity: 1;
      visibility: visible;
    }

    .mobile-filter-drawer.open {
      right: 0;
    }

    /* View toggle active state */
    .view-btn.active {
      background-color: #4f46e5;
      color: white;
    }

    .car-image {
      height: 200px;
      object-fit: cover;
      width: 100%;
    }
  </style>
</head>

<body class="bg-gray-50">
  <?php include 'includes/navbar.php'; ?>

  <!-- Main Content -->
  <main class="container mx-auto px-4 py-6">
    <!-- Hero Section -->
    <div class="mb-8 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl p-6 md:p-8 lg:p-10 text-white shadow-lg">
      <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl md:text-3xl lg:text-4xl font-bold mb-3">Find Your Perfect Car</h1>
        <p class="text-white/90 mb-6 max-w-2xl">Browse our extensive collection of high-quality vehicles. Use our advanced search to find exactly what you're looking for.</p>

        <!-- Search bar -->
        <form action="index.php" method="GET" class="flex flex-col sm:flex-row gap-3 mb-2">
          <div class="flex-grow">
            <input type="text" name="search" placeholder="Search by make, model, or VIN..."
              value="<?php echo htmlspecialchars($searchKeyword); ?>"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-300">
          </div>
          <button type="submit" class="bg-white text-indigo-600 hover:bg-gray-100 px-6 py-3 rounded-lg font-medium transition-colors whitespace-nowrap">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
            </svg>
            Search
          </button>
        </form>

        <!-- Popular searches -->
        <div class="flex flex-wrap gap-2 mt-4">
          <span class="text-xs text-white/80">Popular:</span>
          <?php foreach (array_slice($filterOptions['makes'], 0, min(5, count($filterOptions['makes']))) as $make): ?>
            <a href="index.php?filter_make=<?php echo $make['id']; ?>" class="text-xs bg-white/20 hover:bg-white/30 px-3 py-1 rounded-full transition-colors">
              <?php echo htmlspecialchars($make['name']); ?>
            </a>
          <?php endforeach; ?>

          <?php if (!empty($filterOptions['bodyTypes']) && isset($filterOptions['bodyTypes'][1])): ?>
            <a href="index.php?filter_body_type=<?php echo $filterOptions['bodyTypes'][1]['id']; ?>" class="text-xs bg-white/20 hover:bg-white/30 px-3 py-1 rounded-full transition-colors">
              <?php echo htmlspecialchars($filterOptions['bodyTypes'][1]['name']); ?>s
            </a>
          <?php endif; ?>

          <?php if (!empty($filterOptions['fuelTypes']) && count($filterOptions['fuelTypes']) >= 3 && isset($filterOptions['fuelTypes'][2])): ?>
            <a href="index.php?filter_fuel_type=<?php echo $filterOptions['fuelTypes'][2]['id']; ?>" class="text-xs bg-white/20 hover:bg-white/30 px-3 py-1 rounded-full transition-colors">
              <?php echo htmlspecialchars($filterOptions['fuelTypes'][2]['name']); ?>
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Mobile filter toggle -->
    <div class="lg:hidden mb-6">
      <button id="mobileFilterBtn" class="w-full bg-white shadow-sm border border-gray-200 text-gray-700 px-4 py-3 rounded-lg flex items-center justify-center space-x-2 hover:bg-gray-50 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
        </svg>
        <span>Filters (<?php echo $totalVehicles; ?> cars found)</span>
      </button>
    </div>

    <!-- Content Area (Sidebar + Cars) -->
    <div class="flex flex-wrap lg:flex-nowrap gap-6">
      <!-- Sidebar Filters (Desktop) -->
      <div class="hidden lg:block w-full lg:w-1/4 xl:w-1/5">
        <div class="bg-white rounded-xl shadow-sm p-6 filter-card sticky top-24">
          <div class="mb-6">
            <h3 class="font-semibold text-gray-800 text-lg mb-4">Filters</h3>
            <div class="flex justify-between items-center mb-1">
              <span class="text-gray-600 text-sm"><?php echo $totalVehicles; ?> cars found</span>
              <a href="index.php" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">Clear All</a>
            </div>
          </div>

          <form action="index.php" method="GET" id="desktopFilterForm">
            <!-- Price Range -->
            <div class="mb-6">
              <h4 class="font-medium text-gray-800 mb-3">Price Range</h4>
              <div class="mb-4">
                <input type="range" min="0" max="100000" step="1000" value="<?php echo $filterPriceMax > 0 ? $filterPriceMax : 50000; ?>"
                  class="range-slider" id="priceRange" name="filter_price_max">
                <div class="flex justify-between mt-2 text-sm text-gray-600">
                  <span>$0</span>
                  <span id="priceValue">$<?php echo number_format($filterPriceMax > 0 ? $filterPriceMax : 50000); ?></span>
                  <span>$100k+</span>
                </div>
              </div>
            </div>

            <!-- Make -->
            <div class="mb-6">
              <h4 class="font-medium text-gray-800 mb-3">Make</h4>
              <div class="space-y-2 max-h-48 overflow-y-auto">
                <?php foreach ($filterOptions['makes'] as $make): ?>
                  <label class="flex items-center">
                    <input type="radio" name="filter_make" value="<?php echo $make['id']; ?>"
                      <?php echo ($filterMake == $make['id']) ? 'checked' : ''; ?>
                      class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4"
                      onchange="document.getElementById('desktopFilterForm').submit()">
                    <span class="ml-2 text-gray-700"><?php echo htmlspecialchars($make['name']); ?></span>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Body Type -->
            <div class="mb-6">
              <h4 class="font-medium text-gray-800 mb-3">Body Type</h4>
              <div class="space-y-2">
                <?php foreach ($filterOptions['bodyTypes'] as $bodyType): ?>
                  <label class="flex items-center">
                    <input type="radio" name="filter_body_type" value="<?php echo $bodyType['id']; ?>"
                      <?php echo ($filterBodyType == $bodyType['id']) ? 'checked' : ''; ?>
                      class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4"
                      onchange="document.getElementById('desktopFilterForm').submit()">
                    <span class="ml-2 text-gray-700"><?php echo htmlspecialchars($bodyType['name']); ?></span>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Fuel Type -->
            <div class="mb-6">
              <h4 class="font-medium text-gray-800 mb-3">Fuel Type</h4>
              <div class="space-y-2">
                <?php foreach ($filterOptions['fuelTypes'] as $fuelType): ?>
                  <label class="flex items-center">
                    <input type="radio" name="filter_fuel_type" value="<?php echo $fuelType['id']; ?>"
                      <?php echo ($filterFuelType == $fuelType['id']) ? 'checked' : ''; ?>
                      class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4"
                      onchange="document.getElementById('desktopFilterForm').submit()">
                    <span class="ml-2 text-gray-700"><?php echo htmlspecialchars($fuelType['name']); ?></span>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Year -->
            <div class="mb-6">
              <h4 class="font-medium text-gray-800 mb-3">Year</h4>
              <div class="grid grid-cols-2 gap-2">
                <select name="filter_year_min" class="rounded-lg border-gray-300 text-sm p-2 text-gray-700 focus:ring-indigo-500 focus:border-indigo-500" onchange="document.getElementById('desktopFilterForm').submit()">
                  <option value="">Min Year</option>
                  <?php for ($year = date('Y'); $year >= 2000; $year--): ?>
                    <option value="<?php echo $year; ?>" <?php echo ($filterYearMin == $year) ? 'selected' : ''; ?>><?php echo $year; ?></option>
                  <?php endfor; ?>
                </select>
                <select name="filter_year_max" class="rounded-lg border-gray-300 text-sm p-2 text-gray-700 focus:ring-indigo-500 focus:border-indigo-500" onchange="document.getElementById('desktopFilterForm').submit()">
                  <option value="">Max Year</option>
                  <?php for ($year = date('Y'); $year >= 2000; $year--): ?>
                    <option value="<?php echo $year; ?>" <?php echo ($filterYearMax == $year) ? 'selected' : ''; ?>><?php echo $year; ?></option>
                  <?php endfor; ?>
                </select>
              </div>
            </div>

            <!-- Status Filter -->
            <div class="mb-6">
              <h4 class="font-medium text-gray-800 mb-3">Status</h4>
              <div class="space-y-2">
                <?php foreach ($filterOptions['statuses'] as $status): ?>
                  <label class="flex items-center">
                    <input type="radio" name="filter_status" value="<?php echo $status['id']; ?>"
                      <?php echo ($filterStatus == $status['id']) ? 'checked' : ''; ?>
                      class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4"
                      onchange="document.getElementById('desktopFilterForm').submit()">
                    <span class="ml-2 text-gray-700"><?php echo htmlspecialchars($status['name']); ?></span>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Apply Filters Button -->
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 px-4 rounded-lg transition-colors font-medium shadow-sm">
              Apply Filters
            </button>

            <!-- Hidden inputs to maintain pagination and sorting -->
            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sortField); ?>">
            <input type="hidden" name="order" value="<?php echo htmlspecialchars($sortOrder); ?>">
            <input type="hidden" name="page" value="1"> <!-- Reset to page 1 when filtering -->
          </form>
        </div>
      </div>

      <!-- Cars Listing -->
      <div class="w-full lg:w-3/4 xl:w-4/5">
        <!-- Toolbar -->
        <div class="bg-white rounded-xl shadow-sm p-4 mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div class="flex items-center">
            <span class="text-gray-700 mr-2">Sort by:</span>
            <select id="sortSelect" class="rounded-lg border-gray-300 text-sm p-2 text-gray-700 focus:ring-indigo-500 focus:border-indigo-500">
              <option value="year-desc" <?php echo ($sortField == 'year' && $sortOrder == 'DESC') ? 'selected' : ''; ?>>Newest</option>
              <option value="year-asc" <?php echo ($sortField == 'year' && $sortOrder == 'ASC') ? 'selected' : ''; ?>>Oldest</option>
              <option value="price-asc" <?php echo ($sortField == 'price' && $sortOrder == 'ASC') ? 'selected' : ''; ?>>Price: Low to High</option>
              <option value="price-desc" <?php echo ($sortField == 'price' && $sortOrder == 'DESC') ? 'selected' : ''; ?>>Price: High to Low</option>
              <option value="mileage-asc" <?php echo ($sortField == 'mileage' && $sortOrder == 'ASC') ? 'selected' : ''; ?>>Mileage: Low to High</option>
              <option value="mileage-desc" <?php echo ($sortField == 'mileage' && $sortOrder == 'DESC') ? 'selected' : ''; ?>>Mileage: High to Low</option>
            </select>
          </div>

          <div class="flex items-center space-x-2">
            <span class="text-gray-700 mr-1">View:</span>
            <button id="gridViewBtn" class="view-btn active bg-gray-100 p-2 rounded-lg text-gray-700 hover:bg-gray-200 transition-colors" title="Grid View">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
              </svg>
            </button>
            <button id="listViewBtn" class="view-btn bg-gray-100 p-2 rounded-lg text-gray-700 hover:bg-gray-200 transition-colors" title="List View">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Cars Grid -->
        <div id="carsContainer" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
          <?php if (empty($vehicles)): ?>
            <div class="col-span-full text-center py-12 bg-white rounded-xl shadow-sm">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
              <h3 class="text-lg font-medium text-gray-800 mb-2">No vehicles found</h3>
              <p class="text-gray-600 mb-4">We couldn't find any vehicles matching your criteria.</p>
              <a href="index.php" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition-colors font-medium">
                Clear Filters
              </a>
            </div>
          <?php else: ?>
            <?php foreach ($vehicles as $vehicle): ?>
              <!-- Car Card -->
              <div class="car-card bg-white shadow-sm overflow-hidden rounded-lg">
                <div class="relative">
                  <img src="<?php echo htmlspecialchars($vehicle['primary_image']); ?>" alt="<?php echo htmlspecialchars($vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model']); ?>" class="car-image">
                  <div class="absolute top-2 right-2">
                    <button class="bg-white/80 hover:bg-white p-1.5 rounded-full transition-colors">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 hover:text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                      </svg>
                    </button>
                  </div>
                </div>
                <div class="p-4">
                  <div class="flex justify-between items-start mb-1">
                    <h3 class="font-semibold text-gray-800"><?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']); ?></h3>
                    <span class="<?php echo (!empty($vehicle['status_class'])) ? htmlspecialchars($vehicle['status_class']) : 'bg-gray-100 text-gray-800'; ?> text-xs px-2 py-1 rounded-full font-medium">
                      <?php echo htmlspecialchars($vehicle['status']); ?>
                    </span>
                  </div>
                  <p class="text-gray-600 text-sm mb-3">
                    <?php echo htmlspecialchars($vehicle['year']); ?> ·
                    <?php echo number_format($vehicle['mileage']); ?> mi ·
                    <?php echo htmlspecialchars($vehicle['fuel_type']); ?>
                  </p>
                  <div class="flex flex-wrap gap-2 mb-4">
                    <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full"><?php echo htmlspecialchars($vehicle['body_style']); ?></span>
                    <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full"><?php echo htmlspecialchars($vehicle['transmission']); ?></span>
                    <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full"><?php echo htmlspecialchars($vehicle['drivetrain']); ?></span>
                  </div>
                  <div class="flex justify-between items-end">
                    <div>
                      <span class="text-gray-500 text-xs">Price</span>
                      <p class="text-indigo-600 font-semibold text-xl">$<?php echo number_format($vehicle['price'], 2); ?></p>
                    </div>
                    <a href="car-details.php?id=<?php echo $vehicle['id']; ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                      View Details
                    </a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
          <div class="mt-10 mb-4 flex justify-center">
            <div class="flex space-x-1">
              <?php if ($page > 1): ?>
                <a href="index.php?page=<?php echo $page - 1; ?>&sort=<?php echo $sortField; ?>&order=<?php echo $sortOrder; ?>&filter_make=<?php echo $filterMake; ?>&filter_body_type=<?php echo $filterBodyType; ?>&filter_fuel_type=<?php echo $filterFuelType; ?>&filter_status=<?php echo $filterStatus; ?>&filter_year_min=<?php echo $filterYearMin; ?>&filter_year_max=<?php echo $filterYearMax; ?>&filter_price_min=<?php echo $filterPriceMin; ?>&filter_price_max=<?php echo $filterPriceMax; ?>&search=<?php echo urlencode($searchKeyword); ?>"
                  class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                  Previous
                </a>
              <?php endif; ?>

              <?php
              // Show limited page numbers with ellipsis
              $startPage = max(1, $page - 2);
              $endPage = min($totalPages, $page + 2);

              // Always show first page
              if ($startPage > 1) {
                echo '<a href="index.php?page=1&sort=' . $sortField . '&order=' . $sortOrder . '&filter_make=' . $filterMake . '&filter_body_type=' . $filterBodyType . '&filter_fuel_type=' . $filterFuelType . '&filter_status=' . $filterStatus . '&filter_year_min=' . $filterYearMin . '&filter_year_max=' . $filterYearMax . '&filter_price_min=' . $filterPriceMin . '&filter_price_max=' . $filterPriceMax . '&search=' . urlencode($searchKeyword) . '" 
          class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">1</a>';

                if ($startPage > 2) {
                  echo '<span class="px-4 py-2">...</span>';
                }
              }

              // Display page numbers
              for ($i = $startPage; $i <= $endPage; $i++) {
                $activeClass = ($i == $page) ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50';
                echo '<a href="index.php?page=' . $i . '&sort=' . $sortField . '&order=' . $sortOrder . '&filter_make=' . $filterMake . '&filter_body_type=' . $filterBodyType . '&filter_fuel_type=' . $filterFuelType . '&filter_status=' . $filterStatus . '&filter_year_min=' . $filterYearMin . '&filter_year_max=' . $filterYearMax . '&filter_price_min=' . $filterPriceMin . '&filter_price_max=' . $filterPriceMax . '&search=' . urlencode($searchKeyword) . '" 
          class="px-4 py-2 border border-gray-300 rounded-lg ' . $activeClass . '">' . $i . '</a>';
              }

              // Always show last page
              if ($endPage < $totalPages) {
                if ($endPage < $totalPages - 1) {
                  echo '<span class="px-4 py-2">...</span>';
                }

                echo '<a href="index.php?page=' . $totalPages . '&sort=' . $sortField . '&order=' . $sortOrder . '&filter_make=' . $filterMake . '&filter_body_type=' . $filterBodyType . '&filter_fuel_type=' . $filterFuelType . '&filter_status=' . $filterStatus . '&filter_year_min=' . $filterYearMin . '&filter_year_max=' . $filterYearMax . '&filter_price_min=' . $filterPriceMin . '&filter_price_max=' . $filterPriceMax . '&search=' . urlencode($searchKeyword) . '" 
          class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">' . $totalPages . '</a>';
              }

              // Next button
              if ($page < $totalPages) {
                echo '<a href="index.php?page=' . ($page + 1) . '&sort=' . $sortField . '&order=' . $sortOrder . '&filter_make=' . $filterMake . '&filter_body_type=' . $filterBodyType . '&filter_fuel_type=' . $filterFuelType . '&filter_status=' . $filterStatus . '&filter_year_min=' . $filterYearMin . '&filter_year_max=' . $filterYearMax . '&filter_price_min=' . $filterPriceMin . '&filter_price_max=' . $filterPriceMax . '&search=' . urlencode($searchKeyword) . '" 
          class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Next</a>';
              }
              ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <!-- Mobile Filter Drawer -->
  <div class="mobile-filter-overlay" id="mobileFilterOverlay"></div>
  <div class="mobile-filter-drawer" id="mobileFilterDrawer">
    <div class="p-6">
      <div class="flex justify-between items-center mb-6">
        <h3 class="font-semibold text-gray-800 text-lg">Filters</h3>
        <button id="closeFilterBtn" class="text-gray-400 hover:text-gray-500">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <div class="mb-4">
        <span class="text-gray-600 text-sm"><?php echo $totalVehicles; ?> cars found</span>
        <a href="index.php" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium ml-2">Clear All</a>
      </div>

      <form action="index.php" method="GET" id="mobileFilterForm">
        <!-- Price Range -->
        <div class="mb-6">
          <h4 class="font-medium text-gray-800 mb-3">Price Range</h4>
          <div class="mb-4">
            <input type="range" min="0" max="100000" step="1000" value="<?php echo $filterPriceMax > 0 ? $filterPriceMax : 50000; ?>"
              class="range-slider" id="mobilePriceRange" name="filter_price_max">
            <div class="flex justify-between mt-2 text-sm text-gray-600">
              <span>$0</span>
              <span id="mobilePriceValue">$<?php echo number_format($filterPriceMax > 0 ? $filterPriceMax : 50000); ?></span>
              <span>$100k+</span>
            </div>
          </div>
        </div>

        <!-- Duplicate all the filter options from desktop for mobile -->
        <!-- Make -->
        <div class="mb-6">
          <h4 class="font-medium text-gray-800 mb-3">Make</h4>
          <div class="space-y-2 max-h-48 overflow-y-auto">
            <?php foreach ($filterOptions['makes'] as $make): ?>
              <label class="flex items-center">
                <input type="radio" name="filter_make" value="<?php echo $make['id']; ?>"
                  <?php echo ($filterMake == $make['id']) ? 'checked' : ''; ?>
                  class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                <span class="ml-2 text-gray-700"><?php echo htmlspecialchars($make['name']); ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Body Type -->
        <div class="mb-6">
          <h4 class="font-medium text-gray-800 mb-3">Body Type</h4>
          <div class="space-y-2">
            <?php foreach ($filterOptions['bodyTypes'] as $bodyType): ?>
              <label class="flex items-center">
                <input type="radio" name="filter_body_type" value="<?php echo $bodyType['id']; ?>"
                  <?php echo ($filterBodyType == $bodyType['id']) ? 'checked' : ''; ?>
                  class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                <span class="ml-2 text-gray-700"><?php echo htmlspecialchars($bodyType['name']); ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Fuel Type -->
        <div class="mb-6">
          <h4 class="font-medium text-gray-800 mb-3">Fuel Type</h4>
          <div class="space-y-2">
            <?php foreach ($filterOptions['fuelTypes'] as $fuelType): ?>
              <label class="flex items-center">
                <input type="radio" name="filter_fuel_type" value="<?php echo $fuelType['id']; ?>"
                  <?php echo ($filterFuelType == $fuelType['id']) ? 'checked' : ''; ?>
                  class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                <span class="ml-2 text-gray-700"><?php echo htmlspecialchars($fuelType['name']); ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Year -->
        <div class="mb-6">
          <h4 class="font-medium text-gray-800 mb-3">Year</h4>
          <div class="grid grid-cols-2 gap-2">
            <select name="filter_year_min" class="rounded-lg border-gray-300 text-sm p-2 text-gray-700 focus:ring-indigo-500 focus:border-indigo-500">
              <option value="">Min Year</option>
              <?php for ($year = date('Y'); $year >= 2000; $year--): ?>
                <option value="<?php echo $year; ?>" <?php echo ($filterYearMin == $year) ? 'selected' : ''; ?>><?php echo $year; ?></option>
              <?php endfor; ?>
            </select>
            <select name="filter_year_max" class="rounded-lg border-gray-300 text-sm p-2 text-gray-700 focus:ring-indigo-500 focus:border-indigo-500">
              <option value="">Max Year</option>
              <?php for ($year = date('Y'); $year >= 2000; $year--): ?>
                <option value="<?php echo $year; ?>" <?php echo ($filterYearMax == $year) ? 'selected' : ''; ?>><?php echo $year; ?></option>
              <?php endfor; ?>
            </select>
          </div>
        </div>

        <!-- Status Filter -->
        <div class="mb-6">
          <h4 class="font-medium text-gray-800 mb-3">Status</h4>
          <div class="space-y-2">
            <?php foreach ($filterOptions['statuses'] as $status): ?>
              <label class="flex items-center">
                <input type="radio" name="filter_status" value="<?php echo $status['id']; ?>"
                  <?php echo ($filterStatus == $status['id']) ? 'checked' : ''; ?>
                  class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                <span class="ml-2 text-gray-700"><?php echo htmlspecialchars($status['name']); ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Apply Filters Button -->
        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 px-4 rounded-lg transition-colors font-medium shadow-sm">
          Apply Filters
        </button>

        <!-- Hidden inputs to maintain pagination and sorting -->
        <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sortField); ?>">
        <input type="hidden" name="order" value="<?php echo htmlspecialchars($sortOrder); ?>">
        <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchKeyword); ?>">
        <input type="hidden" name="page" value="1"> <!-- Reset to page 1 when filtering -->
      </form>
    </div>
  </div>

  <?php include 'includes/footer.php'; ?>

  <script>
    // Price range slider
    const priceRange = document.getElementById('priceRange');
    const priceValue = document.getElementById('priceValue');
    const mobilePriceRange = document.getElementById('mobilePriceRange');
    const mobilePriceValue = document.getElementById('mobilePriceValue');

    // Update price value displays
    priceRange.addEventListener('input', function() {
      const value = this.value;
      priceValue.textContent = '$' + Number(value).toLocaleString();
    });

    mobilePriceRange.addEventListener('input', function() {
      const value = this.value;
      mobilePriceValue.textContent = '$' + Number(value).toLocaleString();
    });

    // Mobile filter drawer
    const mobileFilterBtn = document.getElementById('mobileFilterBtn');
    const closeFilterBtn = document.getElementById('closeFilterBtn');
    const mobileFilterDrawer = document.getElementById('mobileFilterDrawer');
    const mobileFilterOverlay = document.getElementById('mobileFilterOverlay');

    mobileFilterBtn.addEventListener('click', function() {
      mobileFilterDrawer.classList.add('open');
      mobileFilterOverlay.classList.add('open');
      document.body.style.overflow = 'hidden';
    });

    function closeFilterDrawer() {
      mobileFilterDrawer.classList.remove('open');
      mobileFilterOverlay.classList.remove('open');
      document.body.style.overflow = '';
    }

    closeFilterBtn.addEventListener('click', closeFilterDrawer);
    mobileFilterOverlay.addEventListener('click', closeFilterDrawer);

    // View toggle
    const gridViewBtn = document.getElementById('gridViewBtn');
    const listViewBtn = document.getElementById('listViewBtn');
    const carsContainer = document.getElementById('carsContainer');

    gridViewBtn.addEventListener('click', function() {
      carsContainer.classList.remove('list-view');
      gridViewBtn.classList.add('active');
      listViewBtn.classList.remove('active');
      localStorage.setItem('view', 'grid');
    });

    listViewBtn.addEventListener('click', function() {
      carsContainer.classList.add('list-view');
      listViewBtn.classList.add('active');
      gridViewBtn.classList.remove('active');
      localStorage.setItem('view', 'list');
    });

    // Load saved view preference
    const savedView = localStorage.getItem('view');
    if (savedView === 'list') {
      carsContainer.classList.add('list-view');
      listViewBtn.classList.add('active');
      gridViewBtn.classList.remove('active');
    }

    // Sort select handler
    const sortSelect = document.getElementById('sortSelect');
    sortSelect.addEventListener('change', function() {
      const [field, order] = this.value.split('-');

      // Create URL with current filters but new sort parameters
      let currentUrl = new URL(window.location.href);
      let params = new URLSearchParams(currentUrl.search);

      params.set('sort', field);
      params.set('order', order);
      params.set('page', '1'); // Reset to page 1 when sorting changes

      window.location.href = `${currentUrl.pathname}?${params.toString()}`;
    });
  </script>
</body>

</html>