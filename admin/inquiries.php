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

// Initialize filters
$vehicleFilter = isset($_GET['vehicle_id']) ? intval($_GET['vehicle_id']) : 0;
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$dateFilter = isset($_GET['date']) ? $_GET['date'] : '';
$searchKeyword = isset($_GET['search']) ? trim($_GET['search']) : '';

// Pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = 10;
$offset = ($page - 1) * $recordsPerPage;

// Sorting parameters
$sortField = isset($_GET['sort']) ? $_GET['sort'] : 'submitted_at';
$sortOrder = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'ASC' : 'DESC';

// Get connection
$conn = getConnection();

// Function to get all vehicles for the filter dropdown
function getVehicles($conn)
{
  $query = "SELECT id, CONCAT(year, ' ', make, ' ', model) AS vehicle_name 
            FROM vehicles 
            ORDER BY year DESC, make, model";
  $stmt = $conn->prepare($query);
  $stmt->execute();
  $result = $stmt->get_result();

  $vehicles = [];
  while ($row = $result->fetch_assoc()) {
    $vehicles[] = $row;
  }

  $stmt->close();
  return $vehicles;
}

// Query to get inquiries with filtering, sorting and pagination
function getInquiries($conn, $vehicleFilter, $statusFilter, $dateFilter, $searchKeyword, $sortField, $sortOrder, $offset, $recordsPerPage, &$totalRecords)
{
  // Base query
  $query = "SELECT vi.*, 
                v.year AS vehicle_year, 
                v.make AS vehicle_make, 
                v.model AS vehicle_model,
                CONCAT(v.year, ' ', v.make, ' ', v.model) AS vehicle_name,
                u.first_name AS user_first_name, 
                u.last_name AS user_last_name,
                u.email AS user_email
            FROM vehicle_inquiries vi
            LEFT JOIN vehicles v ON vi.vehicle_id = v.id
            LEFT JOIN users u ON vi.user_id = u.id
            WHERE 1=1";

  // Count query (same filters, no LIMIT)
  $countQuery = "SELECT COUNT(*) as total FROM vehicle_inquiries vi
                 LEFT JOIN vehicles v ON vi.vehicle_id = v.id
                 LEFT JOIN users u ON vi.user_id = u.id
                 WHERE 1=1";

  $params = [];
  $types = "";

  // Add filters
  if ($vehicleFilter > 0) {
    $query .= " AND vi.vehicle_id = ?";
    $countQuery .= " AND vi.vehicle_id = ?";
    $params[] = $vehicleFilter;
    $types .= "i";
  }

  if (!empty($statusFilter)) {
    $query .= " AND vi.status = ?";
    $countQuery .= " AND vi.status = ?";
    $params[] = $statusFilter;
    $types .= "s";
  }

  if (!empty($dateFilter)) {
    $query .= " AND DATE(vi.submitted_at) = ?";
    $countQuery .= " AND DATE(vi.submitted_at) = ?";
    $params[] = $dateFilter;
    $types .= "s";
  }

  if (!empty($searchKeyword)) {
    $query .= " AND (vi.full_name LIKE ? OR vi.email LIKE ? OR vi.message LIKE ? OR CONCAT(v.year, ' ', v.make, ' ', v.model) LIKE ?)";
    $countQuery .= " AND (vi.full_name LIKE ? OR vi.email LIKE ? OR vi.message LIKE ? OR CONCAT(v.year, ' ', v.make, ' ', v.model) LIKE ?)";
    $searchParam = "%$searchKeyword%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ssss";
  }

  // Get total records count
  $countStmt = $conn->prepare($countQuery);
  if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
  }
  $countStmt->execute();
  $countResult = $countStmt->get_result();
  $countRow = $countResult->fetch_assoc();
  $totalRecords = $countRow['total'];
  $countStmt->close();

  // Add sorting
  $validSortFields = ['submitted_at', 'full_name', 'email', 'status'];
  $sortField = in_array($sortField, $validSortFields) ? $sortField : 'submitted_at';
  $query .= " ORDER BY vi.$sortField $sortOrder";

  // Add pagination
  $query .= " LIMIT ?, ?";
  $params[] = $offset;
  $params[] = $recordsPerPage;
  $types .= "ii";

  // Execute query
  $stmt = $conn->prepare($query);
  if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
  }
  $stmt->execute();
  $result = $stmt->get_result();

  $inquiries = [];
  while ($row = $result->fetch_assoc()) {
    $inquiries[] = $row;
  }

  $stmt->close();
  return $inquiries;
}

// Handle inquiry status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_inquiry_status'])) {
  $inquiry_id = isset($_POST['inquiry_id']) ? intval($_POST['inquiry_id']) : 0;
  $new_status = isset($_POST['new_status']) ? $_POST['new_status'] : '';

  // Validate input
  if ($inquiry_id <= 0 || empty($new_status)) {
    $_SESSION['errors'] = ["Invalid inquiry ID or status"];
  } else {
    // Validate status value
    $valid_statuses = ['New', 'In Progress', 'Contacted', 'Closed'];
    if (!in_array($new_status, $valid_statuses)) {
      $_SESSION['errors'] = ["Invalid status value"];
    } else {
      // Update the inquiry status
      $update_query = "UPDATE vehicle_inquiries SET status = ? WHERE id = ?";
      $update_stmt = $conn->prepare($update_query);
      $update_stmt->bind_param("si", $new_status, $inquiry_id);

      if ($update_stmt->execute()) {
        // Log the activity
        $admin_id = $_SESSION["admin_id"];
        $log_query = "INSERT INTO admin_activity_logs (admin_id, activity_type, activity_details, ip_address, user_agent) 
                      VALUES (?, 'update_inquiry', ?, ?, ?)";

        $activity_details = "Updated inquiry #" . $inquiry_id . " status to " . $new_status;
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        $log_stmt = $conn->prepare($log_query);
        $log_stmt->bind_param("isss", $admin_id, $activity_details, $ip_address, $user_agent);
        $log_stmt->execute();
        $log_stmt->close();

        $_SESSION['success'] = "Inquiry status updated successfully.";
      } else {
        $_SESSION['errors'] = ["Failed to update inquiry status: " . $update_stmt->error];
      }

      $update_stmt->close();
    }
  }
}

// Delete inquiry
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_inquiry'])) {
  $inquiry_id = isset($_POST['inquiry_id']) ? intval($_POST['inquiry_id']) : 0;

  if ($inquiry_id > 0) {
    $delete_query = "DELETE FROM vehicle_inquiries WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("i", $inquiry_id);

    if ($delete_stmt->execute()) {
      // Log the activity
      $admin_id = $_SESSION["admin_id"];
      $log_query = "INSERT INTO admin_activity_logs (admin_id, activity_type, activity_details, ip_address, user_agent) 
                    VALUES (?, 'delete_inquiry', ?, ?, ?)";

      $activity_details = "Deleted inquiry #" . $inquiry_id;
      $ip_address = $_SERVER['REMOTE_ADDR'];
      $user_agent = $_SERVER['HTTP_USER_AGENT'];

      $log_stmt = $conn->prepare($log_query);
      $log_stmt->bind_param("isss", $admin_id, $activity_details, $ip_address, $user_agent);
      $log_stmt->execute();
      $log_stmt->close();

      $_SESSION['success'] = "Inquiry deleted successfully.";
    } else {
      $_SESSION['errors'] = ["Failed to delete inquiry: " . $delete_stmt->error];
    }

    $delete_stmt->close();
  } else {
    $_SESSION['errors'] = ["Invalid inquiry ID"];
  }
}

// Get vehicles for filter dropdown
$vehicles = getVehicles($conn);

// Get inquiries with filtering, sorting and pagination
$totalRecords = 0;
$inquiries = getInquiries($conn, $vehicleFilter, $statusFilter, $dateFilter, $searchKeyword, $sortField, $sortOrder, $offset, $recordsPerPage, $totalRecords);

// Calculate total pages for pagination
$totalPages = ceil($totalRecords / $recordsPerPage);

// Close database connection
$conn->close();
?>

<!doctype html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="../src/output.css" rel="stylesheet">
  <title>Customer Inquiries - CentralAutogy Admin</title>
  <link rel="stylesheet" href="assets/css/index.css">
  <style>
    .status-badge {
      display: inline-flex;
      align-items: center;
      padding: 0.25rem 0.75rem;
      border-radius: 9999px;
      font-size: 0.75rem;
      font-weight: 500;
    }

    .status-badge.New {
      background-color: #e0f2fe;
      color: #0369a1;
    }

    .status-badge.In-Progress {
      background-color: #fef3c7;
      color: #92400e;
    }

    .status-badge.Contacted {
      background-color: #dcfce7;
      color: #166534;
    }

    .status-badge.Closed {
      background-color: #f3f4f6;
      color: #4b5563;
    }

    .inquiry-details {
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.3s ease;
    }

    .inquiry-details.expanded {
      max-height: 500px;
    }
  </style>
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
        <h1 class="text-2xl font-bold text-gray-800">Customer Inquiries</h1>
        <p class="text-gray-600">Manage and respond to customer vehicle inquiries</p>
      </div>

      <!-- Flash Messages -->
      <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow transition-opacity duration-500" role="alert">
          <div class="flex justify-between items-center">
            <div class="flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <p><?php echo $_SESSION['success']; ?></p>
            </div>
            <button class="close-flash">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>
        </div>
        <?php unset($_SESSION['success']); ?>
      <?php endif; ?>

      <?php if (isset($_SESSION['errors']) && is_array($_SESSION['errors']) && count($_SESSION['errors']) > 0): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow transition-opacity duration-500" role="alert">
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

      <!-- Filter and Search Bar -->
      <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <form action="inquiries.php" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
          <!-- Vehicle Filter -->
          <div>
            <label for="vehicle_id" class="block text-sm font-medium text-gray-700 mb-1">Vehicle</label>
            <select id="vehicle_id" name="vehicle_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
              <option value="">All Vehicles</option>
              <?php foreach ($vehicles as $vehicle): ?>
                <option value="<?php echo $vehicle['id']; ?>" <?php echo ($vehicleFilter == $vehicle['id']) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($vehicle['vehicle_name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Status Filter -->
          <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select id="status" name="status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
              <option value="">All Statuses</option>
              <option value="New" <?php echo ($statusFilter == 'New') ? 'selected' : ''; ?>>New</option>
              <option value="In Progress" <?php echo ($statusFilter == 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
              <option value="Contacted" <?php echo ($statusFilter == 'Contacted') ? 'selected' : ''; ?>>Contacted</option>
              <option value="Closed" <?php echo ($statusFilter == 'Closed') ? 'selected' : ''; ?>>Closed</option>
            </select>
          </div>

          <!-- Date Filter -->
          <div>
            <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
            <input type="date" id="date" name="date" value="<?php echo $dateFilter; ?>" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
          </div>

          <!-- Search -->
          <div>
            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($searchKeyword); ?>" placeholder="Name, Email, Message..." class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
          </div>

          <!-- Filter Button -->
          <div class="flex items-end">
            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition duration-300 shadow-sm w-full">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
              </svg>
              Apply Filters
            </button>
          </div>

          <!-- Hidden fields for sorting -->
          <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sortField); ?>">
          <input type="hidden" name="order" value="<?php echo htmlspecialchars($sortOrder); ?>">
        </form>
      </div>

      <!-- Inquiries Table -->
      <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
        <div class="p-4 border-b border-gray-200 flex justify-between items-center">
          <h3 class="text-lg font-semibold text-gray-800">Inquiries (<?php echo $totalRecords; ?>)</h3>

          <!-- Reset Filters -->
          <?php if (!empty($vehicleFilter) || !empty($statusFilter) || !empty($dateFilter) || !empty($searchKeyword)): ?>
            <a href="inquiries.php" class="text-sm text-red-600 hover:text-red-700 flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
              Clear Filters
            </a>
          <?php endif; ?>
        </div>

        <?php if (empty($inquiries)): ?>
          <div class="p-6 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <h3 class="text-lg font-medium text-gray-800 mb-2">No inquiries found</h3>
            <p class="text-gray-600">No inquiries match your search criteria.</p>
          </div>
        <?php else: ?>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="bg-gray-50">
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <a href="inquiries.php?sort=full_name&order=<?php echo $sortField === 'full_name' && $sortOrder === 'ASC' ? 'desc' : 'asc'; ?>&vehicle_id=<?php echo $vehicleFilter; ?>&status=<?php echo $statusFilter; ?>&date=<?php echo $dateFilter; ?>&search=<?php echo urlencode($searchKeyword); ?>" class="flex items-center">
                      Customer
                      <?php if ($sortField === 'full_name'): ?>
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
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <a href="inquiries.php?sort=email&order=<?php echo $sortField === 'email' && $sortOrder === 'ASC' ? 'desc' : 'asc'; ?>&vehicle_id=<?php echo $vehicleFilter; ?>&status=<?php echo $statusFilter; ?>&date=<?php echo $dateFilter; ?>&search=<?php echo urlencode($searchKeyword); ?>" class="flex items-center">
                      Contact Info
                      <?php if ($sortField === 'email'): ?>
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
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <a href="inquiries.php?sort=submitted_at&order=<?php echo $sortField === 'submitted_at' && $sortOrder === 'ASC' ? 'desc' : 'asc'; ?>&vehicle_id=<?php echo $vehicleFilter; ?>&status=<?php echo $statusFilter; ?>&date=<?php echo $dateFilter; ?>&search=<?php echo urlencode($searchKeyword); ?>" class="flex items-center">
                      Date
                      <?php if ($sortField === 'submitted_at'): ?>
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
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <a href="inquiries.php?sort=status&order=<?php echo $sortField === 'status' && $sortOrder === 'ASC' ? 'desc' : 'asc'; ?>&vehicle_id=<?php echo $vehicleFilter; ?>&status=<?php echo $statusFilter; ?>&date=<?php echo $dateFilter; ?>&search=<?php echo urlencode($searchKeyword); ?>" class="flex items-center">
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
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <?php foreach ($inquiries as $inquiry): ?>
                  <tr class="hover:bg-gray-50 transition duration-150">
                    <td class="px-4 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-full flex items-center justify-center">
                          <span class="text-indigo-700 font-medium text-sm">
                            <?php
                            $initials = '';
                            $nameParts = explode(' ', $inquiry['full_name']);
                            foreach ($nameParts as $part) {
                              if (!empty($part)) {
                                $initials .= $part[0];
                              }
                            }
                            echo htmlspecialchars(substr($initials, 0, 2));
                            ?>
                          </span>
                        </div>
                        <div class="ml-4">
                          <div class="text-sm font-medium text-gray-900">
                            <?php echo htmlspecialchars($inquiry['full_name']); ?>
                          </div>
                          <div class="text-xs text-gray-500">
                            <?php if (!empty($inquiry['user_id'])): ?>
                              <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                Registered User
                              </span>
                            <?php else: ?>
                              <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                Guest
                              </span>
                            <?php endif; ?>
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="px-4 py-4">
                      <div class="text-sm text-gray-900">
                        <a href="mailto:<?php echo htmlspecialchars($inquiry['email']); ?>" class="text-indigo-600 hover:text-indigo-900">
                          <?php echo htmlspecialchars($inquiry['email']); ?>
                        </a>
                      </div>
                      <?php if (!empty($inquiry['phone'])): ?>
                        <div class="text-sm text-gray-500">
                          <a href="tel:<?php echo htmlspecialchars($inquiry['phone']); ?>" class="hover:text-gray-700">
                            <?php echo htmlspecialchars($inquiry['phone']); ?>
                          </a>
                        </div>
                      <?php endif; ?>
                    </td>
                    <td class="px-4 py-4">
                      <div class="text-sm text-gray-900">
                        <?php echo htmlspecialchars($inquiry['vehicle_name']); ?>
                      </div>
                      <div class="text-xs text-gray-500">
                        ID: <?php echo htmlspecialchars($inquiry['vehicle_id']); ?>
                      </div>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">
                        <?php echo date('M d, Y', strtotime($inquiry['submitted_at'])); ?>
                      </div>
                      <div class="text-xs text-gray-500">
                        <?php echo date('h:i A', strtotime($inquiry['submitted_at'])); ?>
                      </div>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap">
                      <span class="status-badge <?php echo str_replace(' ', '-', $inquiry['status']); ?>">
                        <?php echo htmlspecialchars($inquiry['status']); ?>
                      </span>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <div class="flex justify-end space-x-2">
                        <button type="button" class="toggle-inquiry-details text-indigo-600 hover:text-indigo-900" data-inquiry-id="<?php echo $inquiry['id']; ?>">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                          </svg>
                        </button>
                        <a href="mailto:<?php echo htmlspecialchars($inquiry['email']); ?>" class="text-blue-600 hover:text-blue-900" title="Email Customer">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                          </svg>
                        </a>
                        <?php if (!empty($inquiry['phone'])): ?>
                          <a href="tel:<?php echo htmlspecialchars($inquiry['phone']); ?>" class="text-green-600 hover:text-green-900" title="Call Customer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                              <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                            </svg>
                          </a>
                        <?php endif; ?>
                        <button type="button" data-modal-target="update-status-modal-<?php echo $inquiry['id']; ?>" class="text-yellow-600 hover:text-yellow-900" title="Update Status">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                            <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                          </svg>
                        </button>
                        <button type="button" data-modal-target="delete-inquiry-modal-<?php echo $inquiry['id']; ?>" class="text-red-600 hover:text-red-900" title="Delete Inquiry">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                          </svg>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr class="bg-gray-50">
                    <td colspan="7" class="px-4 py-0">
                      <div id="inquiry-details-<?php echo $inquiry['id']; ?>" class="inquiry-details">
                        <div class="p-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                          <div class="md:col-span-2">
                            <h4 class="text-sm font-semibold text-gray-600 mb-2">Message</h4>
                            <p class="text-sm text-gray-700 mb-4 whitespace-pre-line"><?php echo htmlspecialchars($inquiry['message']); ?></p>

                            <?php if (!empty($inquiry['preferred_contact_method'])): ?>
                              <div class="mb-2">
                                <span class="text-xs font-semibold text-gray-600">Preferred Contact Method:</span>
                                <span class="text-sm text-gray-700 ml-2"><?php echo htmlspecialchars($inquiry['preferred_contact_method']); ?></span>
                              </div>
                            <?php endif; ?>

                            <?php if (!empty($inquiry['preferred_contact_time'])): ?>
                              <div class="mb-2">
                                <span class="text-xs font-semibold text-gray-600">Preferred Contact Time:</span>
                                <span class="text-sm text-gray-700 ml-2"><?php echo htmlspecialchars($inquiry['preferred_contact_time']); ?></span>
                              </div>
                            <?php endif; ?>

                            <?php if (!empty($inquiry['notes'])): ?>
                              <div class="mb-2">
                                <span class="text-xs font-semibold text-gray-600">Admin Notes:</span>
                                <p class="text-sm text-gray-700 mt-1 whitespace-pre-line"><?php echo htmlspecialchars($inquiry['notes']); ?></p>
                              </div>
                            <?php endif; ?>
                          </div>

                          <div class="border-t md:border-t-0 md:border-l border-gray-200 pt-4 md:pt-0 md:pl-4">
                            <h4 class="text-sm font-semibold text-gray-600 mb-2">Quick Actions</h4>

                            <div class="space-y-2">
                              <a href="vehicles.php?id=<?php echo $inquiry['vehicle_id']; ?>" class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                  <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                                  <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1v-1M14 9a1 1 0 00-1 1v6h1.05a2.5 2.5 0 014.9 0H19a1 1 0 001-1v-5M8 5h8V3H8v2zM8 9h8V7H8v2z" />
                                </svg>
                                View Vehicle Details
                              </a>

                              <?php if (!empty($inquiry['user_id'])): ?>
                                <a href="users.php?id=<?php echo $inquiry['user_id']; ?>" class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center">
                                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                  </svg>
                                  View User Profile
                                </a>
                              <?php endif; ?>

                              <button type="button" data-modal-target="add-note-modal-<?php echo $inquiry['id']; ?>" class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                                </svg>
                                Add Note
                              </button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </td>
                  </tr>

                  <!-- Update Status Modal -->
                  <div id="update-status-modal-<?php echo $inquiry['id']; ?>" class="fixed inset-0 z-50 hidden overflow-y-auto">
                    <div class="flex items-center justify-center min-h-screen px-4">
                      <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity" aria-hidden="true"></div>

                      <div class="relative bg-white rounded-lg max-w-md w-full mx-auto shadow-xl">
                        <div class="p-6">
                          <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Update Inquiry Status</h3>
                            <button type="button" class="modal-close text-gray-400 hover:text-gray-500">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                              </svg>
                            </button>
                          </div>

                          <form action="inquiries.php" method="POST">
                            <input type="hidden" name="inquiry_id" value="<?php echo $inquiry['id']; ?>">
                            <input type="hidden" name="update_inquiry_status" value="1">

                            <div class="mb-4">
                              <label for="new_status_<?php echo $inquiry['id']; ?>" class="block text-sm font-medium text-gray-700 mb-1">
                                New Status
                              </label>
                              <select id="new_status_<?php echo $inquiry['id']; ?>" name="new_status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="New" <?php echo ($inquiry['status'] == 'New') ? 'selected' : ''; ?>>New</option>
                                <option value="In Progress" <?php echo ($inquiry['status'] == 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                                <option value="Contacted" <?php echo ($inquiry['status'] == 'Contacted') ? 'selected' : ''; ?>>Contacted</option>
                                <option value="Closed" <?php echo ($inquiry['status'] == 'Closed') ? 'selected' : ''; ?>>Closed</option>
                              </select>
                            </div>

                            <div class="flex justify-end space-x-3 mt-6">
                              <button type="button" class="modal-close px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg transition duration-300">
                                Cancel
                              </button>
                              <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition duration-300">
                                Update Status
                              </button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Delete Inquiry Modal -->
                  <div id="delete-inquiry-modal-<?php echo $inquiry['id']; ?>" class="fixed inset-0 z-50 hidden overflow-y-auto">
                    <div class="flex items-center justify-center min-h-screen px-4">
                      <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity" aria-hidden="true"></div>

                      <div class="relative bg-white rounded-lg max-w-md w-full mx-auto shadow-xl">
                        <div class="p-6">
                          <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Confirm Deletion</h3>
                            <button type="button" class="modal-close text-gray-400 hover:text-gray-500">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                              </svg>
                            </button>
                          </div>

                          <p class="mb-4 text-gray-600">Are you sure you want to delete this inquiry? This action cannot be undone.</p>

                          <div class="bg-gray-50 rounded p-3 mb-4">
                            <div class="text-sm text-gray-700"><strong>Customer:</strong> <?php echo htmlspecialchars($inquiry['full_name']); ?></div>
                            <div class="text-sm text-gray-700"><strong>Vehicle:</strong> <?php echo htmlspecialchars($inquiry['vehicle_name']); ?></div>
                            <div class="text-sm text-gray-700"><strong>Date:</strong> <?php echo date('M d, Y', strtotime($inquiry['submitted_at'])); ?></div>
                          </div>

                          <form action="inquiries.php" method="POST">
                            <input type="hidden" name="inquiry_id" value="<?php echo $inquiry['id']; ?>">
                            <input type="hidden" name="delete_inquiry" value="1">

                            <div class="flex justify-end space-x-3 mt-6">
                              <button type="button" class="modal-close px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg transition duration-300">
                                Cancel
                              </button>
                              <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition duration-300">
                                Delete Inquiry
                              </button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Add Note Modal -->
                  <div id="add-note-modal-<?php echo $inquiry['id']; ?>" class="fixed inset-0 z-50 hidden overflow-y-auto">
                    <div class="flex items-center justify-center min-h-screen px-4">
                      <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity" aria-hidden="true"></div>

                      <div class="relative bg-white rounded-lg max-w-md w-full mx-auto shadow-xl">
                        <div class="p-6">
                          <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Add Note to Inquiry</h3>
                            <button type="button" class="modal-close text-gray-400 hover:text-gray-500">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                              </svg>
                            </button>
                          </div>

                          <form action="update_inquiry_notes.php" method="POST">
                            <input type="hidden" name="inquiry_id" value="<?php echo $inquiry['id']; ?>">

                            <div class="mb-4">
                              <label for="note_<?php echo $inquiry['id']; ?>" class="block text-sm font-medium text-gray-700 mb-1">
                                Note
                              </label>
                              <textarea id="note_<?php echo $inquiry['id']; ?>" name="note" rows="4" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Enter your notes about this inquiry..."><?php echo htmlspecialchars($inquiry['notes'] ?? ''); ?></textarea>
                            </div>

                            <div class="flex justify-end space-x-3 mt-6">
                              <button type="button" class="modal-close px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg transition duration-300">
                                Cancel
                              </button>
                              <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition duration-300">
                                Save Note
                              </button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
          <div class="px-4 py-3 flex items-center justify-between border-t border-gray-200">
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700">
                  Showing <span class="font-medium"><?php echo $offset + 1; ?></span> to
                  <span class="font-medium"><?php echo min($offset + $recordsPerPage, $totalRecords); ?></span> of
                  <span class="font-medium"><?php echo $totalRecords; ?></span> results
                </p>
              </div>
              <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                  <!-- Previous Page -->
                  <?php if ($page > 1): ?>
                    <a href="inquiries.php?page=<?php echo $page - 1; ?>&sort=<?php echo $sortField; ?>&order=<?php echo $sortOrder; ?>&vehicle_id=<?php echo $vehicleFilter; ?>&status=<?php echo $statusFilter; ?>&date=<?php echo $dateFilter; ?>&search=<?php echo urlencode($searchKeyword); ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                      <span class="sr-only">Previous</span>
                      <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                      </svg>
                    </a>
                  <?php else: ?>
                    <span class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed">
                      <span class="sr-only">Previous</span>
                      <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                      </svg>
                    </span>
                  <?php endif; ?>

                  <!-- Page Numbers -->
                  <?php
                  $startPage = max(1, $page - 2);
                  $endPage = min($startPage + 4, $totalPages);

                  if ($endPage - $startPage < 4 && $startPage > 1) {
                    $startPage = max(1, $endPage - 4);
                  }

                  for ($i = $startPage; $i <= $endPage; $i++):
                  ?>
                    <?php if ($i == $page): ?>
                      <span class="relative inline-flex items-center px-4 py-2 border border-indigo-500 bg-indigo-50 text-sm font-medium text-indigo-600">
                        <?php echo $i; ?>
                      </span>
                    <?php else: ?>
                      <a href="inquiries.php?page=<?php echo $i; ?>&sort=<?php echo $sortField; ?>&order=<?php echo $sortOrder; ?>&vehicle_id=<?php echo $vehicleFilter; ?>&status=<?php echo $statusFilter; ?>&date=<?php echo $dateFilter; ?>&search=<?php echo urlencode($searchKeyword); ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <?php echo $i; ?>
                      </a>
                    <?php endif; ?>
                  <?php endfor; ?>

                  <!-- Next Page -->
                  <?php if ($page < $totalPages): ?>
                    <a href="inquiries.php?page=<?php echo $page + 1; ?>&sort=<?php echo $sortField; ?>&order=<?php echo $sortOrder; ?>&vehicle_id=<?php echo $vehicleFilter; ?>&status=<?php echo $statusFilter; ?>&date=<?php echo $dateFilter; ?>&search=<?php echo urlencode($searchKeyword); ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                      <span class="sr-only">Next</span>
                      <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                      </svg>
                    </a>
                  <?php else: ?>
                    <span class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed">
                      <span class="sr-only">Next</span>
                      <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                      </svg>
                    </span>
                  <?php endif; ?>
                </nav>
              </div>
            </div>

            <!-- Mobile Pagination -->
            <div class="flex items-center justify-between sm:hidden">
              <div class="flex flex-1 justify-between">
                <?php if ($page > 1): ?>
                  <a href="inquiries.php?page=<?php echo $page - 1; ?>&sort=<?php echo $sortField; ?>&order=<?php echo $sortOrder; ?>&vehicle_id=<?php echo $vehicleFilter; ?>&status=<?php echo $statusFilter; ?>&date=<?php echo $dateFilter; ?>&search=<?php echo urlencode($searchKeyword); ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Previous
                  </a>
                <?php else: ?>
                  <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-300 bg-gray-100 cursor-not-allowed">
                    Previous
                  </span>
                <?php endif; ?>

                <span class="text-sm text-gray-700">
                  Page <?php echo $page; ?> of <?php echo $totalPages; ?>
                </span>

                <?php if ($page < $totalPages): ?>
                  <a href="inquiries.php?page=<?php echo $page + 1; ?>&sort=<?php echo $sortField; ?>&order=<?php echo $sortOrder; ?>&vehicle_id=<?php echo $vehicleFilter; ?>&status=<?php echo $statusFilter; ?>&date=<?php echo $dateFilter; ?>&search=<?php echo urlencode($searchKeyword); ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Next
                  </a>
                <?php else: ?>
                  <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-300 bg-gray-100 cursor-not-allowed">
                    Next
                  </span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>

  <script>
    // Toggle mobile menu
    document.getElementById('mobileMenuBtn').addEventListener('click', function() {
      document.querySelector('.sidebar').classList.toggle('-translate-x-full');
    });

    // Close flash messages
    document.querySelectorAll('.close-flash').forEach(button => {
      button.addEventListener('click', function() {
        const flashMessage = this.closest('[role="alert"]');
        flashMessage.style.opacity = '0';
        setTimeout(() => {
          flashMessage.style.display = 'none';
        }, 500);
      });
    });

    // Toggle inquiry details
    document.querySelectorAll('.toggle-inquiry-details').forEach(button => {
      button.addEventListener('click', function() {
        const inquiryId = this.getAttribute('data-inquiry-id');
        const detailsDiv = document.getElementById('inquiry-details-' + inquiryId);

        if (detailsDiv) {
          detailsDiv.classList.toggle('expanded');

          // Rotate the arrow icon
          if (detailsDiv.classList.contains('expanded')) {
            this.innerHTML = `
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
              </svg>
            `;
          } else {
            this.innerHTML = `
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            `;
          }
        }
      });
    });

    // Modal functionality
    document.querySelectorAll('[data-modal-target]').forEach(button => {
      button.addEventListener('click', function() {
        const modalId = this.getAttribute('data-modal-target');
        const modal = document.getElementById(modalId);

        if (modal) {
          modal.classList.remove('hidden');
        }
      });
    });

    document.querySelectorAll('.modal-close').forEach(button => {
      button.addEventListener('click', function() {
        const modal = this.closest('.fixed.inset-0.z-50');

        if (modal) {
          modal.classList.add('hidden');
        }
      });
    });

    // Close modal when clicking outside
    document.addEventListener('click', function(event) {
      if (event.target.classList.contains('fixed') && event.target.classList.contains('inset-0') && event.target.classList.contains('bg-gray-900')) {
        const modal = event.target.closest('.fixed.inset-0.z-50');

        if (modal) {
          modal.classList.add('hidden');
        }
      }
    });
  </script>
</body>

</html>