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

// Initialize variables
$conn = getConnection();
$success_message = "";
$error_message = "";

// Process form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Handle inquiry status update
  if (isset($_POST['update_inquiry_status'])) {
    $inquiry_id = isset($_POST['inquiry_id']) ? intval($_POST['inquiry_id']) : 0;
    $new_status = isset($_POST['new_status']) ? $_POST['new_status'] : '';

    // Validate inputs
    if ($inquiry_id <= 0 || empty($new_status)) {
      $error_message = "Invalid inquiry ID or status";
    } else {
      // Update the inquiry status
      $stmt = $conn->prepare("UPDATE inquiries SET status = ?, updated_at = NOW() WHERE id = ?");
      $stmt->bind_param("si", $new_status, $inquiry_id);

      if ($stmt->execute()) {
        $success_message = "Inquiry status updated successfully.";
      } else {
        $error_message = "Failed to update inquiry status: " . $conn->error;
      }
      $stmt->close();
    }
  }

  // Handle inquiry deletion
  if (isset($_POST['delete_inquiry'])) {
    $inquiry_id = isset($_POST['inquiry_id']) ? intval($_POST['inquiry_id']) : 0;

    if ($inquiry_id > 0) {
      $stmt = $conn->prepare("DELETE FROM inquiries WHERE id = ?");
      $stmt->bind_param("i", $inquiry_id);

      if ($stmt->execute()) {
        $success_message = "Inquiry deleted successfully.";
      } else {
        $error_message = "Failed to delete inquiry: " . $conn->error;
      }
      $stmt->close();
    } else {
      $error_message = "Invalid inquiry ID";
    }
  }

  // Handle inquiry reply/notes
  if (isset($_POST['add_notes'])) {
    $inquiry_id = isset($_POST['inquiry_id']) ? intval($_POST['inquiry_id']) : 0;
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

    if ($inquiry_id > 0) {
      // Check if inquiries table has a 'notes' column
      $check_column = $conn->query("SHOW COLUMNS FROM inquiries LIKE 'notes'");

      if ($check_column->num_rows == 0) {
        // Add notes column if it doesn't exist
        $conn->query("ALTER TABLE inquiries ADD COLUMN notes TEXT AFTER message");
      }

      $stmt = $conn->prepare("UPDATE inquiries SET notes = ?, status = 'in_progress', updated_at = NOW() WHERE id = ?");
      $stmt->bind_param("si", $notes, $inquiry_id);

      if ($stmt->execute()) {
        $success_message = "Notes added and status updated successfully.";
      } else {
        $error_message = "Failed to update inquiry: " . $conn->error;
      }
      $stmt->close();
    } else {
      $error_message = "Invalid inquiry ID";
    }
  }
}

// Pagination variables
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Items per page
$offset = ($page - 1) * $limit;

// Filtering parameters
$filterStatus = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';
$filterType = isset($_GET['filter_type']) ? $_GET['filter_type'] : '';
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$searchKeyword = isset($_GET['search']) ? trim($_GET['search']) : '';

// Sorting parameters
$sortField = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$sortOrder = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'ASC' : 'DESC';

// Build the query with filters
$query = "SELECT i.*, v.make, v.model, v.year, u.first_name, u.last_name, u.email as user_email
          FROM inquiries i
          LEFT JOIN vehicles v ON i.vehicle_id = v.id
          LEFT JOIN users u ON i.user_id = u.id
          WHERE 1=1";

$countQuery = "SELECT COUNT(*) as total 
               FROM inquiries i
               LEFT JOIN vehicles v ON i.vehicle_id = v.id
               LEFT JOIN users u ON i.user_id = u.id
               WHERE 1=1";

$params = [];
$types = "";

// Add filters to query
if (!empty($filterStatus)) {
  $query .= " AND i.status = ?";
  $countQuery .= " AND i.status = ?";
  $params[] = $filterStatus;
  $types .= "s";
}

if (!empty($filterType)) {
  $query .= " AND i.inquiry_type = ?";
  $countQuery .= " AND i.inquiry_type = ?";
  $params[] = $filterType;
  $types .= "s";
}

if (!empty($dateFrom)) {
  $query .= " AND DATE(i.created_at) >= ?";
  $countQuery .= " AND DATE(i.created_at) >= ?";
  $params[] = $dateFrom;
  $types .= "s";
}

if (!empty($dateTo)) {
  $query .= " AND DATE(i.created_at) <= ?";
  $countQuery .= " AND DATE(i.created_at) <= ?";
  $params[] = $dateTo;
  $types .= "s";
}

if (!empty($searchKeyword)) {
  $searchParam = "%{$searchKeyword}%";
  $query .= " AND (i.name LIKE ? OR i.email LIKE ? OR i.message LIKE ? OR CONCAT(v.year, ' ', v.make, ' ', v.model) LIKE ?)";
  $countQuery .= " AND (i.name LIKE ? OR i.email LIKE ? OR i.message LIKE ? OR CONCAT(v.year, ' ', v.make, ' ', v.model) LIKE ?)";
  $params[] = $searchParam;
  $params[] = $searchParam;
  $params[] = $searchParam;
  $params[] = $searchParam;
  $types .= "ssss";
}

// Add sorting
$validSortFields = ['name', 'email', 'created_at', 'status', 'inquiry_type'];
$sortField = in_array($sortField, $validSortFields) ? $sortField : 'created_at';
$query .= " ORDER BY i.$sortField $sortOrder";

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
$totalPages = ceil($total / $limit);

// Get inquiries
$stmt = $conn->prepare($query);
if (!empty($params)) {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$inquiries = [];
if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $inquiries[] = $row;
  }
}
$stmt->close();

// Get inquiry details if viewing/replying to a specific inquiry
$inquiry_details = null;
if (isset($_GET['view']) && !empty($_GET['view'])) {
  $inquiry_id = (int)$_GET['view'];
  $detail_query = "SELECT i.*, v.make, v.model, v.year, u.first_name, u.last_name, u.email as user_email
                   FROM inquiries i
                   LEFT JOIN vehicles v ON i.vehicle_id = v.id
                   LEFT JOIN users u ON i.user_id = u.id
                   WHERE i.id = ?";
  $detail_stmt = $conn->prepare($detail_query);
  $detail_stmt->bind_param("i", $inquiry_id);
  $detail_stmt->execute();
  $detail_result = $detail_stmt->get_result();
  if ($detail_result->num_rows > 0) {
    $inquiry_details = $detail_result->fetch_assoc();
  }
  $detail_stmt->close();
}

// Close the main connection
$conn->close();
?>

<!doctype html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="../src/output.css" rel="stylesheet">
  <title>Contact Management - CentralAutogy Admin</title>
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

    .status-badge.new {
      background-color: #e0f2fe;
      color: #0369a1;
    }

    .status-badge.in_progress {
      background-color: #fef3c7;
      color: #92400e;
    }

    .status-badge.completed {
      background-color: #dcfce7;
      color: #166534;
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
        <h1 class="text-2xl font-bold text-gray-800">Contact Management</h1>
        <p class="text-gray-600">Manage customer inquiries and messages</p>
      </div>

      <!-- Success/Error Messages -->
      <?php if (!empty($success_message)): ?>
        <div class="flash-message mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow transition-opacity duration-500">
          <div class="flex justify-between items-center">
            <div class="flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <p><?php echo $success_message; ?></p>
            </div>
            <button class="close-flash">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>
        </div>
      <?php endif; ?>

      <?php if (!empty($error_message)): ?>
        <div class="flash-message mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow transition-opacity duration-500">
          <div class="flex justify-between items-center">
            <div class="flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
              </svg>
              <p><?php echo $error_message; ?></p>
            </div>
            <button class="close-flash">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>
        </div>
      <?php endif; ?>

      <?php if ($inquiry_details): ?>
        <!-- Single Inquiry View -->
        <div class="dashboard-card bg-white p-6 mb-6">
          <div class="flex justify-between items-center mb-6">
            <div class="flex items-center">
              <div class="bg-indigo-100 rounded-full p-3 mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
              </div>
              <div>
                <h2 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($inquiry_details['name']); ?></h2>
                <p class="text-gray-600"><?php echo htmlspecialchars($inquiry_details['email']); ?></p>
              </div>
            </div>
            <div class="flex items-center space-x-3">
              <span class="status-badge <?php echo htmlspecialchars($inquiry_details['status']); ?>">
                <?php echo ucfirst(str_replace('_', ' ', $inquiry_details['status'])); ?>
              </span>
              <a href="contact.php" class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-4 rounded-lg transition-all flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Back to Inquiries
              </a>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Inquiry Details -->
            <div class="md:col-span-2 bg-gray-50 p-6 rounded-lg">
              <h3 class="text-lg font-semibold mb-4 text-gray-700">Inquiry Details</h3>

              <div class="mb-4">
                <span class="text-sm text-gray-500">Inquiry Type:</span>
                <p class="text-gray-800 font-medium"><?php echo htmlspecialchars($inquiry_details['inquiry_type']); ?></p>
              </div>

              <div class="mb-4">
                <span class="text-sm text-gray-500">Submitted On:</span>
                <p class="text-gray-800"><?php echo date('F j, Y g:i A', strtotime($inquiry_details['created_at'])); ?></p>
              </div>

              <?php if ($inquiry_details['vehicle_id']): ?>
                <div class="mb-4">
                  <span class="text-sm text-gray-500">Related Vehicle:</span>
                  <p class="text-gray-800">
                    <?php
                    if (!empty($inquiry_details['year']) && !empty($inquiry_details['make']) && !empty($inquiry_details['model'])) {
                      echo htmlspecialchars($inquiry_details['year'] . ' ' . $inquiry_details['make'] . ' ' . $inquiry_details['model']);
                    } else {
                      echo "Vehicle #" . $inquiry_details['vehicle_id'];
                    }
                    ?>
                  </p>
                </div>
              <?php endif; ?>

              <div class="mb-4">
                <span class="text-sm text-gray-500">Message:</span>
                <div class="mt-2 p-4 bg-white rounded-lg border border-gray-200">
                  <p class="text-gray-800 whitespace-pre-line"><?php echo htmlspecialchars($inquiry_details['message']); ?></p>
                </div>
              </div>

              <?php if (!empty($inquiry_details['notes'])): ?>
                <div class="mb-4">
                  <span class="text-sm text-gray-500">Admin Notes:</span>
                  <div class="mt-2 p-4 bg-white rounded-lg border border-gray-200">
                    <p class="text-gray-800 whitespace-pre-line"><?php echo htmlspecialchars($inquiry_details['notes']); ?></p>
                  </div>
                </div>
              <?php endif; ?>
            </div>

            <!-- Contact Info & Actions -->
            <div class="space-y-6">
              <div class="bg-gray-50 p-6 rounded-lg">
                <h3 class="text-lg font-semibold mb-4 text-gray-700">Contact Information</h3>

                <div class="mb-4">
                  <span class="text-sm text-gray-500">Name:</span>
                  <p class="text-gray-800 font-medium"><?php echo htmlspecialchars($inquiry_details['name']); ?></p>
                </div>

                <div class="mb-4">
                  <span class="text-sm text-gray-500">Email:</span>
                  <p class="text-gray-800">
                    <a href="mailto:<?php echo htmlspecialchars($inquiry_details['email']); ?>" class="text-indigo-600 hover:text-indigo-800">
                      <?php echo htmlspecialchars($inquiry_details['email']); ?>
                    </a>
                  </p>
                </div>

                <div class="mb-4">
                  <span class="text-sm text-gray-500">Phone:</span>
                  <p class="text-gray-800">
                    <a href="tel:<?php echo htmlspecialchars($inquiry_details['phone']); ?>" class="text-indigo-600 hover:text-indigo-800">
                      <?php echo htmlspecialchars($inquiry_details['phone']); ?>
                    </a>
                  </p>
                </div>

                <?php if ($inquiry_details['user_id']): ?>
                  <div class="mb-4">
                    <span class="text-sm text-gray-500">Registered User:</span>
                    <p class="text-gray-800">
                      <a href="users.php?view=<?php echo $inquiry_details['user_id']; ?>" class="text-indigo-600 hover:text-indigo-800 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd" />
                        </svg>
                        <?php
                        if (!empty($inquiry_details['first_name']) && !empty($inquiry_details['last_name'])) {
                          echo htmlspecialchars($inquiry_details['first_name'] . ' ' . $inquiry_details['last_name']);
                        } else {
                          echo "View Profile";
                        }
                        ?>
                      </a>
                    </p>
                  </div>
                <?php endif; ?>
              </div>

              <div class="bg-gray-50 p-6 rounded-lg">
                <h3 class="text-lg font-semibold mb-4 text-gray-700">Actions</h3>

                <div class="space-y-3">
                  <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-lg transition-all flex items-center justify-center" onclick="openEmailClient()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                      <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                      <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                    </svg>
                    Email Customer
                  </button>

                  <?php if (!empty($inquiry_details['phone'])): ?>
                    <a href="tel:<?php echo htmlspecialchars($inquiry_details['phone']); ?>" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition-all flex items-center justify-center">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                      </svg>
                      Call Customer
                    </a>
                  <?php endif; ?>

                  <button class="w-full bg-amber-500 hover:bg-amber-600 text-white py-2 px-4 rounded-lg transition-all flex items-center justify-center" onclick="openNotesModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M18 13V5a2 2 0 00-2-2H4a2 2 0 00-2 2v8a2 2 0 002 2h3l3 3 3-3h3a2 2 0 002-2zM5 7a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1zm1 3a1 1 0 100 2h3a1 1 0 100-2H6z" clip-rule="evenodd" />
                    </svg>
                    Add Notes
                  </button>

                  <button class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg transition-all flex items-center justify-center" onclick="openStatusModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    Update Status
                  </button>

                  <button class="w-full bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-lg transition-all flex items-center justify-center" onclick="confirmDelete(<?php echo $inquiry_details['id']; ?>, '<?php echo addslashes($inquiry_details['name']); ?>')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    Delete Inquiry
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php else: ?>
        <!-- Search and Filter Options -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
          <form action="contact.php" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
              <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                  </div>
                  <input type="text" name="search" id="search" class="pl-10 w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm" placeholder="Search by name, email, or message" value="<?php echo htmlspecialchars($searchKeyword); ?>">
                </div>
              </div>

              <div>
                <label for="filter_status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="filter_status" name="filter_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                  <option value="">All Statuses</option>
                  <option value="new" <?php echo $filterStatus === 'new' ? 'selected' : ''; ?>>New</option>
                  <option value="in_progress" <?php echo $filterStatus === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                  <option value="completed" <?php echo $filterStatus === 'completed' ? 'selected' : ''; ?>>Completed</option>
                </select>
              </div>

              <div>
                <label for="filter_type" class="block text-sm font-medium text-gray-700 mb-1">Inquiry Type</label>
                <select id="filter_type" name="filter_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                  <option value="">All Types</option>
                  <option value="General Inquiry" <?php echo $filterType === 'General Inquiry' ? 'selected' : ''; ?>>General Inquiry</option>
                  <option value="Vehicle Information" <?php echo $filterType === 'Vehicle Information' ? 'selected' : ''; ?>>Vehicle Information</option>
                  <option value="Other" <?php echo $filterType === 'Other' ? 'selected' : ''; ?>>Other</option>
                </select>
              </div>

              <div class="sm:col-span-2 lg:col-span-1">
                <label for="date_range" class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                <div class="grid grid-cols-2 gap-2">
                  <input type="date" name="date_from" id="date_from" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm" placeholder="From" value="<?php echo htmlspecialchars($dateFrom); ?>">
                  <input type="date" name="date_to" id="date_to" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm" placeholder="To" value="<?php echo htmlspecialchars($dateTo); ?>">
                </div>
              </div>
            </div>

            <div class="flex justify-between">
              <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-lg transition-all flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                </svg>
                Search & Filter
              </button>

              <?php if (!empty($filterStatus) || !empty($filterType) || !empty($dateFrom) || !empty($dateTo) || !empty($searchKeyword)): ?>
                <a href="contact.php" class="bg-red-50 hover:bg-red-100 text-red-600 py-2 px-4 rounded-lg transition-all flex items-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                  </svg>
                  Clear Filters
                </a>
              <?php endif; ?>
            </div>
          </form>
        </div>

        <!-- Inquiries Table -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800">
              Customer Inquiries
              <span class="ml-2 px-3 py-1 bg-indigo-100 text-indigo-800 text-sm rounded-full"><?php echo $total; ?></span>
            </h2>

            <div class="text-sm text-gray-600">
              Showing <?php echo min($total, $limit); ?> of <?php echo $total; ?> inquiries
            </div>
          </div>

          <?php if (empty($inquiries)): ?>
            <div class="bg-yellow-50 p-4 rounded-lg mb-6">
              <div class="flex">
                <div class="flex-shrink-0">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                  </svg>
                </div>
                <div class="ml-3">
                  <h3 class="text-sm font-medium text-yellow-800">No inquiries found</h3>
                  <div class="mt-2 text-sm text-yellow-700">
                    <p>No inquiries match your search criteria. Try adjusting your filters.</p>
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
                      <a href="contact.php?sort=name&order=<?php echo $sortField === 'name' && $sortOrder === 'ASC' ? 'desc' : 'asc'; ?>&<?php echo http_build_query(array_filter(['search' => $searchKeyword, 'filter_status' => $filterStatus, 'filter_type' => $filterType, 'date_from' => $dateFrom, 'date_to' => $dateTo])); ?>" class="flex items-center">
                        Name
                        <?php if ($sortField === 'name'): ?>
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
                      <a href="contact.php?sort=email&order=<?php echo $sortField === 'email' && $sortOrder === 'ASC' ? 'desc' : 'asc'; ?>&<?php echo http_build_query(array_filter(['search' => $searchKeyword, 'filter_status' => $filterStatus, 'filter_type' => $filterType, 'date_from' => $dateFrom, 'date_to' => $dateTo])); ?>" class="flex items-center">
                        Email
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
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Phone</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Type</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Related Vehicle</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">
                      <a href="contact.php?sort=created_at&order=<?php echo $sortField === 'created_at' && $sortOrder === 'ASC' ? 'desc' : 'asc'; ?>&<?php echo http_build_query(array_filter(['search' => $searchKeyword, 'filter_status' => $filterStatus, 'filter_type' => $filterType, 'date_from' => $dateFrom, 'date_to' => $dateTo])); ?>" class="flex items-center">
                        Date
                        <?php if ($sortField === 'created_at'): ?>
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
                      <a href="contact.php?sort=status&order=<?php echo $sortField === 'status' && $sortOrder === 'ASC' ? 'desc' : 'asc'; ?>&<?php echo http_build_query(array_filter(['search' => $searchKeyword, 'filter_status' => $filterStatus, 'filter_type' => $filterType, 'date_from' => $dateFrom, 'date_to' => $dateTo])); ?>" class="flex items-center">
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
                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-600 rounded-tr-lg">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($inquiries as $inquiry): ?>
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                      <td class="px-4 py-3">
                        <div class="font-medium text-gray-800"><?php echo htmlspecialchars($inquiry['name']); ?></div>
                        <?php if ($inquiry['user_id']): ?>
                          <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                            Registered User
                          </span>
                        <?php endif; ?>
                      </td>
                      <td class="px-4 py-3 text-gray-800"><?php echo htmlspecialchars($inquiry['email']); ?></td>
                      <td class="px-4 py-3 text-gray-800"><?php echo htmlspecialchars($inquiry['phone']); ?></td>
                      <td class="px-4 py-3 text-gray-800"><?php echo htmlspecialchars($inquiry['inquiry_type']); ?></td>
                      <td class="px-4 py-3 text-gray-800">
                        <?php if ($inquiry['vehicle_id']): ?>
                          <?php
                          if (!empty($inquiry['make']) && !empty($inquiry['model']) && !empty($inquiry['year'])) {
                            echo htmlspecialchars($inquiry['year'] . ' ' . $inquiry['make'] . ' ' . $inquiry['model']);
                          } else {
                            echo "Vehicle #" . $inquiry['vehicle_id'];
                          }
                          ?>
                        <?php else: ?>
                          <span class="text-gray-400">N/A</span>
                        <?php endif; ?>
                      </td>
                      <td class="px-4 py-3 text-gray-800">
                        <?php echo date('M d, Y', strtotime($inquiry['created_at'])); ?>
                        <div class="text-xs text-gray-500">
                          <?php echo date('g:i A', strtotime($inquiry['created_at'])); ?>
                        </div>
                      </td>
                      <td class="px-4 py-3">
                        <span class="status-badge <?php echo htmlspecialchars($inquiry['status']); ?>">
                          <?php echo ucfirst(str_replace('_', ' ', $inquiry['status'])); ?>
                        </span>
                      </td>
                      <td class="px-4 py-3 text-right">
                        <div class="flex justify-end space-x-2">
                          <a href="contact.php?view=<?php echo $inquiry['id']; ?>" class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-all" title="View Details">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                              <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                              <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                            </svg>
                          </a>
                          <a href="mailto:<?php echo htmlspecialchars($inquiry['email']); ?>" class="p-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-all" title="Email Customer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                              <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                              <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                            </svg>
                          </a>
                          <button onclick="updateStatus(<?php echo $inquiry['id']; ?>, '<?php echo htmlspecialchars($inquiry['status']); ?>')" class="p-1.5 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 transition-all" title="Update Status">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                              <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                              <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                            </svg>
                          </button>
                          <button onclick="confirmDelete(<?php echo $inquiry['id']; ?>, '<?php echo addslashes($inquiry['name']); ?>')" class="p-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-all" title="Delete">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                              <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
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
              <div class="mt-6 flex justify-between items-center">
                <div class="text-sm text-gray-600">
                  Page <?php echo $page; ?> of <?php echo $totalPages; ?>
                </div>
                <div class="flex space-x-1">
                  <?php if ($page > 1): ?>
                    <a href="contact.php?page=<?php echo $page - 1; ?>&<?php echo http_build_query(array_filter(['search' => $searchKeyword, 'filter_status' => $filterStatus, 'filter_type' => $filterType, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'sort' => $sortField, 'order' => $sortOrder])); ?>" class="px-3 py-1.5 rounded-md bg-white border border-gray-200 text-gray-600 text-sm hover:bg-gray-50 transition-all">
                      Previous
                    </a>
                  <?php endif; ?>

                  <?php
                  // Show max 5 page numbers
                  $startPage = max(1, min($page - 2, $totalPages - 4));
                  $endPage = min($startPage + 4, $totalPages);

                  for ($i = $startPage; $i <= $endPage; $i++):
                  ?>
                    <a href="contact.php?page=<?php echo $i; ?>&<?php echo http_build_query(array_filter(['search' => $searchKeyword, 'filter_status' => $filterStatus, 'filter_type' => $filterType, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'sort' => $sortField, 'order' => $sortOrder])); ?>" class="px-3 py-1.5 rounded-md <?php echo $i === $page ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'; ?> text-sm transition-all">
                      <?php echo $i; ?>
                    </a>
                  <?php endfor; ?>

                  <?php if ($page < $totalPages): ?>
                    <a href="contact.php?page=<?php echo $page + 1; ?>&<?php echo http_build_query(array_filter(['search' => $searchKeyword, 'filter_status' => $filterStatus, 'filter_type' => $filterType, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'sort' => $sortField, 'order' => $sortOrder])); ?>" class="px-3 py-1.5 rounded-md bg-white border border-gray-200 text-gray-600 text-sm hover:bg-gray-50 transition-all">
                      Next
                    </a>
                  <?php endif; ?>
                </div>
              </div>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </main>
  </div>

  <!-- Status Update Modal -->
  <div id="statusModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
      <div class="bg-white rounded-xl w-full max-w-md mx-auto shadow-2xl p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-bold text-gray-800">Update Inquiry Status</h3>
          <button class="modal-close text-gray-400 hover:text-gray-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form action="contact.php<?php echo isset($_GET['view']) ? '?view=' . $_GET['view'] : ''; ?>" method="post">
          <input type="hidden" id="status_inquiry_id" name="inquiry_id" value="">
          <input type="hidden" name="update_inquiry_status" value="1">

          <div class="mb-4">
            <label for="new_status" class="block text-sm font-medium text-gray-700 mb-1">New Status</label>
            <select id="new_status" name="new_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
              <option value="new">New</option>
              <option value="in_progress">In Progress</option>
              <option value="completed">Completed</option>
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

  <!-- Notes Modal -->
  <div id="notesModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
      <div class="bg-white rounded-xl w-full max-w-md mx-auto shadow-2xl p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-bold text-gray-800">Add Notes</h3>
          <button class="modal-close text-gray-400 hover:text-gray-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form action="contact.php<?php echo isset($_GET['view']) ? '?view=' . $_GET['view'] : ''; ?>" method="post">
          <input type="hidden" id="notes_inquiry_id" name="inquiry_id" value="">
          <input type="hidden" name="add_notes" value="1">

          <div class="mb-4">
            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea id="notes" name="notes" rows="5" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm" placeholder="Enter notes about this inquiry"></textarea>
            <p class="text-xs text-gray-500 mt-1">Adding notes will automatically update the status to "In Progress"</p>
          </div>

          <div class="flex justify-end space-x-3 mt-6">
            <button type="button" class="modal-close px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg transition duration-300">
              Cancel
            </button>
            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition duration-300">
              Save Notes
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
      <div class="bg-white rounded-xl w-full max-w-md mx-auto shadow-2xl p-6">
        <div class="flex items-center justify-center mb-4">
          <div class="bg-red-100 rounded-full p-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-600" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
          </div>
        </div>
        <h3 class="text-xl font-bold text-center text-gray-800 mb-4">Confirm Deletion</h3>
        <p class="text-center text-gray-600 mb-6" id="delete-message">Are you sure you want to delete this inquiry? This action cannot be undone.</p>

        <form action="contact.php<?php echo isset($_GET['view']) ? '?view=' . $_GET['view'] : ''; ?>" method="post">
          <input type="hidden" id="delete_inquiry_id" name="inquiry_id" value="">
          <input type="hidden" name="delete_inquiry" value="1">

          <div class="flex justify-center space-x-3">
            <button type="button" class="modal-close px-5 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-300 font-medium text-sm">
              Cancel
            </button>
            <button type="submit" class="px-5 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-300 font-medium text-sm">
              Delete Inquiry
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Close flash messages
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

      // Modal functionality
      const statusModal = document.getElementById('statusModal');
      const notesModal = document.getElementById('notesModal');
      const deleteModal = document.getElementById('deleteModal');
      const modalCloseButtons = document.querySelectorAll('.modal-close');

      modalCloseButtons.forEach(button => {
        button.addEventListener('click', function() {
          const modal = this.closest('.fixed.inset-0');
          if (modal) {
            modal.classList.add('hidden');
          }
        });
      });

      // Close modals when clicking outside
      window.addEventListener('click', function(event) {
        if (statusModal && event.target === statusModal) {
          statusModal.classList.add('hidden');
        }
        if (notesModal && event.target === notesModal) {
          notesModal.classList.add('hidden');
        }
        if (deleteModal && event.target === deleteModal) {
          deleteModal.classList.add('hidden');
        }
      });

      // Close modals with escape key
      document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
          if (statusModal) statusModal.classList.add('hidden');
          if (notesModal) notesModal.classList.add('hidden');
          if (deleteModal) deleteModal.classList.add('hidden');
        }
      });

      // Status update modal
      window.updateStatus = function(id, currentStatus) {
        document.getElementById('status_inquiry_id').value = id;
        document.getElementById('new_status').value = currentStatus;
        statusModal.classList.remove('hidden');
      };

      // Notes modal
      window.openNotesModal = function() {
        const inquiryId = <?php echo isset($_GET['view']) ? $_GET['view'] : 0; ?>;
        document.getElementById('notes_inquiry_id').value = inquiryId;
        notesModal.classList.remove('hidden');
      };

      // Delete confirmation modal
      window.confirmDelete = function(id, name) {
        document.getElementById('delete_inquiry_id').value = id;
        document.getElementById('delete-message').textContent = `Are you sure you want to delete the inquiry from ${name}? This action cannot be undone.`;
        deleteModal.classList.remove('hidden');
      };

      // Open email client function
      window.openEmailClient = function() {
        const email = '<?php echo isset($inquiry_details) ? htmlspecialchars($inquiry_details['email']) : ''; ?>';
        const subject = 'RE: Your inquiry';
        window.location.href = `mailto:${email}?subject=${encodeURIComponent(subject)}`;
      };
    });
  </script>
</body>

</html>